@extends('admin.layout')

@section('title', 'Ticket #' . $ticket->ticket_number . ' Beantwoorden - TBS Kliniek Admin')

@section('admin-content')
<div class="space-y-6">

    <!-- Top Navigation / Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <a href="{{ route('admin.tickets.index') }}" 
           class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-slate-800 transition">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Terug naar alle tickets</span>
        </a>

        <!-- Quick Status Change Form -->
        <form action="{{ route('admin.tickets.status', $ticket) }}" method="POST" class="flex items-center gap-2">
            @csrf
            <span class="text-xs font-bold text-slate-500">Status wijzigen:</span>
            <select name="status" 
                    onchange="this.form.submit()" 
                    class="text-xs font-bold px-3 py-1.5 rounded-xl border border-slate-200 bg-white shadow-2xs outline-none">
                <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>🟡 Open</option>
                <option value="in_behandeling" {{ $ticket->status === 'in_behandeling' ? 'selected' : '' }}>🔵 In Behandeling</option>
                <option value="beantwoord" {{ $ticket->status === 'beantwoord' ? 'selected' : '' }}>🟢 Beantwoord</option>
                <option value="gesloten" {{ $ticket->status === 'gesloten' ? 'selected' : '' }}>⚪ Gesloten</option>
            </select>
        </form>
    </div>

    <!-- Ticket & Player Info Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Main Thread & Reply -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Ticket Header Info -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-xs space-y-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-mono text-xs font-black text-purple-700 bg-purple-50 px-2.5 py-0.5 rounded-lg border border-purple-200">
                        {{ $ticket->ticket_number }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $ticket->status_badge_classes }}">
                        {{ $ticket->status_label }}
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $ticket->priority_badge_classes }}">
                        {{ $ticket->priority_label }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[10px] font-bold">
                        <i class="fa-solid {{ $ticket->category_icon }} text-[9px]"></i>
                        {{ $ticket->category_label }}
                    </span>
                </div>

                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                    {{ $ticket->subject }}
                </h1>

                <div class="text-xs text-slate-400 font-medium flex flex-wrap gap-x-4 gap-y-1">
                    <span>Aangemaakt: <strong class="text-slate-700">{{ $ticket->created_at->format('d-m-Y H:i') }}</strong></span>
                    <span>Laatste update: <strong class="text-slate-700">{{ $ticket->updated_at->diffForHumans() }}</strong></span>
                </div>
            </div>

            <!-- GANG OVERSTAP BONUS BEHEER PANEEL -->
            @if($ticket->isGangTransfer())
            <div class="rounded-2xl border-2 {{ $ticket->isGangTransferApproved() ? 'border-emerald-300 bg-emerald-50/50' : 'border-amber-300 bg-gradient-to-br from-amber-50/80 via-white to-purple-50/60' }} p-5 sm:p-6 shadow-sm space-y-4">
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b {{ $ticket->isGangTransferApproved() ? 'border-emerald-200' : 'border-amber-200' }}">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl {{ $ticket->isGangTransferApproved() ? 'bg-emerald-500 text-white' : 'bg-amber-400 text-slate-950' }} flex items-center justify-center text-lg font-black shadow-xs shrink-0">
                            <i class="fa-solid fa-crown"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-black text-slate-900">Gang Overstap Bonus Aanvraag</h3>
                                @if($ticket->isGangTransferApproved())
                                <span class="px-2 py-0.5 rounded-full bg-emerald-600 text-white text-[9px] font-black uppercase">
                                    Goedgekeurd & Actief
                                </span>
                                @else
                                <span class="px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 text-[9px] font-black uppercase animate-pulse">
                                    Wacht op Goedkeuring
                                </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-600">Automatische bende-aanmaak, kluisstorting en 31 dagen VIP activatie.</p>
                        </div>
                    </div>

                    @if($ticket->isGangTransferApproved())
                    <div class="text-xs text-emerald-800 font-bold bg-emerald-100/80 px-3 py-1.5 rounded-xl border border-emerald-300">
                        <i class="fa-solid fa-circle-check text-emerald-600 mr-1"></i>
                        Bende #{{ $ticket->metadata['created_gang_id'] ?? '' }} ({{ $ticket->metadata['created_gang_name'] ?? '' }} [{{ $ticket->metadata['created_gang_tag'] ?? '' }}])
                    </div>
                    @endif
                </div>

                <!-- Submitted Application Details -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-xl bg-white/90 border border-slate-200 space-y-1">
                        <span class="text-[10px] font-bold uppercase text-slate-400 block">Gewenste Bende & Tag</span>
                        <div class="flex items-center gap-2">
                            <span class="font-black text-slate-900 text-sm">{{ $ticket->metadata['gang_name'] ?? 'Onbekend' }}</span>
                            <span class="px-2 py-0.5 rounded bg-slate-900 text-amber-300 font-mono text-xs font-black">
                                [{{ $ticket->metadata['gang_tag'] ?? '-' }}]
                            </span>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-white/90 border border-slate-200 space-y-1">
                        <span class="text-[10px] font-bold uppercase text-slate-400 block">Link naar Externe Game/Gang</span>
                        @if(!empty($ticket->metadata['external_gang_link']))
                        <a href="{{ $ticket->metadata['external_gang_link'] }}" target="_blank" rel="noopener noreferrer" 
                           class="font-bold text-purple-700 hover:text-purple-900 hover:underline flex items-center gap-1.5 truncate">
                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                            <span class="truncate">{{ $ticket->metadata['external_gang_link'] }}</span>
                        </a>
                        @else
                        <span class="text-slate-400 italic">Geen link opgegeven</span>
                        @endif
                    </div>

                    @if(!empty($ticket->metadata['member_names']))
                    <div class="sm:col-span-2 p-3 rounded-xl bg-white/90 border border-slate-200 space-y-1">
                        <span class="text-[10px] font-bold uppercase text-slate-400 block">Aangemelde Overstappende Leden</span>
                        <p class="text-xs text-slate-700 whitespace-pre-line font-medium">{{ $ticket->metadata['member_names'] }}</p>
                    </div>
                    @endif

                    @if(!empty($ticket->metadata['proof_details']))
                    <div class="sm:col-span-2 p-3 rounded-xl bg-white/90 border border-slate-200 space-y-1">
                        <span class="text-[10px] font-bold uppercase text-slate-400 block">Toelichting / Bewijs</span>
                        <p class="text-xs text-slate-700 whitespace-pre-line font-medium">{{ $ticket->metadata['proof_details'] }}</p>
                    </div>
                    @endif
                </div>

                @if(! $ticket->isGangTransferApproved())
                <!-- APPROVAL ACTION FORM (1-CLICK EXECUTE) -->
                <form action="{{ route('admin.tickets.approve_gang', $ticket) }}" method="POST" class="pt-3 border-t border-amber-200/80 space-y-4">
                    @csrf
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                        <div class="sm:col-span-1">
                            <label class="block text-[10px] font-extrabold uppercase text-slate-600 mb-1">Bendenaam Bevestigen</label>
                            <input type="text" name="gang_name" value="{{ old('gang_name', $ticket->metadata['gang_name'] ?? '') }}" required class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white font-bold text-xs text-slate-900 outline-none focus:border-amber-500">
                        </div>

                        <div>
                            <label class="block text-[10px] font-extrabold uppercase text-slate-600 mb-1">Tag (2-4 tekens)</label>
                            <input type="text" name="gang_tag" maxlength="4" value="{{ old('gang_tag', $ticket->metadata['gang_tag'] ?? '') }}" required class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white font-mono font-bold text-xs uppercase text-slate-900 outline-none focus:border-amber-500">
                        </div>

                        <div>
                            <label class="block text-[10px] font-extrabold uppercase text-slate-600 mb-1">Kluisgeld Bonus (€)</label>
                            <input type="number" name="bonus_cash" value="5000000" step="500000" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white font-mono font-bold text-xs text-emerald-700 outline-none focus:border-amber-500">
                        </div>
                    </div>

                    <button type="submit" 
                            onclick="return confirm('Weet je zeker dat je deze Gang Overstap Bonus wilt goedkeuren? Bende wordt direct opgericht met €5.000.000 kluisgeld en 31 dagen VIP voor de bende!');"
                            class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-amber-500 via-amber-600 to-amber-700 hover:from-amber-600 hover:to-amber-800 active:scale-[0.99] text-white font-black text-xs uppercase tracking-wider shadow-md flex items-center justify-center gap-2 cursor-pointer transition">
                        <i class="fa-solid fa-crown text-amber-200 text-base"></i>
                        <span>Gang Overstap Goedkeuren & Bende Direct Oprichten (€ 5.000.000 + 31 Dagen VIP)</span>
                    </button>
                </form>
                @else
                <div class="p-3 rounded-xl bg-emerald-100/70 border border-emerald-300 text-xs text-emerald-900 flex items-center gap-2">
                    <i class="fa-solid fa-check-double text-emerald-600 text-base"></i>
                    <span>Deze bonus is goedgekeurd door <strong>{{ $ticket->metadata['approved_by_name'] ?? 'Staff' }}</strong> op {{ \Carbon\Carbon::parse($ticket->metadata['bonus_approved_at'])->format('d-m-Y H:i') }}. Bende: <strong>{{ $ticket->metadata['created_gang_name'] ?? '' }} [{{ $ticket->metadata['created_gang_tag'] ?? '' }}]</strong>.</span>
                </div>
                @endif

            </div>
            @endif

            <!-- Messages Thread -->
            <div class="space-y-4">
                @foreach($ticket->messages as $msg)
                <div class="bg-white border {{ $msg->is_staff ? 'border-purple-200 shadow-xs' : 'border-slate-200' }} rounded-2xl p-5 shadow-2xs space-y-3">
                    
                    <div class="flex items-center justify-between gap-3 border-b {{ $msg->is_staff ? 'border-purple-100' : 'border-slate-100' }} pb-3">
                        <div class="flex items-center gap-3">
                            @if($msg->is_staff)
                            <div class="w-9 h-9 rounded-xl bg-purple-600 text-white flex items-center justify-center text-base font-bold shrink-0">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-extrabold text-sm text-purple-900">{{ $msg->user?->name ? ucfirst($msg->user->name) : 'Daniel' }}</span>
                                    <span class="px-2 py-0.5 rounded-md bg-purple-100 text-purple-800 text-[10px] font-black uppercase">
                                        Medewerker
                                    </span>
                                </div>
                                <span class="text-[11px] text-purple-600/80 font-medium">TBS Kliniek Medewerker</span>
                            </div>
                            @else
                            <img src="{{ $msg->character?->avatar_url ?? asset('assets/logo.png') }}" 
                                 alt="{{ $msg->character?->name ?? 'Speler' }}" 
                                 class="w-9 h-9 rounded-xl object-cover border border-slate-200 shrink-0">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-extrabold text-sm text-slate-900">{{ $msg->character?->name ?? 'Speler' }}</span>
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-bold">
                                        Patiënt
                                    </span>
                                </div>
                                <span class="text-[11px] text-slate-400">Level {{ $msg->character?->level ?? 1 }}</span>
                            </div>
                            @endif
                        </div>

                        <span class="text-xs text-slate-400 font-mono">
                            {{ $msg->created_at->format('d-m-Y H:i') }}
                        </span>
                    </div>

                    <div class="text-xs sm:text-sm text-slate-800 dark:text-slate-100 leading-relaxed whitespace-pre-line font-medium {{ $msg->is_staff ? 'bg-purple-50/70 dark:bg-slate-950/70 p-4 sm:p-5 rounded-xl border border-purple-200/80 dark:border-purple-900/60' : 'p-1' }}">
                        {{ $msg->message }}
                    </div>

                </div>
                @endforeach
            </div>

            <!-- Staff Reply Box -->
            <div class="bg-white border border-purple-200 rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
                <div class="flex items-center gap-2 text-sm font-extrabold text-slate-900">
                    <i class="fa-solid fa-reply text-purple-600"></i>
                    <span>Antwoord sturen naar {{ $ticket->character->name }}</span>
                </div>

                <form action="{{ route('admin.tickets.reply', $ticket) }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <textarea name="message" 
                                  rows="5" 
                                  placeholder="Typ hier het officiële antwoord namens TBS Kliniek staff..."
                                  class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 text-xs font-medium text-slate-900 placeholder-slate-400 outline-none transition resize-y"
                                  required></textarea>
                        @error('message')
                        <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status after reply -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-2">
                        <div class="flex items-center gap-3">
                            <label for="status_select" class="text-xs font-bold text-slate-600">Status na verzenden:</label>
                            <select id="status_select" 
                                    name="status" 
                                    class="text-xs font-bold px-3 py-1.5 rounded-xl border border-slate-200 bg-white outline-none">
                                <option value="beantwoord" selected>🟢 Beantwoord (Wacht op speler)</option>
                                <option value="in_behandeling">🔵 In Behandeling (Wordt onderzocht)</option>
                                <option value="gesloten">⚪ Gesloten (Volledig afgehandeld)</option>
                                <option value="open">🟡 Open laten</option>
                            </select>
                        </div>

                        <button type="submit" 
                                class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-extrabold text-xs transition shadow-xs cursor-pointer flex items-center justify-center gap-2">
                            <i class="fa-solid fa-paper-plane"></i>
                            <span>Verstuur Reactie</span>
                        </button>
                    </div>

                </form>
            </div>

        </div>

        <!-- Right Col: Player Overview Card -->
        <div class="space-y-4">
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs space-y-4">
                <div class="flex items-center gap-2 text-xs font-extrabold text-slate-900 uppercase tracking-wider pb-3 border-b border-slate-100">
                    <i class="fa-solid fa-user text-purple-600"></i>
                    <span>Patiënt Dossier</span>
                </div>

                <div class="flex items-center gap-3">
                    <img src="{{ $ticket->character->avatar_url }}" 
                         alt="{{ $ticket->character->name }}" 
                         class="w-12 h-12 rounded-xl object-cover border border-slate-200 shadow-2xs">
                    <div>
                        <div class="font-extrabold text-sm text-slate-900">{{ $ticket->character->name }}</div>
                        <div class="text-xs text-purple-700 font-bold">Level {{ $ticket->character->level }} &bull; {{ $ticket->character->title ?? 'Patiënt' }}</div>
                        <div class="text-[11px] text-slate-400">{{ $ticket->user->email }}</div>
                    </div>
                </div>

                <div class="space-y-2 text-xs divide-y divide-slate-100 font-medium">
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-slate-500">VIP Status:</span>
                        @if($ticket->character->is_vip)
                        <span class="text-amber-700 font-black">⭐ VIP ACTIEF</span>
                        @else
                        <span class="text-slate-400">Regulier</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-slate-500">Cash op zak:</span>
                        <strong class="font-mono text-slate-800">€{{ number_format($ticket->character->cash, 0, ',', '.') }}</strong>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-slate-500">Bank saldo:</span>
                        <strong class="font-mono text-slate-800">€{{ number_format($ticket->character->bank, 0, ',', '.') }}</strong>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-slate-500">Gang:</span>
                        <span class="font-bold text-slate-800">{{ $ticket->character->gang?->name ?? 'Geen gang' }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-slate-500">In Isoleercel:</span>
                        @if($ticket->character->isImprisoned())
                        <span class="text-rose-600 font-bold">🔒 Ja ({{ $ticket->character->remainingPrisonSeconds() }}s)</span>
                        @else
                        <span class="text-emerald-600 font-bold">🔓 Vrij</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-slate-500">Lid sinds:</span>
                        <span class="text-slate-700">{{ $ticket->user->created_at->format('d-m-Y') }}</span>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100">
                    <a href="{{ route('admin.players', ['search' => $ticket->character->name]) }}" 
                       class="w-full py-2 px-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-bold transition flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-pen-to-square text-slate-500"></i>
                        <span>Bewerk Speler in Admin</span>
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
