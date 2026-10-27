@extends('admin.layout')

@section('admin-content')
<div class="space-y-6" x-data="{
    detailModalOpen: false,
    selectedLog: null,
    openDetail(log) {
        this.selectedLog = log;
        this.detailModalOpen = true;
    }
}">

    <!-- Top Summary & Action Header -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-sm shadow-2xs">
                    <i class="fa-solid fa-shield-virus"></i>
                </span>
                <h2 class="text-lg font-black text-slate-900 tracking-tight">Audit Log &amp; Automatische Misbruikdetectie</h2>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Real-time bewaking van alle stappen, functies, geldstromen en verdachte uitschieters in de kliniek.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <form action="{{ route('admin.audit_log.sync') }}" method="POST" class="inline">
                @csrf
                <button type="submit" 
                        onclick="return confirm('Wil je de complete database-historie (bank, diefstal, bendes, armdrukken en straffen) opnieuw analyseren op risico\'s?')"
                        class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-rotate"></i>
                    <span>Historie Synchroniseren &amp; Scannen</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Stats KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-list-check"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Totaal Gelogd</span>
                <span class="text-xl font-black text-slate-900">{{ number_format($totalCount, 0, ',', '.') }}</span>
                <span class="text-[10px] text-slate-500 block">{{ $uniquePlayersCount }} unieke spelers</span>
            </div>
        </div>

        <a href="{{ route('admin.audit_log.index', ['risk' => 'hoog']) }}" 
           class="bg-white border {{ $risk === 'hoog' ? 'border-rose-500 ring-2 ring-rose-200' : 'border-slate-200 hover:border-rose-300' }} rounded-xl p-4 shadow-xs flex items-center gap-3.5 transition">
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <div class="flex items-center gap-1.5">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Hoog Risico Alerts</span>
                    @if($highRiskCount > 0)
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                    @endif
                </div>
                <span class="text-xl font-black text-rose-600">{{ number_format($highRiskCount, 0, ',', '.') }}</span>
                <span class="text-[10px] text-slate-500 block">Fraude &amp; extreme waarden</span>
            </div>
        </a>

        <a href="{{ route('admin.audit_log.index', ['risk' => 'verdacht']) }}" 
           class="bg-white border {{ $risk === 'verdacht' ? 'border-amber-500 ring-2 ring-amber-200' : 'border-slate-200 hover:border-amber-300' }} rounded-xl p-4 shadow-xs flex items-center gap-3.5 transition">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-radiation"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Verdacht</span>
                <span class="text-xl font-black text-amber-600">{{ number_format($suspiciousCount, 0, ',', '.') }}</span>
                <span class="text-[10px] text-slate-500 block">Afwijkende activiteit</span>
            </div>
        </a>

        <a href="{{ route('admin.audit_log.index', ['risk' => 'normaal']) }}" 
           class="bg-white border {{ $risk === 'normaal' ? 'border-emerald-500 ring-2 ring-emerald-200' : 'border-slate-200 hover:border-emerald-300' }} rounded-xl p-4 shadow-xs flex items-center gap-3.5 transition">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-shield-check"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Regulier &amp; Veilig</span>
                <span class="text-xl font-black text-emerald-600">{{ number_format($normalCount, 0, ',', '.') }}</span>
                <span class="text-[10px] text-slate-500 block">Normale spelhandelingen</span>
            </div>
        </a>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs space-y-3">
        <form action="{{ route('admin.audit_log.index') }}" method="GET" class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <!-- Search field -->
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" 
                       name="search" 
                       value="{{ $search }}" 
                       placeholder="Zoek op spelernaam, IP-adres, omschrijving of risico-reden..." 
                       class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 text-xs text-slate-900 focus:outline-hidden focus:border-purple-500 focus:ring-1 focus:ring-purple-500 bg-slate-50">
            </div>

            <!-- Dropdown Filters -->
            <div class="flex items-center gap-2 flex-wrap text-xs">
                <!-- Risico Filter -->
                <select name="risk" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 font-bold focus:outline-hidden">
                    <option value="all" {{ $risk === 'all' ? 'selected' : '' }}>Alle Risiconiveaus</option>
                    <option value="hoog" {{ $risk === 'hoog' ? 'selected' : '' }}>🔴 Alleen Hoog Risico</option>
                    <option value="verdacht" {{ $risk === 'verdacht' ? 'selected' : '' }}>🟡 Alleen Verdacht</option>
                    <option value="normaal" {{ $risk === 'normaal' ? 'selected' : '' }}>🟢 Alleen Normaal</option>
                </select>

                <!-- Categorie Filter -->
                <select name="category" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 font-bold focus:outline-hidden">
                    <option value="all" {{ $category === 'all' ? 'selected' : '' }}>Alle Categorieën</option>
                    <option value="economy" {{ $category === 'economy' ? 'selected' : '' }}>🏦 Economie &amp; Bank</option>
                    <option value="crime" {{ $category === 'crime' ? 'selected' : '' }}>🥷 Misdaad &amp; Diefstal</option>
                    <option value="shop" {{ $category === 'shop' ? 'selected' : '' }}>🛒 Kantine &amp; Shop</option>
                    <option value="gang" {{ $category === 'gang' ? 'selected' : '' }}>👥 Bendes &amp; Donaties</option>
                    <option value="combat" {{ $category === 'combat' ? 'selected' : '' }}>🥊 Gevechten &amp; Armdrukken</option>
                    <option value="moderation" {{ $category === 'moderation' ? 'selected' : '' }}>⚖️ Moderatie &amp; Sancties</option>
                </select>

                <!-- Datum filter -->
                <select name="date" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 font-bold focus:outline-hidden">
                    <option value="all" {{ $dateRange === 'all' ? 'selected' : '' }}>Alle Data</option>
                    <option value="today" {{ $dateRange === 'today' ? 'selected' : '' }}>Vandaag</option>
                    <option value="week" {{ $dateRange === 'week' ? 'selected' : '' }}>Afgelopen 7 Dagen</option>
                    <option value="month" {{ $dateRange === 'month' ? 'selected' : '' }}>Afgelopen 30 Dagen</option>
                </select>

                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold transition">
                    Filteren
                </button>

                @if($risk !== 'all' || $category !== 'all' || $search || $dateRange !== 'all')
                <a href="{{ route('admin.audit_log.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold transition">
                    Reset
                </a>
                @endif
            </div>
        </form>

        <!-- Quick Filter Badges -->
        <div class="flex items-center gap-2 pt-2 border-t border-slate-100 flex-wrap text-xs">
            <span class="text-slate-400 font-bold">Snel filter:</span>
            <a href="{{ route('admin.audit_log.index', ['risk' => 'all']) }}" 
               class="px-2.5 py-1 rounded-lg font-bold transition {{ $risk === 'all' && !$search ? 'bg-purple-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Alles ({{ $totalCount }})
            </a>
            <a href="{{ route('admin.audit_log.index', ['risk' => 'hoog']) }}" 
               class="px-2.5 py-1 rounded-lg font-bold transition {{ $risk === 'hoog' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">
                Hoog Risico ({{ $highRiskCount }})
            </a>
            <a href="{{ route('admin.audit_log.index', ['risk' => 'verdacht']) }}" 
               class="px-2.5 py-1 rounded-lg font-bold transition {{ $risk === 'verdacht' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-800 hover:bg-amber-100' }}">
                Verdacht ({{ $suspiciousCount }})
            </a>
            <a href="{{ route('admin.audit_log.index', ['category' => 'economy']) }}" 
               class="px-2.5 py-1 rounded-lg font-bold transition {{ $category === 'economy' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                Bank &amp; Geldstromen
            </a>
            <a href="{{ route('admin.audit_log.index', ['category' => 'crime']) }}" 
               class="px-2.5 py-1 rounded-lg font-bold transition {{ $category === 'crime' ? 'bg-indigo-600 text-white' : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100' }}">
                Zakkenrollen
            </a>
            <a href="{{ route('admin.audit_log.index', ['category' => 'combat']) }}" 
               class="px-2.5 py-1 rounded-lg font-bold transition {{ $category === 'combat' ? 'bg-cyan-600 text-white' : 'bg-cyan-50 text-cyan-800 hover:bg-cyan-100' }}">
                Armdrukken
            </a>
        </div>
    </div>

    <!-- Table of Audit Events -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-extrabold uppercase tracking-wider">
                        <th class="py-3 px-4">Tijdstip</th>
                        <th class="py-3 px-4">Speler</th>
                        <th class="py-3 px-4">Risiconiveau</th>
                        <th class="py-3 px-4">Categorie &amp; Actie</th>
                        <th class="py-3 px-4">Omschrijving &amp; Bedrag</th>
                        <th class="py-3 px-4">Gedetecteerde Risico-Reden</th>
                        <th class="py-3 px-4 text-right">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/70 transition {{ $log->risk_level === 'hoog' ? 'bg-rose-50/30' : ($log->risk_level === 'verdacht' ? 'bg-amber-50/20' : '') }}">
                        <!-- Timestamp -->
                        <td class="py-3.5 px-4 whitespace-nowrap text-slate-500">
                            <span class="font-bold text-slate-900 block font-mono">{{ $log->created_at->format('d-m-Y H:i:s') }}</span>
                            <span class="text-[10px] text-slate-400 block">{{ $log->created_at->diffForHumans() }}</span>
                        </td>

                        <!-- Player -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            @if($log->character)
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-purple-100 text-purple-700 font-black flex items-center justify-center text-xs">
                                    {{ substr($log->character->name, 0, 1) }}
                                </div>
                                <div>
                                    <span class="font-extrabold text-slate-900 block">{{ $log->character->name }}</span>
                                    <span class="text-[10px] text-slate-400 block font-mono">Lvl {{ $log->character->level }} &bull; #{{ $log->character->id }}</span>
                                </div>
                            </div>
                            @elseif($log->user)
                            <div>
                                <span class="font-bold text-slate-800 block">{{ $log->user->name }}</span>
                                <span class="text-[10px] text-slate-400 block font-mono">{{ $log->user->email }}</span>
                            </div>
                            @else
                            <span class="text-slate-400 italic">Systeem</span>
                            @endif
                        </td>

                        <!-- Risk Level -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <div class="flex items-center gap-1.5">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-black inline-flex items-center gap-1.5 {{ $log->getRiskBadgeClass() }}">
                                    <i class="{{ $log->getRiskIcon() }} text-[10px]"></i>
                                    <span>{{ ucfirst($log->risk_level) }}</span>
                                    <span class="opacity-75 font-mono">({{ $log->risk_score }}/100)</span>
                                </span>
                            </div>
                        </td>

                        <!-- Category & Action -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <div class="flex items-center gap-1.5">
                                <i class="{{ $log->getCategoryIcon() }} text-xs"></i>
                                <span class="font-bold text-slate-800">{{ ucfirst($log->category) }}</span>
                            </div>
                            <span class="text-[10px] font-mono text-slate-400 block">{{ $log->action }}</span>
                        </td>

                        <!-- Description & Amount -->
                        <td class="py-3.5 px-4 max-w-md">
                            <p class="font-medium text-slate-800 truncate" title="{{ $log->description }}">
                                {{ $log->description }}
                            </p>
                            @if($log->amount)
                            <span class="font-mono font-extrabold text-[11px] {{ $log->risk_level === 'hoog' ? 'text-rose-600' : ($log->risk_level === 'verdacht' ? 'text-amber-600' : 'text-emerald-600') }}">
                                €{{ number_format($log->amount, 0, ',', '.') }}
                            </span>
                            @endif
                        </td>

                        <!-- Risk Reason -->
                        <td class="py-3.5 px-4">
                            @if($log->risk_reason && $log->risk_level !== 'normaal')
                            <div class="px-2 py-1 rounded-lg text-[11px] font-semibold flex items-start gap-1.5 {{ $log->risk_level === 'hoog' ? 'bg-rose-100/70 text-rose-800' : 'bg-amber-100/70 text-amber-900' }}">
                                <i class="fa-solid fa-circle-exclamation mt-0.5 shrink-0 text-[10px]"></i>
                                <span>{{ $log->risk_reason }}</span>
                            </div>
                            @else
                            <span class="text-slate-400 text-[11px]">Geen risico indicatoren</span>
                            @endif
                        </td>

                        <!-- Action / Details Button -->
                        <td class="py-3.5 px-4 whitespace-nowrap text-right">
                            <button type="button" 
                                    @click="openDetail({{ json_encode($log) }})"
                                    class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-purple-100 text-slate-700 hover:text-purple-700 font-bold transition inline-flex items-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-code text-[10px]"></i>
                                <span>Inspecteer</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center text-lg mb-2">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </div>
                            <span class="font-bold text-sm block">Geen audit logs gevonden</span>
                            <span class="text-xs block mt-1">Probeer een ander risiconiveau of klik op 'Historie Synchroniseren'.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($logs->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $logs->links() }}
        </div>
        @endif
    </div>

    <!-- Inspector JSON Modal -->
    <div x-show="detailModalOpen" 
         x-transition.opacity
         class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" 
         style="display: none;">
        <div @click.away="detailModalOpen = false" 
             class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl space-y-4 max-h-[85vh] flex flex-col">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-code"></i>
                    </span>
                    <h3 class="font-black text-slate-900 text-sm">Audit Log Inspectie &bull; ID #<span x-text="selectedLog?.id"></span></h3>
                </div>
                <button type="button" @click="detailModalOpen = false" class="text-slate-400 hover:text-slate-600 text-sm cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="space-y-3 overflow-y-auto pr-1 flex-1 text-xs">
                <div>
                    <span class="text-slate-400 font-bold block uppercase text-[10px]">Omschrijving:</span>
                    <p class="font-bold text-slate-800 text-sm" x-text="selectedLog?.description"></p>
                </div>

                <div class="grid grid-cols-2 gap-3 bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <div>
                        <span class="text-slate-400 font-bold block text-[10px]">Actie:</span>
                        <span class="font-mono font-bold text-slate-800" x-text="selectedLog?.action"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-bold block text-[10px]">Categorie:</span>
                        <span class="font-mono font-bold text-slate-800" x-text="selectedLog?.category"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-bold block text-[10px]">Risicoscore:</span>
                        <span class="font-mono font-extrabold" :class="selectedLog?.risk_level === 'hoog' ? 'text-rose-600' : 'text-amber-600'" x-text="selectedLog?.risk_score + '/100 (' + selectedLog?.risk_level + ')'"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-bold block text-[10px]">IP-adres:</span>
                        <span class="font-mono font-bold text-slate-800" x-text="selectedLog?.ip_address || 'N/A'"></span>
                    </div>
                </div>

                <div>
                    <span class="text-slate-400 font-bold block uppercase text-[10px] mb-1">Gedetecteerde Risico Redenen:</span>
                    <div class="p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 font-semibold" x-text="selectedLog?.risk_reason || 'Geen automatische risicoflags'"></div>
                </div>

                <div>
                    <span class="text-slate-400 font-bold block uppercase text-[10px] mb-1">Ruwe Payload / Metadata (JSON):</span>
                    <pre class="bg-slate-900 text-slate-100 p-3 rounded-xl font-mono text-[11px] overflow-x-auto max-h-48" x-text="JSON.stringify(selectedLog?.details, null, 2)"></pre>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end">
                <button type="button" @click="detailModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs cursor-pointer">
                    Sluiten
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
