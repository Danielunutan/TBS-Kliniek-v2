@extends('layouts.app')

@section('title', 'Ticket #' . $ticket->ticket_number . ' - ' . $ticket->subject)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Top Navigation / Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <a href="{{ route('help.tickets.index') }}" 
           class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-slate-800 transition">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Terug naar al mijn tickets</span>
        </a>

        <div class="flex items-center gap-2">
            @if($ticket->isOpen())
            <form action="{{ route('help.tickets.close', $ticket) }}" method="POST" onsubmit="return confirm('Weet je zeker dat je dit ticket wilt sluiten?');">
                @csrf
                <button type="submit" 
                        class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-700 border border-slate-200 hover:border-rose-200 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-lock text-xs"></i>
                    <span>Ticket Sluiten</span>
                </button>
            </form>
            @endif
            <a href="{{ route('help.tickets.create') }}" 
               class="px-3.5 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs transition flex items-center gap-1.5 shadow-2xs">
                <i class="fa-solid fa-plus"></i>
                <span>Nieuw Ticket</span>
            </a>
        </div>
    </div>

    <!-- Ticket Summary Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <span class="font-mono text-xs font-black text-purple-700 bg-purple-50 px-2.5 py-0.5 rounded-lg border border-purple-200">
                        {{ $ticket->ticket_number }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $ticket->status_badge_classes }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $ticket->status === 'gesloten' ? 'bg-slate-400' : 'bg-emerald-500' }}"></span>
                        {{ $ticket->status_label }}
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $ticket->priority_badge_classes }}">
                        Prioriteit: {{ $ticket->priority_label }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[10px] font-bold">
                        <i class="fa-solid {{ $ticket->category_icon }} text-[9px] text-slate-500"></i>
                        {{ $ticket->category_label }}
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">{{ $ticket->subject }}</h1>
            </div>

            <div class="text-right text-xs text-slate-400 font-medium shrink-0">
                <div>Aangemaakt: <strong class="text-slate-700">{{ $ticket->created_at->format('d-m-Y H:i') }}</strong></div>
                @if($ticket->closed_at)
                <div class="text-rose-600 font-bold mt-0.5">Gesloten op: {{ $ticket->closed_at->format('d-m-Y H:i') }}</div>
                @endif
            </div>
        </div>

        @if($ticket->status === 'gesloten')
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs text-slate-600">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                <span>Dit support ticket is gemarkeerd als afgehandeld en gesloten.</span>
            </div>
            <a href="{{ route('help.tickets.create') }}" class="font-bold text-purple-600 hover:text-purple-800 underline">
                Nieuwe vraag stellen &rarr;
            </a>
        </div>
        @endif
    </div>

    <!-- Messages Timeline -->
    <div class="space-y-4">
        @foreach($ticket->messages as $msg)
        <div class="bg-white border {{ $msg->is_staff ? 'border-purple-200 shadow-xs' : 'border-slate-200' }} rounded-2xl p-5 shadow-2xs space-y-3">
            
            <!-- Message Header -->
            <div class="flex items-center justify-between gap-3 border-b {{ $msg->is_staff ? 'border-purple-100' : 'border-slate-100' }} pb-3">
                <div class="flex items-center gap-3">
                    @if($msg->is_staff)
                    <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center text-lg font-bold shadow-xs">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-sm text-purple-900">{{ $msg->user?->name ? ucfirst($msg->user->name) : 'Daniel' }}</span>
                            <span class="px-2 py-0.5 rounded-md bg-purple-100 text-purple-800 text-[10px] font-black uppercase tracking-wider border border-purple-200">
                                MEDEWERKER
                            </span>
                        </div>
                        <span class="text-[11px] text-purple-600 font-medium">Officiële reactie van TBS Kliniek</span>
                    </div>
                    @else
                    <img src="{{ $msg->character?->avatar_url ?? asset('assets/logo.png') }}" 
                         alt="{{ $msg->character?->name ?? 'Speler' }}" 
                         class="w-10 h-10 rounded-xl object-cover border border-slate-200 shadow-2xs">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-sm text-slate-900">{{ $msg->character?->name ?? 'Jij' }}</span>
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-bold">
                                Speler
                            </span>
                        </div>
                        <span class="text-[11px] text-slate-400 font-medium">Level {{ $msg->character?->level ?? 1 }}</span>
                    </div>
                    @endif
                </div>

                <div class="text-xs text-slate-400 font-mono">
                    {{ $msg->created_at->format('d-m-Y H:i') }}
                    <span class="hidden sm:inline">({{ $msg->created_at->diffForHumans() }})</span>
                </div>
            </div>

            <!-- Message Body -->
            <div class="text-xs sm:text-sm text-slate-800 leading-relaxed whitespace-pre-line font-medium {{ $msg->is_staff ? 'bg-purple-50/50 p-4 rounded-xl border border-purple-100/70' : '' }}">
                {{ $msg->message }}
            </div>

        </div>
        @endforeach
    </div>

    <!-- Reply Box (if open) -->
    @if($ticket->isOpen())
    <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
        <div class="flex items-center gap-2 text-sm font-extrabold text-slate-900">
            <i class="fa-solid fa-reply text-purple-600"></i>
            <span>Antwoord of reageer op dit ticket</span>
        </div>

        <form action="{{ route('help.tickets.reply', $ticket) }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <textarea name="message" 
                          rows="4" 
                          placeholder="Typ hier je extra toelichting of reactie op de medewerker..."
                          class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 text-xs font-medium text-slate-900 placeholder-slate-400 outline-none transition resize-y"
                          required></textarea>
                @error('message')
                <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <p class="text-[11px] text-slate-400 font-medium">
                    Na het plaatsen van je reactie wordt de status automatisch op <strong>Open</strong> gezet.
                </p>

                <button type="submit" 
                        class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-extrabold text-xs transition shadow-xs cursor-pointer flex items-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Reactie Plaatsen</span>
                </button>
            </div>
        </form>
    </div>
    @endif

</div>
@endsection
