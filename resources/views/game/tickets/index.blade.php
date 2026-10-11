@extends('layouts.app')

@section('title', 'Help & Support Tickets - TBS Kliniek')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 border border-purple-200 text-purple-600 flex items-center justify-center text-xl shadow-2xs shrink-0">
                <i class="fa-solid fa-headset"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Support Tickets</h1>
                    <span class="bg-purple-100 text-purple-800 text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full border border-purple-200">
                        Helpdesk
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Meld een bug, stel een vraag aan het team of deel je ideeën en feedback voor TBS Kliniek.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('faq.index') }}" 
               class="px-3.5 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 font-bold text-xs transition flex items-center gap-1.5 shadow-2xs">
                <i class="fa-solid fa-circle-question text-sky-500"></i>
                <span>FAQ & Kennisbank</span>
            </a>
            <a href="{{ route('help.tickets.create') }}" 
               class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs transition flex items-center gap-2 shadow-xs cursor-pointer">
                <i class="fa-solid fa-plus"></i>
                <span>Nieuw Ticket Aanmaken</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats & Open Beta Notice -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-2xs flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-base border border-purple-100">
                    <i class="fa-solid fa-ticket"></i>
                </div>
                <div>
                    <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Totaal Tickets</div>
                    <div class="text-lg font-black text-slate-900 leading-tight">{{ $tickets->total() }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-2xs flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-base border border-amber-100">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
                <div>
                    <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">In Behandeling / Open</div>
                    <div class="text-lg font-black text-amber-700 leading-tight">{{ $openCount }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-2xs flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-base border border-emerald-100">
                    <i class="fa-solid fa-comments"></i>
                </div>
                <div>
                    <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Beantwoorde Tickets</div>
                    <div class="text-lg font-black text-emerald-700 leading-tight">{{ $answeredCount }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tickets Table / List -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-list-check text-purple-600 text-sm"></i>
                <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wide">Mijn Support Tickets</h2>
            </div>
            <span class="text-xs text-slate-500 font-medium">
                {{ $tickets->total() }} {{ Str::plural('ticket', $tickets->total()) }}
            </span>
        </div>

        @if($tickets->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-100 bg-slate-50/50">
                        <th class="py-3 px-4">TICKET #</th>
                        <th class="py-3 px-4">ONDERWERP</th>
                        <th class="py-3 px-4">CATEGORIE</th>
                        <th class="py-3 px-4 text-center">STATUS</th>
                        <th class="py-3 px-4 text-center">LAATSTE UPDATE</th>
                        <th class="py-3 px-4 text-right">ACTIE</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($tickets as $ticket)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-700">
                            {{ $ticket->ticket_number }}
                        </td>
                        <td class="py-3.5 px-4">
                            <a href="{{ route('help.tickets.show', $ticket) }}" class="font-bold text-slate-900 hover:text-purple-600 transition block">
                                {{ $ticket->subject }}
                            </a>
                            <span class="text-[11px] text-slate-400 truncate block max-w-xs">
                                {{ Str::limit($ticket->lastMessage?->message ?? '', 60) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[11px] font-semibold">
                                <i class="fa-solid {{ $ticket->category_icon }} text-[10px] text-slate-500"></i>
                                {{ $ticket->category_label }}
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
                            <a href="{{ route('help.tickets.show', $ticket) }}" 
                               class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-purple-50 text-slate-700 hover:text-purple-700 border border-slate-200 hover:border-purple-200 font-bold text-xs transition inline-flex items-center gap-1.5">
                                <span>Bekijk</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
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
        <div class="p-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-purple-50 text-purple-500 border border-purple-100 flex items-center justify-center text-2xl mx-auto mb-3 shadow-2xs">
                <i class="fa-solid fa-ticket"></i>
            </div>
            <h3 class="text-sm font-extrabold text-slate-900 mb-1">Geen support tickets gevonden</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto mb-4">
                Heb je een vraag, bug of feedback tijdens deze Open Beta van TBS Kliniek? Ons supportteam staat voor je klaar!
            </p>
            <a href="{{ route('help.tickets.create') }}" 
               class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs transition inline-flex items-center gap-2 shadow-xs">
                <i class="fa-solid fa-plus"></i>
                <span>Maak je eerste ticket aan</span>
            </a>
        </div>
        @endif
    </div>

</div>
@endsection
