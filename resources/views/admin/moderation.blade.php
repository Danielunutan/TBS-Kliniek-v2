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
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
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
    </div>

    <!-- Main Navigation & Search Bar -->
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
        
        <!-- Tab Switcher -->
        <div class="bg-slate-100 p-1 rounded-xl flex gap-1 w-full sm:w-auto">
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
                            <td class="py-3 px-4 text-right">
                                <form action="{{ route('admin.moderation.unban', $bannedUser) }}" method="POST" onsubmit="return confirm('Weet je zeker dat je de ban van {{ $bannedUser->character?->name ?? $bannedUser->name }} wilt opheffen?');">
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
                                <form action="{{ route('admin.moderation.warning.destroy', $warning) }}" method="POST" onsubmit="return confirm('Weet je zeker dat je deze waarschuwing uit het dossier wilt wissen?');">
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

</div>
@endsection
