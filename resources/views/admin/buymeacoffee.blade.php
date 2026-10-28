@extends('admin.layout')

@section('admin-content')
<div class="space-y-6" x-data="{ 
    manualModalOpen: false,
    linkModalOpen: false,
    selectedOrder: null,
    searchChar: '',
    openLinkModal(order) {
        this.selectedOrder = order;
        this.linkModalOpen = true;
    }
}">

    <!-- Top Info & Action Bar -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600 font-black text-lg">
                    <i class="fa-solid fa-mug-hot"></i>
                </div>
                <div>
                    <h2 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">Buy Me a Coffee Koppelingen &amp; Memberships</h2>
                    <p class="text-xs text-slate-500">
                        Koppel supporters die hun accountnaam vergeten zijn aan hun in-game personage. VIP privileges verlopen automatisch bij opzeggen.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" @click="manualModalOpen = true" 
                    class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-xs font-black shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-plus"></i> Handmatige Donatie Invoeren
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Totaal -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-2xs">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Totaal Registraties</span>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($totalOrders) }}</div>
            <span class="text-[11px] text-slate-500 font-medium">Donaties &amp; lidmaatschappen</span>
        </div>

        <!-- Ongekoppeld -->
        <div class="bg-white border {{ $unmatchedCount > 0 ? 'border-amber-300 bg-amber-50/40 ring-2 ring-amber-400/20' : 'border-slate-200' }} rounded-2xl p-4 shadow-2xs">
            <span class="text-[10px] font-black uppercase tracking-wider {{ $unmatchedCount > 0 ? 'text-amber-700 font-extrabold' : 'text-slate-400' }} block flex items-center justify-between">
                <span>Ongekoppeld</span>
                @if($unmatchedCount > 0)
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                @endif
            </span>
            <div class="text-2xl font-black {{ $unmatchedCount > 0 ? 'text-amber-700' : 'text-slate-900' }} mt-1">{{ number_format($unmatchedCount) }}</div>
            <span class="text-[11px] {{ $unmatchedCount > 0 ? 'text-amber-700 font-bold' : 'text-slate-500' }} font-medium">
                {{ $unmatchedCount > 0 ? 'Wacht op handmatige koppeling' : 'Alles netjes verwerkt' }}
            </span>
        </div>

        <!-- Actieve VIP Memberships -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-2xs">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Actieve Memberships</span>
            <div class="text-2xl font-black text-purple-700 mt-1">{{ number_format($activeMembershipsCount) }}</div>
            <span class="text-[11px] text-slate-500 font-medium">Doorlopend VIP actief</span>
        </div>

        <!-- Opgezegd -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-2xs">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Opgezegd / Verlopen</span>
            <div class="text-2xl font-black text-slate-700 mt-1">{{ number_format($cancelledCount) }}</div>
            <span class="text-[11px] text-slate-500 font-medium">VIP status ingetrokken</span>
        </div>
    </div>

    <!-- ONGEKOPPELDE ORDERS HIGHLIGHT SECTIE (Als er ongekoppelde orders zijn) -->
    @php
        $unmatchedOrders = \App\Models\BuyMeACoffeeOrder::where('status', 'unmatched')->latest()->get();
    @endphp

    @if($unmatchedOrders->isNotEmpty())
    <div class="bg-amber-50 border border-amber-300 rounded-2xl p-5 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-amber-900 font-black text-sm sm:text-base">
                <i class="fa-solid fa-triangle-exclamation text-amber-600 text-lg"></i>
                <span>Let op: Er zijn {{ $unmatchedOrders->count() }} ongekoppelde Buy Me a Coffee donaties!</span>
            </div>
            <span class="text-xs font-bold text-amber-800 bg-amber-200/80 px-2.5 py-0.5 rounded-full">
                Geen tag/naam ingevuld
            </span>
        </div>
        <p class="text-xs text-amber-800 leading-relaxed">
            Onderstaande supporters hebben gedoneerd of een lidmaatschap gestart, maar zijn niet automatisch herkend door het systeem. Koppel hen direct aan de juiste speler zodat hun VIP status en privileges worden geactiveerd.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 pt-1">
            @foreach($unmatchedOrders as $unmatched)
            <div class="bg-white border border-amber-200 rounded-xl p-4 shadow-2xs space-y-3">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="font-extrabold text-slate-900 text-sm">{{ $unmatched->supporter_name ?: 'Onbekende Supporter' }}</div>
                        <div class="text-[11px] text-slate-500 font-mono">{{ $unmatched->supporter_email ?: 'Geen e-mailadres' }}</div>
                    </div>
                    <span class="font-black text-sm text-emerald-600 font-mono bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                        {{ $unmatched->formatted_amount }}
                    </span>
                </div>

                <div class="text-xs space-y-1 bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="text-slate-400 font-medium">Type:</span>
                        <span class="font-bold text-purple-700">{{ $unmatched->display_tier }}</span>
                    </div>
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="text-slate-400 font-medium">Datum:</span>
                        <span class="text-slate-600 font-mono">{{ $unmatched->created_at->format('d-m-Y H:i') }}</span>
                    </div>
                    @if($unmatched->message)
                    <div class="pt-1 border-t border-slate-200/60 text-slate-700 italic text-[11px]">
                        "{{ $unmatched->message }}"
                    </div>
                    @endif
                </div>

                <button type="button" @click="openLinkModal({{ json_encode($unmatched) }})"
                        class="w-full py-2 px-3 rounded-lg bg-amber-500 hover:bg-amber-600 active:scale-95 text-white font-extrabold text-xs transition flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer">
                    <i class="fa-solid fa-link"></i> Koppel aan Speler &amp; Activeer VIP
                </button>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Search & Filter Controls -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
        <!-- Filter Tabs -->
        <div class="flex flex-wrap items-center gap-1.5 text-xs font-bold">
            <a href="{{ route('admin.buymeacoffee.index', ['filter' => 'all', 'search' => request('search')]) }}"
               class="px-3 py-1.5 rounded-lg transition {{ $filter === 'all' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                Alles ({{ $totalOrders }})
            </a>
            <a href="{{ route('admin.buymeacoffee.index', ['filter' => 'unmatched', 'search' => request('search')]) }}"
               class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 {{ $filter === 'unmatched' ? 'bg-amber-500 text-white' : 'text-amber-700 bg-amber-50 hover:bg-amber-100' }}">
                <span>Ongekoppeld</span>
                @if($unmatchedCount > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $filter === 'unmatched' ? 'bg-white text-amber-600' : 'bg-amber-500 text-white' }}">
                    {{ $unmatchedCount }}
                </span>
                @endif
            </a>
            <a href="{{ route('admin.buymeacoffee.index', ['filter' => 'matched', 'search' => request('search')]) }}"
               class="px-3 py-1.5 rounded-lg transition {{ $filter === 'matched' ? 'bg-purple-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                Gekoppeld
            </a>
            <a href="{{ route('admin.buymeacoffee.index', ['filter' => 'memberships', 'search' => request('search')]) }}"
               class="px-3 py-1.5 rounded-lg transition {{ $filter === 'memberships' ? 'bg-purple-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                Memberships ({{ $activeMembershipsCount }})
            </a>
            <a href="{{ route('admin.buymeacoffee.index', ['filter' => 'cancelled', 'search' => request('search')]) }}"
               class="px-3 py-1.5 rounded-lg transition {{ $filter === 'cancelled' ? 'bg-rose-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                Opgezegd ({{ $cancelledCount }})
            </a>
        </div>

        <!-- Search Form -->
        <form method="GET" action="{{ route('admin.buymeacoffee.index') }}" class="flex items-center gap-2 w-full md:w-auto">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <div class="relative w-full md:w-64">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Zoek supporter, e-mail of speler..."
                       class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
            </div>
            @if(request('search'))
            <a href="{{ route('admin.buymeacoffee.index', ['filter' => $filter]) }}" class="px-2.5 py-1.5 text-xs font-bold text-slate-500 hover:text-slate-800">
                Wissen
            </a>
            @endif
        </form>
    </div>

    <!-- Alle Orders Tabel -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                        <th class="py-3 px-4">Datum &amp; ID</th>
                        <th class="py-3 px-4">Supporter</th>
                        <th class="py-3 px-4">Type &amp; Tier</th>
                        <th class="py-3 px-4">Bedrag</th>
                        <th class="py-3 px-4">Bericht</th>
                        <th class="py-3 px-4">Gekoppelde Speler</th>
                        <th class="py-3 px-4">Toegekende Beloningen</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Acties</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($orders as $order)
                    <tr class="hover:bg-slate-50/80 transition-colors {{ $order->status === 'unmatched' ? 'bg-amber-50/30' : '' }}">
                        <!-- Datum & ID -->
                        <td class="py-3 px-4 whitespace-nowrap">
                            <div class="font-mono text-slate-700 font-bold">{{ $order->created_at->format('d-m-Y H:i') }}</div>
                            <div class="text-[10px] text-slate-400 font-mono truncate max-w-[120px]" title="{{ $order->external_id }}">
                                #{{ $order->id }} &bull; {{ $order->external_id ?: 'webhook' }}
                            </div>
                        </td>

                        <!-- Supporter -->
                        <td class="py-3 px-4">
                            <div class="font-extrabold text-slate-900">{{ $order->supporter_name ?: 'Onbekend' }}</div>
                            <div class="text-[11px] text-slate-500 font-mono">{{ $order->supporter_email ?: '-' }}</div>
                        </td>

                        <!-- Type & Tier -->
                        <td class="py-3 px-4">
                            @if($order->is_membership)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-purple-100 text-purple-800 border border-purple-200">
                                <i class="fa-solid fa-crown text-[9px]"></i> {{ $order->display_tier }}
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200">
                                <i class="fa-solid fa-mug-hot text-[9px]"></i> {{ $order->display_tier }}
                            </span>
                            @endif
                        </td>

                        <!-- Bedrag -->
                        <td class="py-3 px-4 whitespace-nowrap font-mono font-black text-emerald-600">
                            {{ $order->formatted_amount }}
                        </td>

                        <!-- Bericht -->
                        <td class="py-3 px-4 max-w-xs">
                            @if($order->message)
                            <span class="text-slate-600 italic truncate block max-w-[200px]" title="{{ $order->message }}">
                                "{{ $order->message }}"
                            </span>
                            @else
                            <span class="text-slate-400 text-[11px]">-</span>
                            @endif
                        </td>

                        <!-- Gekoppelde Speler -->
                        <td class="py-3 px-4 whitespace-nowrap">
                            @if($order->character)
                            <div class="flex items-center gap-2">
                                <img src="{{ $order->character->avatar_url }}" alt="{{ $order->character->name }}" class="w-6 h-6 rounded-full object-cover border border-slate-200">
                                <div>
                                    <a href="{{ route('profile.show', $order->character->name) }}" class="font-extrabold text-slate-900 hover:text-purple-600 transition">
                                        {{ $order->character->name }}
                                    </a>
                                    <div class="text-[10px] text-slate-400">
                                        Lvl {{ $order->character->level }} &bull; {{ $order->character->is_vip ? 'VIP actief' : 'Geen VIP' }}
                                    </div>
                                </div>
                            </div>
                            @else
                            <span class="text-amber-700 bg-amber-100/80 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider border border-amber-200">
                                Niet gekoppeld
                            </span>
                            @endif
                        </td>

                        <!-- Toegekende Beloningen -->
                        <td class="py-3 px-4">
                            @if($order->isLinked())
                            <div class="space-y-0.5">
                                <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wider text-emerald-800 bg-emerald-100/80 px-2 py-0.5 rounded border border-emerald-200">
                                    <i class="fa-solid fa-circle-check text-emerald-600"></i> Uitgekeerd
                                </span>
                                <div class="text-[11px] text-slate-700 font-bold leading-tight max-w-[220px]">
                                    {{ $order->rewards_summary }}
                                </div>
                            </div>
                            @else
                            <div class="space-y-0.5">
                                <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wider text-amber-800 bg-amber-100/80 px-2 py-0.5 rounded border border-amber-300">
                                    <i class="fa-solid fa-hourglass-half text-amber-600"></i> Wacht op koppeling
                                </span>
                                <div class="text-[10px] text-slate-400 italic">
                                    {{ $order->rewards_summary }}
                                </div>
                            </div>
                            @endif
                        </td>

                        <!-- Status -->
                        <td class="py-3 px-4 whitespace-nowrap">
                            @if($order->status === 'cancelled' || $order->membership_status === 'cancelled')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-200">
                                <i class="fa-solid fa-ban text-[9px]"></i> Opgezegd
                            </span>
                            @elseif($order->status === 'unmatched')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-300">
                                <i class="fa-solid fa-clock text-[9px]"></i> Wacht op Speler
                            </span>
                            @elseif($order->status === 'manually_linked')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-blue-100 text-blue-800 border border-blue-200">
                                <i class="fa-solid fa-link text-[9px]"></i> Handmatig
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                                <i class="fa-solid fa-check text-[9px]"></i> Automatisch
                            </span>
                            @endif
                        </td>

                        <!-- Acties -->
                        <td class="py-3 px-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                @if(!$order->character_id || $order->status === 'unmatched')
                                <button type="button" @click="openLinkModal({{ json_encode($order) }})"
                                        class="px-2.5 py-1 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-[11px] shadow-2xs transition flex items-center gap-1 cursor-pointer">
                                    <i class="fa-solid fa-link"></i> Koppelen
                                </button>
                                @endif

                                @if($order->character_id && $order->status !== 'cancelled')
                                <form action="{{ route('admin.buymeacoffee.regrant', $order) }}" method="POST"
                                      onsubmit="return confirm('Weet je zeker dat je de beloningen opnieuw wilt uitkeren aan {{ $order->character?->name }}?');">
                                    @csrf
                                    <button type="submit" title="Beloningen opnieuw uitkeren aan speler"
                                            class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-extrabold text-[11px] transition flex items-center gap-1 cursor-pointer">
                                        <i class="fa-solid fa-gift"></i> Uitkeren
                                    </button>
                                </form>
                                @endif

                                @if($order->character_id && $order->is_membership && $order->status !== 'cancelled')
                                <form action="{{ route('admin.buymeacoffee.cancel', $order) }}" method="POST"
                                      onsubmit="return confirm('Weet je zeker dat je dit lidmaatschap wilt beëindigen? De VIP status en voordelen van de speler worden direct ingetrokken.');">
                                    @csrf
                                    <button type="submit" title="Zet VIP stop / Opzeggen"
                                            class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-extrabold text-[11px] transition flex items-center gap-1 cursor-pointer">
                                        <i class="fa-solid fa-ban"></i> VIP Stopzetten
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-400 text-xs">
                            Geen Buy Me a Coffee registraties gevonden.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $orders->links() }}
        </div>
        @endif
    </div>

    <!-- MODAL: KOPPELEN AAN SPELER -->
    <div x-show="linkModalOpen" style="display: none;" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         @keydown.escape.window="linkModalOpen = false">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4"
             @click.away="linkModalOpen = false">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-link"></i>
                    </div>
                    <h3 class="text-base font-black text-slate-900">Buy Me a Coffee Koppelen</h3>
                </div>
                <button type="button" @click="linkModalOpen = false" class="text-slate-400 hover:text-slate-700 text-lg">&times;</button>
            </div>

            <template x-if="selectedOrder">
                <form :action="'/admin/buymeacoffee/' + selectedOrder.id + '/link'" method="POST" class="space-y-4 text-xs">
                    @csrf

                    <!-- Order samenvatting -->
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 font-medium">Supporter:</span>
                            <span class="font-extrabold text-slate-900" x-text="selectedOrder.supporter_name || 'Onbekend'"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 font-medium">E-mailadres:</span>
                            <span class="font-mono text-slate-700" x-text="selectedOrder.supporter_email || 'Geen e-mail'"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 font-medium">Bedrag:</span>
                            <span class="font-mono font-black text-emerald-600" x-text="selectedOrder.formatted_amount"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 font-medium">Gedetecteerd type:</span>
                            <span class="font-bold text-purple-700" x-text="selectedOrder.display_tier"></span>
                        </div>
                        <template x-if="selectedOrder.message">
                            <div class="pt-1 border-t border-slate-200 text-slate-600 italic">
                                "<span x-text="selectedOrder.message"></span>"
                            </div>
                        </template>
                    </div>

                    <!-- Kies Speler -->
                    <div class="space-y-1.5">
                        <label class="font-black text-slate-800 uppercase tracking-wider text-[11px]">Koppel aan Speler / Karakter</label>
                        <select name="character_id" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 focus:outline-hidden focus:border-amber-500">
                            <option value="">-- Kies een speler uit de lijst --</option>
                            @foreach($allCharacters as $char)
                            <option value="{{ $char->id }}">
                                {{ $char->name }} (ID {{ $char->id }} &bull; Lvl {{ $char->level }}{{ $char->is_vip ? ' &bull; Heeft al VIP' : '' }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Membership Tier Selectie -->
                    <div class="space-y-1.5">
                        <label class="font-black text-slate-800 uppercase tracking-wider text-[11px]">Membership Pakket / Tier</label>
                        <select name="membership_tier" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 focus:outline-hidden focus:border-amber-500">
                            <option value="TBS V.I.P. Lid">TBS V.I.P. Lid (€6/mnd &bull; Tier 1 &bull; +100 Sterren, +2 Maskers, +2.500/u)</option>
                            <option value="TBS Gangster Elite">TBS Gangster Elite (€14/mnd &bull; Tier 2 &bull; +250 Sterren, +5 Maskers, 1x XXL, +7.500/u)</option>
                            <option value="TBS Kliniek Baas">TBS Kliniek Baas (€24/mnd &bull; Tier 3 &bull; +600 Sterren, +12 Maskers, 3x XXL, +20.000/u)</option>
                            <option value="VIP LIFETIME">VIP LIFETIME (€125 &bull; Tier 4 &bull; +1500 Sterren, +30 Maskers, 10x XXL, +50.000/u)</option>
                            <option value="1x Bakkie Koffie (€3)">1x Bakkie Koffie (€3 &bull; +30 Sterren, +1 Masker, +50 Kogels)</option>
                            <option value="3x Bakkie Koffie (€9)">3x Bakkie Koffie (€9 &bull; +85 Sterren, +3 Maskers, 1x XXL, +200 Kogels)</option>
                            <option value="5x Bakkie Koffie (€15)">5x Bakkie Koffie (€15 &bull; +160 Sterren, +6 Maskers, 2x XXL, +500 Kogels)</option>
                            <option value="">Losse Donatie (Berekend op basis van bedrag)</option>
                        </select>
                    </div>

                    <!-- Checkbox voordelen direct activeren -->
                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" name="grant_rewards" value="1" id="grant_rewards" checked
                               class="rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                        <label for="grant_rewards" class="text-xs font-bold text-slate-700 cursor-pointer">
                            VIP status (31 dagen) en bijbehorende sterren/koffers direct toekennen
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="linkModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">
                            Annuleren
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-black shadow-xs transition cursor-pointer">
                            Opslaan &amp; Koppel aan Speler
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <!-- MODAL: HANDMATIGE DONATIE INVOEREN -->
    <div x-show="manualModalOpen" style="display: none;" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         @keydown.escape.window="manualModalOpen = false">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4"
             @click.away="manualModalOpen = false">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-plus"></i>
                    </div>
                    <h3 class="text-base font-black text-slate-900">Handmatige Donatie of Membership Registreren</h3>
                </div>
                <button type="button" @click="manualModalOpen = false" class="text-slate-400 hover:text-slate-700 text-lg">&times;</button>
            </div>

            <form action="{{ route('admin.buymeacoffee.manual') }}" method="POST" class="space-y-3.5 text-xs">
                @csrf

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="font-black text-slate-800 uppercase tracking-wider text-[11px]">Supporter Naam</label>
                        <input type="text" name="supporter_name" required placeholder="Bijv. Daniel"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 focus:outline-hidden focus:border-amber-500">
                    </div>
                    <div class="space-y-1">
                        <label class="font-black text-slate-800 uppercase tracking-wider text-[11px]">Supporter E-mail</label>
                        <input type="email" name="supporter_email" placeholder="optioneel@voorbeeld.nl"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 focus:outline-hidden focus:border-amber-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="font-black text-slate-800 uppercase tracking-wider text-[11px]">Bedrag (€)</label>
                        <input type="number" step="0.01" name="amount" required value="6.00"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 focus:outline-hidden focus:border-amber-500">
                    </div>
                    <div class="space-y-1">
                        <label class="font-black text-slate-800 uppercase tracking-wider text-[11px]">Type</label>
                        <select name="is_membership" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 focus:outline-hidden focus:border-amber-500">
                            <option value="1">Maandelijks VIP Membership</option>
                            <option value="0">Eenmalige Koffie Donatie</option>
                        </select>
                    </div>
                </div>

                    <div class="space-y-1">
                        <label class="font-black text-slate-800 uppercase tracking-wider text-[11px]">Pakket / Membership Tier</label>
                        <select name="membership_tier" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 focus:outline-hidden focus:border-amber-500">
                            <option value="TBS V.I.P. Lid">TBS V.I.P. Lid (€6/mnd &bull; Tier 1 &bull; +100 Sterren, +2 Maskers, +2.500/u)</option>
                            <option value="TBS Gangster Elite">TBS Gangster Elite (€14/mnd &bull; Tier 2 &bull; +250 Sterren, +5 Maskers, 1x XXL, +7.500/u)</option>
                            <option value="TBS Kliniek Baas">TBS Kliniek Baas (€24/mnd &bull; Tier 3 &bull; +600 Sterren, +12 Maskers, 3x XXL, +20.000/u)</option>
                            <option value="VIP LIFETIME">VIP LIFETIME (€125 &bull; Tier 4 &bull; +1500 Sterren, +30 Maskers, 10x XXL, +50.000/u)</option>
                            <option value="1x Bakkie Koffie (€3)">1x Bakkie Koffie (€3 &bull; +30 Sterren, +1 Masker, +50 Kogels)</option>
                            <option value="3x Bakkie Koffie (€9)">3x Bakkie Koffie (€9 &bull; +85 Sterren, +3 Maskers, 1x XXL, +200 Kogels)</option>
                            <option value="5x Bakkie Koffie (€15)">5x Bakkie Koffie (€15 &bull; +160 Sterren, +6 Maskers, 2x XXL, +500 Kogels)</option>
                        </select>
                    </div>

                <div class="space-y-1">
                    <label class="font-black text-slate-800 uppercase tracking-wider text-[11px]">Koppel direct aan Karakter</label>
                    <select name="character_id" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 focus:outline-hidden focus:border-amber-500">
                        <option value="">-- Kies speler --</option>
                        @foreach($allCharacters as $char)
                        <option value="{{ $char->id }}">
                            {{ $char->name }} (ID {{ $char->id }} &bull; Lvl {{ $char->level }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="font-black text-slate-800 uppercase tracking-wider text-[11px]">Optionele Notitie / Bericht</label>
                    <input type="text" name="message" placeholder="Bijv. Donatie via PayPal of bank"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 focus:outline-hidden focus:border-amber-500">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="manualModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">
                        Annuleren
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-black shadow-xs transition cursor-pointer">
                        Toevoegen &amp; Activeren
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
