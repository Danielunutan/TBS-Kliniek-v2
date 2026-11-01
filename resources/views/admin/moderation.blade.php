@extends('admin.layout')

@section('admin-content')
@php
    $allowedModTabs = ['bans', 'warnings', 'reports'];
    $rawModTab = (string) request('tab', 'bans');
    $safeModTab = in_array($rawModTab, $allowedModTabs, true) ? $rawModTab : 'bans';
@endphp
<div class="space-y-6" x-data="{ 
    activeTab: '{{ $safeModTab }}', 
    showQuickAction: false, 
    quickMode: 'warn', 
    selectedUserId: '', 
    selectedUserName: '',
    selectedActionUrl: '',
    defaultReason: '',
    openQuickWarn(userId, userName, reason) {
        this.selectedUserId = userId;
        this.selectedUserName = userName;
        this.defaultReason = reason ? ('Naar aanleiding van melding: ' + reason) : '';
        this.quickMode = 'warn';
        this.selectedActionUrl = '{{ url('/admin/moderatie/spelers') }}/' + userId + '/waarschuwen';
        this.showQuickAction = true;
    },
    openQuickBan(userId, userName, reason) {
        this.selectedUserId = userId;
        this.selectedUserName = userName;
        this.defaultReason = reason ? ('Wegens overtreding: ' + reason) : '';
        this.quickMode = 'ban';
        this.selectedActionUrl = '{{ url('/admin/moderatie/spelers') }}/' + userId + '/verbannen';
        this.showQuickAction = true;
    }
}">

    <!-- Stats Row -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200/80 dark:border-rose-800/70 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-ban"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Actieve Verbanningen</span>
                <span class="text-xl font-black text-rose-700 dark:text-rose-400">{{ $totalBannedCount }}</span>
                <span class="text-[10px] text-slate-500 dark:text-slate-400 block">Geblokkeerd van toegang</span>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/70 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Waarschuwingen Uitgedeeld</span>
                <span class="text-xl font-black text-amber-700 dark:text-amber-400">{{ $totalWarningsCount }}</span>
                <span class="text-[10px] text-slate-500 dark:text-slate-400 block">Dossier berispingen</span>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Wacht op Bevestiging</span>
                <span class="text-xl font-black text-purple-700 dark:text-purple-400">{{ $unackedWarningsCount }}</span>
                <span class="text-[10px] text-slate-500 dark:text-slate-400 block">Nog niet afgevinkt door speler</span>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200/80 dark:border-rose-800/70 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-flag"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Openstaande Meldingen</span>
                <span class="text-xl font-black text-rose-600 dark:text-rose-400">{{ $pendingReportsCount }}</span>
                <span class="text-[10px] text-slate-500 dark:text-slate-400 block">Spelers, bendes & chat</span>
            </div>
        </div>
    </div>

    <!-- Main Navigation & Search Bar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
        
        <!-- Tab Switcher -->
        <div class="bg-slate-100/90 dark:bg-slate-800/80 p-1 rounded-xl border border-slate-200/80 dark:border-slate-700/70 flex flex-wrap gap-1 w-full sm:w-auto">
            <button type="button" @click="activeTab = 'bans'"
                    :class="activeTab === 'bans' ? 'bg-white dark:bg-slate-900 text-rose-700 dark:text-rose-400 font-black shadow-xs border border-slate-200/80 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-bold'"
                    class="px-4 py-2 text-xs rounded-lg transition-all duration-150 cursor-pointer flex items-center gap-1.5">
                <i class="fa-solid fa-ban text-rose-500"></i>
                <span>Verbanningen ({{ $totalBannedCount }})</span>
            </button>
            <button type="button" @click="activeTab = 'warnings'"
                    :class="activeTab === 'warnings' ? 'bg-white dark:bg-slate-900 text-amber-700 dark:text-amber-400 font-black shadow-xs border border-slate-200/80 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-bold'"
                    class="px-4 py-2 text-xs rounded-lg transition-all duration-150 cursor-pointer flex items-center gap-1.5">
                <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                <span>Waarschuwingen ({{ $totalWarningsCount }})</span>
            </button>
            <button type="button" @click="activeTab = 'reports'"
                    :class="activeTab === 'reports' ? 'bg-white dark:bg-slate-900 text-rose-700 dark:text-rose-400 font-black shadow-xs border border-slate-200/80 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-bold'"
                    class="px-4 py-2 text-xs rounded-lg transition-all duration-150 cursor-pointer flex items-center gap-1.5">
                <i class="fa-solid fa-flag text-rose-500"></i>
                <span>Meldingen ({{ $pendingReportsCount }})</span>
                @if($pendingReportsCount > 0)
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                @endif
            </button>
        </div>

        <!-- Search Form -->
        <form method="GET" action="{{ route('admin.moderation.index') }}" class="w-full sm:w-auto flex items-center gap-2">
            <div class="relative flex-1 sm:w-64">
                <input type="text" name="search" value="{{ $search }}" placeholder="Zoek op naam, email of reden..." 
                       class="w-full bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl pl-3.5 pr-8 py-2 text-xs font-bold text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                <i class="fa-solid fa-magnifying-glass absolute right-3 top-2.5 text-slate-400 dark:text-slate-500 text-xs"></i>
            </div>
            <button type="submit" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white text-xs font-black shadow-xs transition-all duration-150 active:scale-95 cursor-pointer">
                Zoeken
            </button>
            @if($search)
            <a href="{{ route('admin.moderation.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700 text-xs font-bold transition active:scale-95">
                Wissen
            </a>
            @endif
        </form>

    </div>

    <!-- TAB 1: ACTIEVE VERBANNINGEN (BANS) -->
    <div x-show="activeTab === 'bans'" class="space-y-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-extrabold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-ban text-rose-500"></i> Actueel Verbannen Spelers
                    </h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Deze accounts hebben geen toegang tot de spelwereld.</p>
                </div>
                <a href="{{ route('admin.players') }}" class="text-xs text-purple-700 dark:text-purple-400 font-bold hover:underline flex items-center gap-1">
                    <i class="fa-solid fa-users text-xs"></i> Naar Spelerslijst
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50/90 dark:bg-slate-800/60 text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200/80 dark:border-slate-800">
                            <th class="py-3 px-4">PATIËNT</th>
                            <th class="py-3 px-3">VERBANNEN DOOR</th>
                            <th class="py-3 px-3">TYPE / DUUR</th>
                            <th class="py-3 px-3">OPGEGEVEN REDEN</th>
                            <th class="py-3 px-3">DATUM VERBANNEN</th>
                            <th class="py-3 px-4 text-right">ACTIE</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 font-medium">
                        @forelse($activeBans as $bannedUser)
                        <tr class="hover:bg-rose-50/40 dark:hover:bg-rose-950/20 transition-colors">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $bannedUser->character?->avatar_url ?? asset('assets/characters/avatar_male_1.jpg') }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 dark:border-slate-700">
                                    <div>
                                        <div class="font-extrabold text-slate-900 dark:text-white">{{ $bannedUser->character?->name ?? $bannedUser->name }}</div>
                                        <div class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">{{ $bannedUser->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3 text-slate-700 dark:text-slate-300">
                                @if($bannedUser->bannedBy)
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $bannedUser->bannedBy->character?->name ?? $bannedUser->bannedBy->name }}</span>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500">Directie / Systeem</span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                @if($bannedUser->banned_until)
                                    <span class="bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 text-[10px] font-extrabold px-2 py-0.5 rounded-full inline-flex items-center gap-1 border border-amber-200/80 dark:border-amber-800/70" title="Tot {{ $bannedUser->banned_until->isoFormat('D MMMM YYYY HH:mm') }}">
                                        <i class="fa-solid fa-hourglass-half"></i> Tot {{ $bannedUser->banned_until->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="bg-rose-50 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full inline-flex items-center gap-1 border border-rose-200/80 dark:border-rose-800/70">
                                        <i class="fa-solid fa-skull"></i> Permanent
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 max-w-xs">
                                <span class="text-slate-800 dark:text-slate-200 text-xs font-semibold truncate block" title="{{ $bannedUser->ban_reason }}">
                                    {{ $bannedUser->ban_reason ?: 'Geen reden ingevoerd' }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-slate-500 dark:text-slate-400 text-[11px] font-mono">
                                {{ $bannedUser->banned_at ? $bannedUser->banned_at->isoFormat('D MMM YYYY [om] HH:mm') : '-' }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                <form action="{{ route('admin.moderation.unban', $bannedUser) }}" method="POST"
                                      data-confirm="Weet je zeker dat je de ban van {{ $bannedUser->character?->name ?? $bannedUser->name }} wilt opheffen?"
                                      data-confirm-title="Verbanning Opheffen"
                                      data-confirm-btn="Ja, Ban Opheffen"
                                      data-confirm-type="warning"
                                      data-confirm-icon="fa-lock-open">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-600 hover:text-white dark:bg-emerald-950/50 dark:hover:bg-emerald-600 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/70 text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer shadow-2xs">
                                        <i class="fa-solid fa-lock-open mr-1"></i> Ban Opheffen
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 dark:text-slate-500 text-xs">
                                <i class="fa-solid fa-shield-halved text-emerald-500 text-xl block mb-1"></i>
                                Er zijn momenteel geen actieve verbanningen op TBS Kliniek.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                {{ $activeBans->links() }}
            </div>
        </div>
    </div>

    <!-- TAB 2: RECENTE WAARSCHUWINGEN (WARNINGS) -->
    <div x-show="activeTab === 'warnings'" class="space-y-4" x-cloak>
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-extrabold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-triangle-exclamation text-amber-500"></i> Dossier Waarschuwingen
                    </h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Overzicht van officiële waarschuwingen en gelezen-bevestigingen.</p>
                </div>
                <a href="{{ route('admin.players') }}" class="text-xs text-purple-700 dark:text-purple-400 font-bold hover:underline flex items-center gap-1">
                    <i class="fa-solid fa-users text-xs"></i> Naar Spelerslijst
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50/90 dark:bg-slate-800/60 text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200/80 dark:border-slate-800">
                            <th class="py-3 px-4">PATIËNT</th>
                            <th class="py-3 px-3">UITGEDEELD DOOR</th>
                            <th class="py-3 px-3">REDEN / BERICHT</th>
                            <th class="py-3 px-3 text-center">STATUS</th>
                            <th class="py-3 px-3">DATUM</th>
                            <th class="py-3 px-4 text-right">ACTIE</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 font-medium">
                        @forelse($recentWarnings as $warning)
                        <tr class="hover:bg-amber-50/30 dark:hover:bg-amber-950/20 transition-colors">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $warning->user?->character?->avatar_url ?? asset('assets/characters/avatar_male_1.jpg') }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 dark:border-slate-700">
                                    <div>
                                        <div class="font-extrabold text-slate-900 dark:text-white">{{ $warning->user?->character?->name ?? $warning->user?->name ?? 'Onbekend' }}</div>
                                        <div class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">{{ $warning->user?->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3 text-slate-700 dark:text-slate-300">
                                @if($warning->issuer)
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $warning->issuer->character?->name ?? $warning->issuer->name }}</span>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500">Directie / Systeem</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 max-w-sm">
                                <p class="text-slate-800 dark:text-slate-200 text-xs leading-relaxed font-semibold break-words">
                                    "{{ $warning->reason }}"
                                </p>
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if($warning->isAcknowledged())
                                    <span class="bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full inline-flex items-center gap-1 border border-emerald-200/80 dark:border-emerald-800/70" title="Gelezen op {{ $warning->acknowledged_at->isoFormat('D MMM YYYY HH:mm') }}">
                                        <i class="fa-solid fa-check"></i> Bevestigd
                                    </span>
                                @else
                                    <span class="bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full inline-flex items-center gap-1 border border-amber-200/80 dark:border-amber-800/70">
                                        <i class="fa-solid fa-clock"></i> Openstaand
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-slate-500 dark:text-slate-400 text-[11px] font-mono">
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
                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-600 hover:text-white dark:bg-rose-950/50 dark:hover:bg-rose-600 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70 text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer shadow-2xs" title="Wis waarschuwing">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 dark:text-slate-500 text-xs">
                                Geen waarschuwingen gevonden.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                {{ $recentWarnings->links() }}
            </div>
        </div>
    </div>

    <!-- TAB 3: SPELER & BENDE & CHAT MELDINGEN (REPORTS) -->
    <div x-show="activeTab === 'reports'" class="space-y-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-extrabold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-flag text-rose-500"></i> Binnengekomen Rapporten & Meldingen
                    </h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Ingezonden door patiënten via profielen, bendes of live chat berichten.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70">
                        {{ $pendingReportsCount }} openstaand
                    </span>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700">
                        {{ $totalReportsCount }} totaal
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/90 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 text-slate-500 dark:text-slate-400 text-[10px] uppercase tracking-wider">
                            <th class="py-3 px-4 font-extrabold">Status</th>
                            <th class="py-3 px-4 font-extrabold">Onderwerp / Type</th>
                            <th class="py-3 px-4 font-extrabold">Geraporteerde</th>
                            <th class="py-3 px-4 font-extrabold">Reden & Toelichting</th>
                            <th class="py-3 px-4 font-extrabold">Melder</th>
                            <th class="py-3 px-4 font-extrabold">Datum</th>
                            <th class="py-3 px-4 font-extrabold text-right">Acties</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @forelse($reports as $report)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors {{ $report->status === 'pending' ? 'bg-rose-50/20 dark:bg-rose-950/15' : '' }}">
                            <td class="py-3 px-4">
                                @if($report->status === 'pending')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70 animate-pulse">
                                        <i class="fa-solid fa-clock"></i> Nieuw
                                    </span>
                                @elseif($report->status === 'resolved')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/70">
                                        <i class="fa-solid fa-check"></i> Afgehandeld
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700">
                                        <i class="fa-solid fa-xmark"></i> Verworpen
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($report->reported_type === 'character')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-extrabold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/70">
                                        <i class="fa-solid fa-user"></i> Speler Profiel
                                    </span>
                                @elseif($report->reported_type === 'gang')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-extrabold bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70">
                                        <i class="fa-solid fa-shield-halved"></i> Bende / Gang
                                    </span>
                                @elseif($report->reported_type === 'chat_message')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70">
                                        <i class="fa-solid fa-comments"></i> Chatbericht
                                    </span>
                                @else
                                    <span class="text-slate-500 dark:text-slate-400 font-mono">{{ $report->reported_type }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-800 dark:text-slate-200">
                                @php
                                    $reportedChar = $report->getReportedCharacter();
                                    $reportedUser = $report->getReportedUser();
                                    $targetUrl = $report->getTargetUrl();
                                @endphp
                                <div class="flex items-center gap-2.5">
                                    @if($reportedChar)
                                        <a href="{{ $targetUrl }}" target="_blank" class="shrink-0" title="Bekijk dossier van {{ $reportedChar->name }}">
                                            <img src="{{ $reportedChar->avatar_url }}" alt="{{ $reportedChar->name }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 dark:border-slate-700 hover:ring-2 hover:ring-purple-400 transition">
                                        </a>
                                    @endif
                                    <div>
                                        <a href="{{ $targetUrl }}" target="_blank" class="text-purple-700 dark:text-purple-400 hover:text-purple-900 dark:hover:text-purple-300 font-extrabold flex items-center gap-1.5 hover:underline" title="Klik om direct naar het profiel te gaan">
                                            <span>{{ $report->reported_name ?? ($report->reported_type === 'gang' ? 'Bende #'.$report->reported_id : 'Speler #'.$report->reported_id) }}</span>
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-purple-400"></i>
                                        </a>
                                        @if($reportedChar)
                                            <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono block">Level {{ $reportedChar->level }} &bull; {{ number_format($reportedChar->respect, 0, ',', '.') }} respect</span>
                                        @elseif($report->reported_type === 'gang')
                                            <span class="text-[10px] text-purple-600 dark:text-purple-400 font-bold block">Openbare Bendepagina</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 max-w-xs" x-data="{ expanded: false }">
                                <div class="font-bold text-rose-700 dark:text-rose-400 break-words">{{ $report->reason }}</div>
                                @if($report->details)
                                    <p @click="expanded = !expanded" :class="expanded ? '' : 'line-clamp-2'" class="text-[11px] text-slate-600 dark:text-slate-400 mt-0.5 italic break-words cursor-pointer hover:text-slate-900 dark:hover:text-slate-200 transition" title="Klik om volledig te lezen">"{{ $report->details }}"</p>
                                @endif
                                @if($report->mod_notes)
                                    <div class="text-[10px] text-purple-700 dark:text-purple-300 font-semibold mt-1 bg-purple-50 dark:bg-purple-950/50 p-1 rounded border border-purple-100 dark:border-purple-800/70 break-words">
                                        Notitie: {{ $report->mod_notes }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($report->reporter)
                                    <a href="{{ route('profile.show', $report->reporter->name) }}" target="_blank" class="font-bold text-slate-700 dark:text-slate-300 hover:text-purple-600 dark:hover:text-purple-400 hover:underline">
                                        {{ $report->reporter->name }}
                                    </a>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500">Onbekend</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-500 dark:text-slate-400 text-[11px] font-mono whitespace-nowrap">
                                {{ $report->created_at->isoFormat('D MMM YYYY [om] HH:mm') }}
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- DIRECT LINK TO DOSSIER / PROFIEL -->
                                    <a href="{{ $targetUrl }}" target="_blank" 
                                       class="px-2.5 py-1 rounded-lg bg-purple-50 hover:bg-purple-600 hover:text-white dark:bg-purple-950/50 dark:hover:bg-purple-600 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70 text-xs font-bold transition-all duration-150 inline-flex items-center gap-1 shadow-2xs cursor-pointer active:scale-95"
                                       title="Bekijk direct het profiel van {{ $report->reported_name }}">
                                        <i class="fa-solid fa-id-card text-[10px]"></i>
                                        <span>Dossier</span>
                                    </a>

                                    @if($reportedUser && ! $reportedUser->isOwner())
                                        <!-- DIRECTE MAATREGEL: WAARSCHUWEN -->
                                        <button type="button" 
                                                @click="openQuickWarn(@js((string) $reportedUser->id), @js($report->reported_name), @js($report->reason))"
                                                class="px-2 py-1 rounded-lg bg-amber-50 hover:bg-amber-600 hover:text-white dark:bg-amber-950/50 dark:hover:bg-amber-600 text-amber-700 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70 text-xs font-bold transition-all duration-150 inline-flex items-center gap-1 shadow-2xs cursor-pointer active:scale-95"
                                                title="Deel direct een officiële waarschuwing uit aan {{ $report->reported_name }}">
                                            <i class="fa-solid fa-triangle-exclamation text-[10px]"></i>
                                            <span class="hidden xl:inline">Waarschuw</span>
                                        </button>

                                        <!-- DIRECTE MAATREGEL: VERBANNEN -->
                                        @if(! $reportedUser->is_banned)
                                        <button type="button" 
                                                @click="openQuickBan(@js((string) $reportedUser->id), @js($report->reported_name), @js($report->reason))"
                                                class="px-2 py-1 rounded-lg bg-rose-50 hover:bg-rose-600 hover:text-white dark:bg-rose-950/50 dark:hover:bg-rose-600 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70 text-xs font-bold transition-all duration-150 inline-flex items-center gap-1 shadow-2xs cursor-pointer active:scale-95"
                                                title="Verban {{ $report->reported_name }} direct van het spel">
                                            <i class="fa-solid fa-ban text-[10px]"></i>
                                            <span class="hidden xl:inline">Ban</span>
                                        </button>
                                        @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-black bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70 inline-flex items-center gap-1" title="Deze speler is al verbannen">
                                            <i class="fa-solid fa-lock text-[9px]"></i>
                                        </span>
                                        @endif
                                    @endif

                                    @if($report->status === 'pending')
                                    <form action="{{ route('admin.moderation.report.resolve', $report) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-600 hover:text-white dark:bg-emerald-950/50 dark:hover:bg-emerald-600 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/70 text-xs font-bold transition-all duration-150 cursor-pointer active:scale-95" title="Markeer als opgelost">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.moderation.report.dismiss', $report) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700 text-xs font-bold transition-all duration-150 cursor-pointer active:scale-95" title="Verwerp melding">
                                            <i class="fa-solid fa-xmark"></i>
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
                                        <button type="submit" class="p-1 rounded-lg bg-rose-50 hover:bg-rose-600 hover:text-white dark:bg-rose-950/50 dark:hover:bg-rose-600 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70 text-xs font-bold transition-all duration-150 cursor-pointer active:scale-95" title="Wis melding">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 dark:text-slate-500 text-xs">
                                Geen meldingen gevonden. Alles is rustig op de afdelingen!
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                {{ $reports->links() }}
            </div>
        </div>
    </div>

    <!-- QUICK ACTION MODAL (WARN / BAN) -->
    <div x-show="showQuickAction" x-cloak @keydown.escape.window="showQuickAction = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-xs">
        <div @click.outside="showQuickAction = false" class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full max-h-[85dvh] overflow-y-auto p-6 shadow-2xl border border-slate-200/90 dark:border-slate-800 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <span :class="quickMode === 'ban' ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/70' : 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200/80 dark:border-amber-800/70'" class="w-8 h-8 rounded-xl flex items-center justify-center text-sm font-bold">
                        <i :class="quickMode === 'ban' ? 'fa-solid fa-ban' : 'fa-solid fa-triangle-exclamation'"></i>
                    </span>
                    <div>
                        <h4 class="text-sm font-black text-slate-900 dark:text-white" x-text="quickMode === 'ban' ? 'Speler Verbannen' : 'Officiële Waarschuwing Uitdelen'"></h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Patiënt: <strong class="text-slate-800 dark:text-slate-200" x-text="selectedUserName"></strong></p>
                    </div>
                </div>
                <button type="button" @click="showQuickAction = false" aria-label="Sluiten" class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 text-lg cursor-pointer transition">&times;</button>
            </div>

            <form :action="selectedActionUrl" method="POST" class="space-y-4">
                @csrf
                <template x-if="quickMode === 'ban'">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Duur van de verbanning</label>
                        <select name="duration" class="w-full bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl p-2.5 text-base sm:text-xs font-bold text-slate-800 dark:text-slate-100 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                            <option value="1_hour">1 uur</option>
                            <option value="6_hours">6 uur</option>
                            <option value="12_hours">12 uur</option>
                            <option value="1_day" selected>1 dag (24 uur)</option>
                            <option value="3_days">3 dagen</option>
                            <option value="7_days">7 dagen (1 week)</option>
                            <option value="14_days">14 dagen</option>
                            <option value="30_days">30 dagen (1 maand)</option>
                            <option value="permanent">Permanent (Voor altijd)</option>
                        </select>
                    </div>
                </template>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Opgegeven Reden (zichtbaar voor speler)</label>
                    <textarea name="reason" rows="3" required x-model="defaultReason"
                              placeholder="Beschrijf de reden van deze maatregel..." 
                              class="w-full bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl p-3 text-base sm:text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="showQuickAction = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer">
                        Annuleren
                    </button>
                    <button type="submit" 
                            :class="quickMode === 'ban' ? 'bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500 text-white' : 'bg-amber-500 hover:bg-amber-400 dark:bg-amber-500 dark:hover:bg-amber-400 text-slate-950 font-black'"
                            class="px-4 py-2 rounded-xl text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer shadow-xs">
                        <span x-text="quickMode === 'ban' ? 'Definitief Verbannen' : 'Waarschuwing Verzenden'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
