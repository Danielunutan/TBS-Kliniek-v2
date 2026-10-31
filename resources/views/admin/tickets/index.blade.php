@extends('admin.layout')

@section('title', 'Support Tickets Beheer - TBS Kliniek Admin')

@section('admin-content')
<div class="space-y-6">

    <!-- Header & Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <a href="{{ route('admin.tickets.index', ['status' => 'open']) }}" 
           class="bg-white dark:bg-slate-900 border {{ $currentStatus === 'open' ? 'border-amber-400 dark:border-amber-500 ring-2 ring-amber-400/20' : 'border-slate-200/90 dark:border-slate-800' }} rounded-2xl p-4 shadow-xs hover:border-amber-300 dark:hover:border-amber-600 transition-all block">
            <div class="text-[10px] font-extrabold text-amber-700 dark:text-amber-400 uppercase tracking-wider">Open Tickets</div>
            <div class="text-2xl font-black text-amber-900 dark:text-amber-300 mt-1">{{ $stats['open'] }}</div>
            <div class="text-[11px] text-amber-600 dark:text-amber-400/80 mt-0.5">Wachten op eerste reactie</div>
        </a>

        <a href="{{ route('admin.tickets.index', ['status' => 'in_behandeling']) }}" 
           class="bg-white dark:bg-slate-900 border {{ $currentStatus === 'in_behandeling' ? 'border-sky-400 dark:border-sky-500 ring-2 ring-sky-400/20' : 'border-slate-200/90 dark:border-slate-800' }} rounded-2xl p-4 shadow-xs hover:border-sky-300 dark:hover:border-sky-600 transition-all block">
            <div class="text-[10px] font-extrabold text-sky-700 dark:text-sky-400 uppercase tracking-wider">In Behandeling</div>
            <div class="text-2xl font-black text-sky-900 dark:text-sky-300 mt-1">{{ $stats['in_behandeling'] }}</div>
            <div class="text-[11px] text-sky-600 dark:text-sky-400/80 mt-0.5">Onderzoek door staff</div>
        </a>

        <a href="{{ route('admin.tickets.index', ['status' => 'beantwoord']) }}" 
           class="bg-white dark:bg-slate-900 border {{ $currentStatus === 'beantwoord' ? 'border-emerald-400 dark:border-emerald-500 ring-2 ring-emerald-400/20' : 'border-slate-200/90 dark:border-slate-800' }} rounded-2xl p-4 shadow-xs hover:border-emerald-300 dark:hover:border-emerald-600 transition-all block">
            <div class="text-[10px] font-extrabold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Beantwoord</div>
            <div class="text-2xl font-black text-emerald-900 dark:text-emerald-300 mt-1">{{ $stats['beantwoord'] }}</div>
            <div class="text-[11px] text-emerald-600 dark:text-emerald-400/80 mt-0.5">Wacht op speler</div>
        </a>

        <a href="{{ route('admin.tickets.index', ['status' => 'gesloten']) }}" 
           class="bg-white dark:bg-slate-900 border {{ $currentStatus === 'gesloten' ? 'border-slate-400 dark:border-slate-500 ring-2 ring-slate-400/20' : 'border-slate-200/90 dark:border-slate-800' }} rounded-2xl p-4 shadow-xs hover:border-slate-300 dark:hover:border-slate-700 transition-all block">
            <div class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Gesloten / Afgehandeld</div>
            <div class="text-2xl font-black text-slate-700 dark:text-slate-200 mt-1">{{ $stats['gesloten'] }}</div>
            <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Succesvol afgerond</div>
        </a>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-xs space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            
            <!-- Status Tabs -->
            <div class="flex flex-wrap items-center gap-1.5 text-xs font-bold">
                <a href="{{ route('admin.tickets.index', ['status' => 'actief']) }}" 
                   class="px-3 py-1.5 rounded-lg transition {{ $currentStatus === 'actief' ? 'bg-purple-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    Alle Actieve ({{ $stats['open'] + $stats['in_behandeling'] + $stats['beantwoord'] }})
                </a>
                <a href="{{ route('admin.tickets.index', ['status' => 'open']) }}" 
                   class="px-3 py-1.5 rounded-lg transition {{ $currentStatus === 'open' ? 'bg-amber-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    Open ({{ $stats['open'] }})
                </a>
                <a href="{{ route('admin.tickets.index', ['status' => 'in_behandeling']) }}" 
                   class="px-3 py-1.5 rounded-lg transition {{ $currentStatus === 'in_behandeling' ? 'bg-sky-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    In Behandeling ({{ $stats['in_behandeling'] }})
                </a>
                <a href="{{ route('admin.tickets.index', ['status' => 'beantwoord']) }}" 
                   class="px-3 py-1.5 rounded-lg transition {{ $currentStatus === 'beantwoord' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    Beantwoord ({{ $stats['beantwoord'] }})
                </a>
                <a href="{{ route('admin.tickets.index', ['status' => 'gesloten']) }}" 
                   class="px-3 py-1.5 rounded-lg transition {{ $currentStatus === 'gesloten' ? 'bg-slate-700 dark:bg-slate-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    Gesloten ({{ $stats['gesloten'] }})
                </a>
                <a href="{{ route('admin.tickets.index', ['status' => 'all']) }}" 
                   class="px-3 py-1.5 rounded-lg transition {{ $currentStatus === 'all' ? 'bg-purple-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    Alles ({{ $stats['totaal'] }})
                </a>
            </div>

            <!-- Search Form -->
            <form action="{{ route('admin.tickets.index') }}" method="GET" class="flex items-center gap-2 w-full md:w-auto">
                <input type="hidden" name="status" value="{{ $currentStatus }}">
                <div class="relative flex-1 md:flex-none">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 dark:text-slate-500 text-xs"></i>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Zoek ticket, speler, email..." 
                           class="pl-8 pr-3 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none w-full md:w-64 transition">
                </div>
                <button type="submit" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs rounded-xl border border-slate-200/80 dark:border-slate-700 transition cursor-pointer active:scale-95">
                    Filter
                </button>
                @if(request('search'))
                <a href="{{ route('admin.tickets.index', ['status' => $currentStatus]) }}" class="text-xs text-rose-600 dark:text-rose-400 hover:underline">
                    Wis
                </a>
                @endif
            </form>

        </div>
    </div>

    <!-- Tickets Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-headset text-purple-600 dark:text-purple-400"></i>
                <h2 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wide">Support Tickets Overzicht</h2>
            </div>
            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                {{ $tickets->total() }} gevonden
            </span>
        </div>

        @if($tickets->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/90 dark:bg-slate-800/60">
                        <th class="py-3 px-4">TICKET #</th>
                        <th class="py-3 px-4">PATIËNT / SPELER</th>
                        <th class="py-3 px-4">ONDERWERP & CATEGORIE</th>
                        <th class="py-3 px-4 text-center">PRIORITEIT</th>
                        <th class="py-3 px-4 text-center">STATUS</th>
                        <th class="py-3 px-4 text-center">LAATSTE REACTIE</th>
                        <th class="py-3 px-4 text-right">ACTIE</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 font-medium">
                    @foreach($tickets as $ticket)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-800 dark:text-slate-200">
                            {{ $ticket->ticket_number }}
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2.5">
                                <img src="{{ $ticket->character?->avatar_url ?? asset('assets/logo.png') }}" 
                                     alt="{{ $ticket->character?->name ?? 'Speler' }}" 
                                     class="w-7 h-7 rounded-lg object-cover border border-slate-200 dark:border-slate-700 shadow-2xs shrink-0">
                                <div>
                                    <div class="font-extrabold text-slate-900 dark:text-white leading-tight">
                                        {{ $ticket->character?->name ?? 'Verwijderd' }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 dark:text-slate-500">
                                        {{ $ticket->user?->email }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 max-w-xs">
                            <a href="{{ route('admin.tickets.show', $ticket) }}" class="font-extrabold text-slate-900 dark:text-white hover:text-purple-600 dark:hover:text-purple-400 transition block truncate">
                                {{ $ticket->subject }}
                            </a>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-500 dark:text-slate-400">
                                    <i class="fa-solid {{ $ticket->category_icon }} text-[9px]"></i>
                                    {{ $ticket->category_label }}
                                </span>
                                @if($ticket->status === 'beantwoord')
                                <span class="text-[9px] bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 px-1.5 py-0.2 rounded-sm border border-emerald-200/80 dark:border-emerald-800/70 font-bold inline-flex items-center gap-1">
                                    <i class="fa-solid fa-check text-[8px]"></i> Beantwoord door staff
                                </span>
                                @elseif($ticket->status === 'gesloten')
                                <span class="text-[9px] bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-1.5 py-0.2 rounded-sm border border-slate-200/80 dark:border-slate-700 font-bold inline-flex items-center gap-1">
                                    <i class="fa-solid fa-lock text-[8px]"></i> Gesloten
                                </span>
                                @elseif($ticket->last_reply_by_staff)
                                <span class="text-[9px] bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 px-1.5 py-0.2 rounded-sm border border-purple-200/80 dark:border-purple-800/70 font-bold">
                                    Wacht op speler
                                </span>
                                @else
                                <span class="text-[9px] bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 px-1.5 py-0.2 rounded-sm border border-amber-200/80 dark:border-amber-800/70 font-bold inline-flex items-center gap-1 animate-pulse">
                                    <i class="fa-solid fa-bell text-[8px]"></i> Reactie vereist!
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $ticket->priority_badge_classes }}">
                                {{ $ticket->priority_label }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $ticket->status_badge_classes }}">
                                {{ $ticket->status_label }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center text-slate-500 dark:text-slate-400 text-[11px]">
                            {{ $ticket->updated_at->diffForHumans() }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            @if($ticket->status === 'beantwoord')
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.tickets.show', $ticket) }}" 
                                   class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-extrabold text-xs transition inline-flex items-center gap-1 shadow-2xs cursor-pointer active:scale-95">
                                    <i class="fa-solid fa-eye text-[10px]"></i>
                                    <span>Bekijken</span>
                                </a>
                                <form action="{{ route('admin.tickets.status', $ticket) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="status" value="gesloten">
                                    <button type="submit" title="Direct sluiten & afronden" class="px-2 py-1.5 rounded-lg bg-white dark:bg-slate-800/90 hover:bg-rose-50 dark:hover:bg-rose-950/50 text-slate-500 dark:text-slate-300 hover:text-rose-700 dark:hover:text-rose-300 border border-slate-200/80 dark:border-slate-700 hover:border-rose-200 dark:hover:border-rose-800 text-xs font-bold transition inline-flex items-center gap-1 cursor-pointer active:scale-95">
                                        <i class="fa-solid fa-lock text-[10px]"></i>
                                        <span>Sluit</span>
                                    </button>
                                </form>
                            </div>
                            @elseif($ticket->status === 'gesloten')
                            <a href="{{ route('admin.tickets.show', $ticket) }}" 
                               class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700 font-bold text-xs transition inline-flex items-center gap-1 cursor-pointer active:scale-95">
                                <i class="fa-solid fa-folder-open text-[10px]"></i>
                                <span>Dossier</span>
                            </a>
                            @else
                            <a href="{{ route('admin.tickets.show', $ticket) }}" 
                               class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-extrabold text-xs transition inline-flex items-center gap-1.5 shadow-2xs cursor-pointer active:scale-95">
                                <i class="fa-solid fa-reply text-[10px]"></i>
                                <span>Beantwoorden</span>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $tickets->links() }}
        </div>
        @else
        <div class="p-12 text-center text-slate-500 dark:text-slate-400 text-xs">
            <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 flex items-center justify-center text-lg mx-auto mb-2">
                <i class="fa-solid fa-check"></i>
            </div>
            Geen support tickets gevonden voor dit filter.
        </div>
        @endif
    </div>

</div>
@endsection
