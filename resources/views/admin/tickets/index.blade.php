@extends('admin.layout')

@section('title', 'Support Tickets Beheer - TBS Kliniek Admin')

@section('admin-content')
<div class="space-y-6">

    <!-- Header & Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <a href="{{ route('admin.tickets.index', ['status' => 'open']) }}" 
           class="bg-white border {{ $currentStatus === 'open' ? 'border-amber-400 ring-2 ring-amber-100' : 'border-slate-200' }} rounded-xl p-4 shadow-2xs hover:border-amber-300 transition block">
            <div class="text-[10px] font-extrabold text-amber-700 uppercase tracking-wider">Open Tickets</div>
            <div class="text-2xl font-black text-amber-900 mt-1">{{ $stats['open'] }}</div>
            <div class="text-[11px] text-amber-600 mt-0.5">Wachten op eerste reactie</div>
        </a>

        <a href="{{ route('admin.tickets.index', ['status' => 'in_behandeling']) }}" 
           class="bg-white border {{ $currentStatus === 'in_behandeling' ? 'border-blue-400 ring-2 ring-blue-100' : 'border-slate-200' }} rounded-xl p-4 shadow-2xs hover:border-blue-300 transition block">
            <div class="text-[10px] font-extrabold text-blue-700 uppercase tracking-wider">In Behandeling</div>
            <div class="text-2xl font-black text-blue-900 mt-1">{{ $stats['in_behandeling'] }}</div>
            <div class="text-[11px] text-blue-600 mt-0.5">Onderzoek door staff</div>
        </a>

        <a href="{{ route('admin.tickets.index', ['status' => 'beantwoord']) }}" 
           class="bg-white border {{ $currentStatus === 'beantwoord' ? 'border-emerald-400 ring-2 ring-emerald-100' : 'border-slate-200' }} rounded-xl p-4 shadow-2xs hover:border-emerald-300 transition block">
            <div class="text-[10px] font-extrabold text-emerald-700 uppercase tracking-wider">Beantwoord</div>
            <div class="text-2xl font-black text-emerald-900 mt-1">{{ $stats['beantwoord'] }}</div>
            <div class="text-[11px] text-emerald-600 mt-0.5">Wacht op speler</div>
        </a>

        <a href="{{ route('admin.tickets.index', ['status' => 'gesloten']) }}" 
           class="bg-white border {{ $currentStatus === 'gesloten' ? 'border-slate-400 ring-2 ring-slate-100' : 'border-slate-200' }} rounded-xl p-4 shadow-2xs hover:border-slate-300 transition block">
            <div class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Gesloten / Afgehandeld</div>
            <div class="text-2xl font-black text-slate-700 mt-1">{{ $stats['gesloten'] }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Succesvol afgerond</div>
        </a>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-xs space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            
            <!-- Status Tabs -->
            <div class="flex flex-wrap items-center gap-1.5 text-xs font-bold">
                <a href="{{ route('admin.tickets.index', ['status' => 'actief']) }}" 
                   class="px-3 py-1.5 rounded-lg transition {{ $currentStatus === 'actief' ? 'bg-purple-600 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100' }}">
                    Alle Actieve ({{ $stats['open'] + $stats['in_behandeling'] + $stats['beantwoord'] }})
                </a>
                <a href="{{ route('admin.tickets.index', ['status' => 'open']) }}" 
                   class="px-3 py-1.5 rounded-lg transition {{ $currentStatus === 'open' ? 'bg-amber-600 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100' }}">
                    Open ({{ $stats['open'] }})
                </a>
                <a href="{{ route('admin.tickets.index', ['status' => 'in_behandeling']) }}" 
                   class="px-3 py-1.5 rounded-lg transition {{ $currentStatus === 'in_behandeling' ? 'bg-blue-600 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100' }}">
                    In Behandeling ({{ $stats['in_behandeling'] }})
                </a>
                <a href="{{ route('admin.tickets.index', ['status' => 'beantwoord']) }}" 
                   class="px-3 py-1.5 rounded-lg transition {{ $currentStatus === 'beantwoord' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100' }}">
                    Beantwoord ({{ $stats['beantwoord'] }})
                </a>
                <a href="{{ route('admin.tickets.index', ['status' => 'gesloten']) }}" 
                   class="px-3 py-1.5 rounded-lg transition {{ $currentStatus === 'gesloten' ? 'bg-slate-700 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100' }}">
                    Gesloten ({{ $stats['gesloten'] }})
                </a>
                <a href="{{ route('admin.tickets.index', ['status' => 'all']) }}" 
                   class="px-3 py-1.5 rounded-lg transition {{ $currentStatus === 'all' ? 'bg-slate-900 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100' }}">
                    Alles ({{ $stats['totaal'] }})
                </a>
            </div>

            <!-- Search Form -->
            <form action="{{ route('admin.tickets.index') }}" method="GET" class="flex items-center gap-2">
                <input type="hidden" name="status" value="{{ $currentStatus }}">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Zoek ticket, speler, email..." 
                           class="pl-8 pr-3 py-1.5 text-xs rounded-xl border border-slate-200 focus:border-purple-500 focus:ring-1 focus:ring-purple-200 outline-none w-56 sm:w-64">
                </div>
                <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition cursor-pointer">
                    Filter
                </button>
                @if(request('search'))
                <a href="{{ route('admin.tickets.index', ['status' => $currentStatus]) }}" class="text-xs text-rose-600 hover:underline">
                    Wis
                </a>
                @endif
            </form>

        </div>
    </div>

    <!-- Tickets Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-headset text-purple-600"></i>
                <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wide">Support Tickets Overzicht</h2>
            </div>
            <span class="text-xs text-slate-500 font-medium">
                {{ $tickets->total() }} gevonden
            </span>
        </div>

        @if($tickets->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-100 bg-slate-50/50">
                        <th class="py-3 px-4">TICKET #</th>
                        <th class="py-3 px-4">PATIËNT / SPELER</th>
                        <th class="py-3 px-4">ONDERWERP & CATEGORIE</th>
                        <th class="py-3 px-4 text-center">PRIORITEIT</th>
                        <th class="py-3 px-4 text-center">STATUS</th>
                        <th class="py-3 px-4 text-center">LAATSTE REACTIE</th>
                        <th class="py-3 px-4 text-right">ACTIE</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($tickets as $ticket)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-800">
                            {{ $ticket->ticket_number }}
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2.5">
                                <img src="{{ $ticket->character?->avatar_url ?? asset('assets/logo.png') }}" 
                                     alt="{{ $ticket->character?->name ?? 'Speler' }}" 
                                     class="w-7 h-7 rounded-lg object-cover border border-slate-200 shadow-2xs shrink-0">
                                <div>
                                    <div class="font-extrabold text-slate-900 leading-tight">
                                        {{ $ticket->character?->name ?? 'Verwijderd' }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        {{ $ticket->user?->email }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 max-w-xs">
                            <a href="{{ route('admin.tickets.show', $ticket) }}" class="font-extrabold text-slate-900 hover:text-purple-600 transition block truncate">
                                {{ $ticket->subject }}
                            </a>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-500">
                                    <i class="fa-solid {{ $ticket->category_icon }} text-[9px]"></i>
                                    {{ $ticket->category_label }}
                                </span>
                                @if($ticket->status === 'beantwoord')
                                <span class="text-[9px] bg-emerald-50 text-emerald-700 px-1.5 py-0.2 rounded-sm border border-emerald-200 font-bold inline-flex items-center gap-1">
                                    <i class="fa-solid fa-check text-[8px]"></i> Beantwoord door staff
                                </span>
                                @elseif($ticket->status === 'gesloten')
                                <span class="text-[9px] bg-slate-100 text-slate-600 px-1.5 py-0.2 rounded-sm border border-slate-200 font-bold inline-flex items-center gap-1">
                                    <i class="fa-solid fa-lock text-[8px]"></i> Gesloten
                                </span>
                                @elseif($ticket->last_reply_by_staff)
                                <span class="text-[9px] bg-purple-50 text-purple-700 px-1.5 py-0.2 rounded-sm border border-purple-200 font-bold">
                                    Wacht op speler
                                </span>
                                @else
                                <span class="text-[9px] bg-amber-50 text-amber-700 px-1.5 py-0.2 rounded-sm border border-amber-200 font-bold inline-flex items-center gap-1 animate-pulse">
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
                        <td class="py-3.5 px-4 text-center text-slate-500 text-[11px]">
                            {{ $ticket->updated_at->diffForHumans() }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            @if($ticket->status === 'beantwoord')
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.tickets.show', $ticket) }}" 
                                   class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold text-xs transition inline-flex items-center gap-1 shadow-2xs">
                                    <i class="fa-solid fa-eye text-[10px]"></i>
                                    <span>Bekijken</span>
                                </a>
                                <form action="{{ route('admin.tickets.status', $ticket) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="status" value="gesloten">
                                    <button type="submit" title="Direct sluiten & afronden" class="px-2 py-1.5 rounded-lg bg-white hover:bg-rose-50 text-slate-500 hover:text-rose-700 border border-slate-200 hover:border-rose-200 text-xs font-bold transition inline-flex items-center gap-1 cursor-pointer">
                                        <i class="fa-solid fa-lock text-[10px]"></i>
                                        <span>Sluit</span>
                                    </button>
                                </form>
                            </div>
                            @elseif($ticket->status === 'gesloten')
                            <a href="{{ route('admin.tickets.show', $ticket) }}" 
                               class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs transition inline-flex items-center gap-1">
                                <i class="fa-solid fa-folder-open text-[10px]"></i>
                                <span>Dossier</span>
                            </a>
                            @else
                            <a href="{{ route('admin.tickets.show', $ticket) }}" 
                               class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs transition inline-flex items-center gap-1.5 shadow-2xs">
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

        <div class="p-4 border-t border-slate-100">
            {{ $tickets->links() }}
        </div>
        @else
        <div class="p-12 text-center text-slate-500 text-xs">
            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-lg mx-auto mb-2">
                <i class="fa-solid fa-check"></i>
            </div>
            Geen support tickets gevonden voor dit filter.
        </div>
        @endif
    </div>

</div>
@endsection
