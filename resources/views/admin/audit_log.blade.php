@extends('admin.layout')

@section('admin-content')
@php
    $currentSort = $sort ?? request('sort', 'newest');
    $currentIp = $ip ?? request('ip');
    $currentCharacterId = $characterId ?? request('character_id');
    $currentPerPage = $perPage ?? (int) request('per_page', 30);
    $sharedIpsList = $sharedIps ?? collect();
    $sharedIpCharsMap = $sharedIpCharacters ?? collect();
    $watchlist = $topRiskPlayers ?? collect();
    $metrics24h = $stats24h ?? ['total' => 0, 'high_risk' => 0, 'suspicious' => 0];
@endphp
<div class="space-y-6" x-data="{
    detailModalOpen: false,
    selectedLog: null,
    copied: false,
    openDetail(log) {
        this.selectedLog = log;
        this.copied = false;
        this.detailModalOpen = true;
    },
    copyPayload() {
        if (!this.selectedLog) return;
        const text = JSON.stringify(this.selectedLog.details ?? {}, null, 2);
        navigator.clipboard?.writeText(text);
        this.copied = true;
        setTimeout(() => { this.copied = false; }, 2000);
    }
}">

    <!-- Top Summary & Action Header -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/70 flex items-center justify-center text-sm shadow-2xs">
                    <i class="fa-solid fa-shield-virus"></i>
                </span>
                <h2 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">Audit Log &amp; Automatische Misbruikdetectie</h2>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Real-time bewaking van alle stappen, functies, geldstromen, multi-account IP-clusters en verdachte uitschieters in de kliniek.
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <!-- CSV Export Button -->
            <a href="{{ route('admin.audit_log.export', request()->query()) }}" 
               class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-black text-xs shadow-xs transition-all duration-150 active:scale-95 flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-file-csv text-emerald-600 dark:text-emerald-400"></i>
                <span>Exporteer CSV</span>
            </a>

            <!-- Sync Form with Custom Limit -->
            <form action="{{ route('admin.audit_log.sync') }}" method="POST" class="flex items-center gap-2">
                @csrf
                <select name="limit" aria-label="Synchronisatie limiet" class="px-2.5 py-2 text-xs font-bold rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 text-slate-700 dark:text-slate-200 focus:outline-none focus:border-purple-500 transition">
                    <option value="500">500 rijen</option>
                    <option value="1000">1.000 rijen</option>
                    <option value="2000" selected>2.000 rijen</option>
                    <option value="5000">5.000 rijen</option>
                </select>
                <button type="submit" 
                        data-confirm="Wil je de complete database-historie (bank, diefstal, bendes, armdrukken en straffen) opnieuw analyseren op risico's?"
                        data-confirm-title="Historie Synchroniseren &amp; Scannen"
                        data-confirm-btn="Ja, Synchroniseren"
                        data-confirm-type="warning"
                        data-confirm-icon="fa-rotate"
                        class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs shadow-xs transition-all duration-150 active:scale-95 flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-rotate"></i>
                    <span>Historie Synchroniseren &amp; Scannen</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Stats KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-list-check"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Totaal Gelogd</span>
                <span class="text-xl font-black text-slate-900 dark:text-white">{{ number_format($totalCount, 0, ',', '.') }}</span>
                <span class="text-[10px] text-slate-500 dark:text-slate-400 block">{{ $uniquePlayersCount }} unieke spelers &bull; {{ number_format($metrics24h['total'], 0, ',', '.') }} (24u)</span>
            </div>
        </div>

        <a href="{{ route('admin.audit_log.index', ['risk' => 'hoog']) }}" 
           class="bg-white dark:bg-slate-900 border {{ $risk === 'hoog' ? 'border-rose-500 ring-2 ring-rose-200 dark:ring-rose-900/50' : 'border-slate-200/90 dark:border-slate-800 hover:border-rose-300 dark:hover:border-rose-700/80' }} rounded-2xl p-4 shadow-xs flex items-center gap-3.5 transition">
            <div class="w-11 h-11 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/70 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <div class="flex items-center gap-1.5">
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Hoog Risico Alerts</span>
                    @if($highRiskCount > 0)
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                    @endif
                </div>
                <span class="text-xl font-black text-rose-600 dark:text-rose-400">{{ number_format($highRiskCount, 0, ',', '.') }}</span>
                <span class="text-[10px] text-slate-500 dark:text-slate-400 block">Fraude &amp; extreme waarden ({{ $metrics24h['high_risk'] }} in 24u)</span>
            </div>
        </a>

        <a href="{{ route('admin.audit_log.index', ['risk' => 'verdacht']) }}" 
           class="bg-white dark:bg-slate-900 border {{ $risk === 'verdacht' ? 'border-amber-500 ring-2 ring-amber-200 dark:ring-amber-900/50' : 'border-slate-200/90 dark:border-slate-800 hover:border-amber-300 dark:hover:border-amber-700/80' }} rounded-2xl p-4 shadow-xs flex items-center gap-3.5 transition">
            <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200/80 dark:border-amber-800/70 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-radiation"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Verdacht</span>
                <span class="text-xl font-black text-amber-600 dark:text-amber-400">{{ number_format($suspiciousCount, 0, ',', '.') }}</span>
                <span class="text-[10px] text-slate-500 dark:text-slate-400 block">Afwijkende activiteit ({{ $metrics24h['suspicious'] }} in 24u)</span>
            </div>
        </a>

        <a href="{{ route('admin.audit_log.index', ['risk' => 'normaal']) }}" 
           class="bg-white dark:bg-slate-900 border {{ $risk === 'normaal' ? 'border-emerald-500 ring-2 ring-emerald-200 dark:ring-emerald-900/50' : 'border-slate-200/90 dark:border-slate-800 hover:border-emerald-300 dark:hover:border-emerald-700/80' }} rounded-2xl p-4 shadow-xs flex items-center gap-3.5 transition">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/70 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-shield-check"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Regulier &amp; Veilig</span>
                <span class="text-xl font-black text-emerald-600 dark:text-emerald-400">{{ number_format($normalCount, 0, ',', '.') }}</span>
                <span class="text-[10px] text-slate-500 dark:text-slate-400 block">Normale spelhandelingen</span>
            </div>
        </a>
    </div>

    <!-- Forensics & Misbruik Radar -->
    <div x-data="{ radarOpen: true }" class="space-y-3">
        <div class="flex items-center justify-between">
            <button type="button" 
                    @click="radarOpen = !radarOpen" 
                    class="flex items-center gap-2 text-xs font-black text-slate-800 dark:text-slate-200 cursor-pointer">
                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                <span>Forensics &amp; Misbruik Radar</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200" :class="radarOpen ? 'rotate-180' : ''"></i>
            </button>
            <span class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider">
                Heuristische Multi-Account &amp; Risico Analyse
            </span>
        </div>

        <div x-show="radarOpen" x-transition.opacity class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Card 1: Multi-Account & Shared IP Detection -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2.5">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-800/70 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-network-wired"></i>
                        </div>
                        <div>
                            <h3 class="text-xs font-black text-slate-900 dark:text-white">Gedeelde IP &amp; Multi-Account Detectie</h3>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500">IP-adressen actief op meerdere accounts of spelers</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                    @forelse($sharedIpsList as $cluster)
                    @php
                        $linkedCharNames = $sharedIpCharsMap->get($cluster->ip_address, collect())
                            ->map(fn ($item) => $item->character?->name)
                            ->filter()
                            ->unique()
                            ->values();
                        $accountTotal = max((int) ($cluster->character_count ?? 0), (int) ($cluster->user_count ?? 0));
                    @endphp
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex items-center justify-between gap-2">
                        <div class="space-y-0.5 min-w-0">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="font-mono font-bold text-xs text-slate-900 dark:text-white">{{ $cluster->ip_address }}</span>
                                <span class="px-1.5 py-0.5 rounded-md bg-indigo-100 dark:bg-indigo-950/70 text-indigo-700 dark:text-indigo-300 font-black text-[10px]">
                                    {{ $accountTotal }} accounts ({{ $cluster->total_logs ?? 0 }} logs)
                                </span>
                                @if(($cluster->high_risk_count ?? 0) > 0)
                                <span class="px-1.5 py-0.5 rounded-md bg-rose-100 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300 font-black text-[10px]">
                                    {{ $cluster->high_risk_count }} hoog risico
                                </span>
                                @endif
                            </div>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400 truncate">
                                {{ $linkedCharNames->isNotEmpty() ? $linkedCharNames->implode(', ') : 'Meerdere gekoppelde gebruikersaccounts' }}
                            </p>
                        </div>
                        <a href="{{ route('admin.audit_log.index', array_merge(request()->except('page'), ['ip' => $cluster->ip_address])) }}" 
                           class="px-2.5 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[10px] shadow-2xs transition active:scale-95 shrink-0">
                            Filter IP
                        </a>
                    </div>
                    @empty
                    <div class="py-6 text-center text-slate-400 dark:text-slate-500 space-y-1">
                        <i class="fa-solid fa-shield-check text-base text-emerald-500"></i>
                        <p class="text-xs font-bold">Geen verdachte gedeelde IP clusters</p>
                        <p class="text-[10px]">Geen multi-account activiteit gedetecteerd in actieve logs.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Card 2: Top Risk Patients (Watchlist) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2.5">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/70 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <h3 class="text-xs font-black text-slate-900 dark:text-white">Top Risico Patiënten (Watchlist)</h3>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500">Spelers met de meeste hoge of verdachte risicomeldingen</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                    @forelse($watchlist as $patient)
                    @php
                        $patientName = $patient->character?->name ?? 'Patiënt #'.$patient->character_id;
                    @endphp
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-7 h-7 rounded-lg bg-rose-100 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300 font-black flex items-center justify-center text-xs border border-rose-200/80 dark:border-rose-800/70 shrink-0">
                                {{ substr($patientName, 0, 1) }}
                            </div>
                            <div class="truncate">
                                <div class="flex items-center gap-1.5">
                                    @if($patient->character)
                                    <a href="{{ route('profile.show', $patient->character->name) }}" 
                                       class="font-black text-xs text-slate-900 dark:text-white hover:text-purple-600 dark:hover:text-purple-400 truncate">
                                        {{ $patientName }}
                                    </a>
                                    @else
                                    <span class="font-black text-xs text-slate-900 dark:text-white truncate">{{ $patientName }}</span>
                                    @endif
                                    <span class="px-1.5 py-0.5 rounded bg-rose-100 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 font-mono font-bold text-[9px]">
                                        {{ $patient->incident_count ?? 1 }}x alert
                                    </span>
                                </div>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono block">
                                    Max Score: {{ $patient->max_risk_score ?? 100 }}/100 &bull; Cumulatief: {{ $patient->total_risk_score ?? 0 }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0">
                            <a href="{{ route('admin.audit_log.index', array_merge(request()->except('page'), ['character_id' => $patient->character_id])) }}" 
                               class="px-2 py-1 rounded-lg bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold text-[10px] transition" 
                               title="Filter audit logs op deze speler">
                                <i class="fa-solid fa-filter"></i>
                            </a>
                            <a href="{{ route('admin.moderation.index', ['search' => $patientName]) }}" 
                               class="px-2 py-1 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold text-[10px] transition" 
                               title="Naar Dossier &amp; Moderatie">
                                <i class="fa-solid fa-gavel"></i>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="py-6 text-center text-slate-400 dark:text-slate-500 space-y-1">
                        <i class="fa-solid fa-circle-check text-base text-emerald-500"></i>
                        <p class="text-xs font-bold">Geen patiënten op de watchlist</p>
                        <p class="text-[10px]">Geen herhaalde hoog-risico acties geregistreerd.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs space-y-3">
        <form action="{{ route('admin.audit_log.index') }}" method="GET" class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            @if($currentIp)
                <input type="hidden" name="ip" value="{{ $currentIp }}">
            @endif
            @if($currentCharacterId)
                <input type="hidden" name="character_id" value="{{ $currentCharacterId }}">
            @endif

            <!-- Search field -->
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-xs"></i>
                <input type="text" 
                       name="search" 
                       value="{{ $search }}" 
                       placeholder="Zoek op spelernaam, IP-adres, omschrijving of risico-reden..." 
                       class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-base sm:text-xs text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 bg-slate-50 dark:bg-slate-800/90 transition">
            </div>

            <!-- Dropdown Filters -->
            <div class="flex items-center gap-2 flex-wrap text-base sm:text-xs">
                <!-- Sortering Filter -->
                <select name="sort" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/90 text-slate-700 dark:text-slate-200 font-bold focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 transition">
                    <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>⏱️ Nieuwste Eerst</option>
                    <option value="highest_risk" {{ $currentSort === 'highest_risk' ? 'selected' : '' }}>⚠️ Hoogste Risicoscore</option>
                    <option value="highest_amount" {{ $currentSort === 'highest_amount' ? 'selected' : '' }}>💰 Hoogste Bedrag</option>
                    <option value="oldest" {{ $currentSort === 'oldest' ? 'selected' : '' }}>🕰️ Oudste Eerst</option>
                </select>

                <!-- Risico Filter -->
                <select name="risk" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/90 text-slate-700 dark:text-slate-200 font-bold focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 transition">
                    <option value="all" {{ $risk === 'all' ? 'selected' : '' }}>Alle Risiconiveaus</option>
                    <option value="hoog" {{ $risk === 'hoog' ? 'selected' : '' }}>🔴 Alleen Hoog Risico</option>
                    <option value="verdacht" {{ $risk === 'verdacht' ? 'selected' : '' }}>🟡 Alleen Verdacht</option>
                    <option value="normaal" {{ $risk === 'normaal' ? 'selected' : '' }}>🟢 Alleen Normaal</option>
                </select>

                <!-- Categorie Filter -->
                <select name="category" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/90 text-slate-700 dark:text-slate-200 font-bold focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 transition">
                    <option value="all" {{ $category === 'all' ? 'selected' : '' }}>Alle Categorieën</option>
                    <option value="economy" {{ $category === 'economy' ? 'selected' : '' }}>🏦 Economie &amp; Bank</option>
                    <option value="crime" {{ $category === 'crime' ? 'selected' : '' }}>🥷 Misdaad &amp; Diefstal</option>
                    <option value="shop" {{ $category === 'shop' ? 'selected' : '' }}>🛒 Kantine &amp; Shop</option>
                    <option value="gang" {{ $category === 'gang' ? 'selected' : '' }}>👥 Bendes &amp; Donaties</option>
                    <option value="combat" {{ $category === 'combat' ? 'selected' : '' }}>🥊 Gevechten &amp; Armdrukken</option>
                    <option value="training" {{ $category === 'training' ? 'selected' : '' }}>🏋️ Fitness &amp; Training</option>
                    <option value="moderation" {{ $category === 'moderation' ? 'selected' : '' }}>⚖️ Moderatie &amp; Sancties</option>
                    <option value="security" {{ $category === 'security' ? 'selected' : '' }}>🛡️ Beveiliging &amp; Fraude</option>
                    <option value="admin" {{ $category === 'admin' ? 'selected' : '' }}>👑 Beheer &amp; Staff</option>
                    <option value="casino" {{ $category === 'casino' ? 'selected' : '' }}>🎲 Casino &amp; Kansspel</option>
                    <option value="marketplace" {{ $category === 'marketplace' ? 'selected' : '' }}>⚖️ Marktplaats &amp; Handel</option>
                </select>

                <!-- Datum filter -->
                <select name="date" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/90 text-slate-700 dark:text-slate-200 font-bold focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 transition">
                    <option value="all" {{ $dateRange === 'all' ? 'selected' : '' }}>Alle Data</option>
                    <option value="24h" {{ $dateRange === '24h' ? 'selected' : '' }}>Afgelopen 24 Uur</option>
                    <option value="today" {{ $dateRange === 'today' ? 'selected' : '' }}>Vandaag</option>
                    <option value="week" {{ $dateRange === 'week' ? 'selected' : '' }}>Afgelopen 7 Dagen</option>
                    <option value="month" {{ $dateRange === 'month' ? 'selected' : '' }}>Afgelopen 30 Dagen</option>
                </select>

                <button type="submit" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black shadow-xs transition-all duration-150 active:scale-95 cursor-pointer">
                    Filteren
                </button>

                @if($risk !== 'all' || $category !== 'all' || $search || $dateRange !== 'all' || $currentIp || $currentCharacterId || $currentSort !== 'newest')
                <a href="{{ route('admin.audit_log.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700 font-bold transition-all duration-150 active:scale-95 cursor-pointer">
                    Reset
                </a>
                @endif
            </div>
        </form>

        <!-- Active Filter Pills -->
        @if($currentIp || $currentCharacterId || $search)
        <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800 flex-wrap text-xs">
            <span class="text-slate-400 dark:text-slate-500 font-bold">Actieve focus:</span>
            @if($currentIp)
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/70 font-mono text-[11px] font-bold">
                <i class="fa-solid fa-network-wired text-[9px]"></i>
                IP: {{ $currentIp }}
                <a href="{{ route('admin.audit_log.index', request()->except('ip', 'page')) }}" class="text-indigo-400 hover:text-indigo-600 dark:hover:text-white ml-1">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            </span>
            @endif

            @if($currentCharacterId)
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70 text-[11px] font-bold">
                <i class="fa-solid fa-user text-[9px]"></i>
                Speler: {{ isset($selectedCharacter) && $selectedCharacter ? $selectedCharacter->name.' (#'.$selectedCharacter->id.')' : '#'.$currentCharacterId }}
                <a href="{{ route('admin.audit_log.index', request()->except('character_id', 'page')) }}" class="text-purple-400 hover:text-purple-600 dark:hover:text-white ml-1">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            </span>
            @endif

            @if($search)
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 text-[11px] font-bold">
                Zoekterm: "{{ $search }}"
                <a href="{{ route('admin.audit_log.index', request()->except('search', 'page')) }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-white ml-1">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            </span>
            @endif
        </div>
        @endif

        <!-- Quick Filter Badges -->
        <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800 flex-wrap text-xs">
            <span class="text-slate-400 dark:text-slate-500 font-bold">Snel filter:</span>
            <a href="{{ route('admin.audit_log.index', ['risk' => 'all']) }}" 
               class="px-2.5 py-1 rounded-lg font-bold transition {{ $risk === 'all' && !$search && $category === 'all' ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700' }}">
                Alles ({{ $totalCount }})
            </a>
            <a href="{{ route('admin.audit_log.index', ['risk' => 'hoog']) }}" 
               class="px-2.5 py-1 rounded-lg font-bold transition {{ $risk === 'hoog' ? 'bg-rose-600 text-white shadow-xs' : 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-900/40 border border-rose-200/80 dark:border-rose-800/70' }}">
                Hoog Risico ({{ $highRiskCount }})
            </a>
            <a href="{{ route('admin.audit_log.index', ['risk' => 'verdacht']) }}" 
               class="px-2.5 py-1 rounded-lg font-bold transition {{ $risk === 'verdacht' ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/40 border border-amber-200/80 dark:border-amber-800/70' }}">
                Verdacht ({{ $suspiciousCount }})
            </a>
            <a href="{{ route('admin.audit_log.index', ['category' => 'economy']) }}" 
               class="px-2.5 py-1 rounded-lg font-bold transition {{ $category === 'economy' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 border border-emerald-200/80 dark:border-emerald-800/70' }}">
                Bank &amp; Geldstromen
            </a>
            <a href="{{ route('admin.audit_log.index', ['category' => 'crime']) }}" 
               class="px-2.5 py-1 rounded-lg font-bold transition {{ $category === 'crime' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 border border-indigo-200/80 dark:border-indigo-800/70' }}">
                Zakkenrollen
            </a>
            <a href="{{ route('admin.audit_log.index', ['category' => 'combat']) }}" 
               class="px-2.5 py-1 rounded-lg font-bold transition {{ $category === 'combat' ? 'bg-cyan-600 text-white shadow-xs' : 'bg-cyan-50 dark:bg-cyan-950/60 text-cyan-800 dark:text-cyan-300 hover:bg-cyan-100 dark:hover:bg-cyan-900/40 border border-cyan-200/80 dark:border-cyan-800/70' }}">
                Armdrukken
            </a>
            <a href="{{ route('admin.audit_log.index', ['category' => 'moderation']) }}" 
               class="px-2.5 py-1 rounded-lg font-bold transition {{ $category === 'moderation' ? 'bg-rose-600 text-white shadow-xs' : 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-900/40 border border-rose-200/80 dark:border-rose-800/70' }}">
                Moderatie &amp; Sancties
            </a>
        </div>
    </div>

    <!-- Table of Audit Events -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/90 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-extrabold uppercase tracking-wider text-[10px]">
                        <th class="py-3 px-4">Tijdstip</th>
                        <th class="py-3 px-4">Speler &amp; IP</th>
                        <th class="py-3 px-4">Risiconiveau</th>
                        <th class="py-3 px-4">Categorie &amp; Actie</th>
                        <th class="py-3 px-4">Omschrijving &amp; Bedrag</th>
                        <th class="py-3 px-4">Gedetecteerde Risico-Reden</th>
                        <th class="py-3 px-4 text-right">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors {{ $log->risk_level === 'hoog' ? 'bg-rose-50/30 dark:bg-rose-950/15' : ($log->risk_level === 'verdacht' ? 'bg-amber-50/20 dark:bg-amber-950/15' : '') }}">
                        <!-- Timestamp -->
                        <td class="py-3.5 px-4 whitespace-nowrap text-slate-500 dark:text-slate-400">
                            <span class="font-bold text-slate-900 dark:text-white block font-mono">{{ $log->created_at->format('d-m-Y H:i:s') }}</span>
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 block">{{ $log->created_at->diffForHumans() }}</span>
                        </td>

                        <!-- Player & IP -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            @if($log->character)
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 font-black flex items-center justify-center text-xs border border-purple-200/80 dark:border-purple-800/70 shrink-0">
                                    {{ substr($log->character->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <a href="{{ route('profile.show', $log->character->name) }}" 
                                           class="font-extrabold text-slate-900 dark:text-white hover:text-purple-600 dark:hover:text-purple-400">
                                            {{ $log->character->name }}
                                        </a>
                                        <a href="{{ route('admin.audit_log.index', array_merge(request()->except('page'), ['character_id' => $log->character_id])) }}" 
                                           title="Filter op deze speler" 
                                           class="text-slate-400 hover:text-purple-600 dark:hover:text-purple-400 text-[10px]">
                                            <i class="fa-solid fa-filter"></i>
                                        </a>
                                    </div>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 block font-mono">Lvl {{ $log->character->level }} &bull; #{{ $log->character->id }}</span>
                                </div>
                            </div>
                            @elseif($log->user)
                            <div>
                                <span class="font-bold text-slate-800 dark:text-slate-200 block">{{ $log->user->name }}</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 block font-mono">{{ $log->user->email }}</span>
                            </div>
                            @else
                            <span class="text-slate-400 dark:text-slate-500 italic">Systeem</span>
                            @endif

                            @if($log->ip_address)
                            <div class="mt-1 flex items-center gap-1 font-mono text-[10px]">
                                <i class="fa-solid fa-globe text-[9px] text-slate-400"></i>
                                <a href="{{ route('admin.audit_log.index', array_merge(request()->except('page'), ['ip' => $log->ip_address])) }}" 
                                   class="text-indigo-600 dark:text-indigo-400 hover:underline font-bold" 
                                   title="Filter alle activiteit op dit IP">
                                    {{ $log->ip_address }}
                                </a>
                            </div>
                            @endif
                        </td>

                        <!-- Risk Level -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <div class="space-y-1.5 w-36">
                                <div class="flex items-center gap-1.5">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-black inline-flex items-center gap-1.5 {{ $log->getRiskBadgeClass() }}">
                                        <i class="{{ $log->getRiskIcon() }} text-[10px]"></i>
                                        <span>{{ ucfirst($log->risk_level) }}</span>
                                        <span class="opacity-75 font-mono">({{ $log->risk_score }}/100)</span>
                                    </span>
                                </div>
                                <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-1.5 rounded-full {{ $log->risk_level === 'hoog' ? 'bg-rose-500' : ($log->risk_level === 'verdacht' ? 'bg-amber-500' : 'bg-emerald-500') }}" 
                                         style="width: {{ min(100, max(5, (int) $log->risk_score)) }}%;">
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Category & Action -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <div class="flex items-center gap-1.5">
                                <i class="{{ $log->getCategoryIcon() }} text-xs"></i>
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $log->getCategoryLabel() }}</span>
                            </div>
                            <span class="text-[10px] font-mono text-slate-400 dark:text-slate-500 block">{{ $log->action }}</span>
                        </td>

                        <!-- Description & Amount -->
                        <td class="py-3.5 px-4 max-w-md">
                            <p class="font-medium text-slate-800 dark:text-slate-200 truncate" title="{{ $log->description }}">
                                {{ $log->description }}
                            </p>
                            @if($log->amount)
                            <span class="font-mono font-extrabold text-[11px] {{ $log->risk_level === 'hoog' ? 'text-rose-600 dark:text-rose-400' : ($log->risk_level === 'verdacht' ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400') }}">
                                €{{ number_format($log->amount, 0, ',', '.') }}
                            </span>
                            @endif
                        </td>

                        <!-- Risk Reason -->
                        <td class="py-3.5 px-4 max-w-xs">
                            @if(!empty($log->risk_flags) && is_array($log->risk_flags) && $log->risk_level !== 'normaal')
                            <div class="flex flex-wrap gap-1">
                                @foreach($log->risk_flags as $flag)
                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-semibold inline-flex items-center gap-1 {{ $log->risk_level === 'hoog' ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70' : 'bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70' }}">
                                    <i class="fa-solid fa-circle-exclamation shrink-0 text-[9px]"></i>
                                    <span>{{ $flag }}</span>
                                </span>
                                @endforeach
                            </div>
                            @elseif($log->risk_reason && $log->risk_level !== 'normaal')
                            <div class="px-2 py-1 rounded-lg text-[11px] font-semibold flex items-start gap-1.5 {{ $log->risk_level === 'hoog' ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70' : 'bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70' }}">
                                <i class="fa-solid fa-circle-exclamation mt-0.5 shrink-0 text-[10px]"></i>
                                <span>{{ $log->risk_reason }}</span>
                            </div>
                            @else
                            <span class="text-slate-400 dark:text-slate-500 text-[11px]">Geen risico indicatoren</span>
                            @endif
                        </td>

                        <!-- Action / Details Button -->
                        <td class="py-3.5 px-4 whitespace-nowrap text-right">
                            <button type="button" 
                                    @click="openDetail(@js([
                                        'id' => $log->id,
                                        'description' => $log->description,
                                        'action' => $log->action,
                                        'category' => $log->category,
                                        'category_label' => $log->getCategoryLabel(),
                                        'risk_level' => $log->risk_level,
                                        'risk_score' => $log->risk_score,
                                        'ip_address' => $log->ip_address,
                                        'risk_reason' => $log->risk_reason,
                                        'risk_flags' => $log->risk_flags ?? [],
                                        'amount' => $log->amount,
                                        'created_at' => $log->created_at?->format('d-m-Y H:i:s'),
                                        'character_name' => $log->character?->name,
                                        'character_id' => $log->character_id,
                                        'user_name' => $log->user?->name,
                                        'details' => $log->details,
                                    ]))"
                                    class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-purple-100 dark:bg-slate-800 dark:hover:bg-purple-950/50 text-slate-700 hover:text-purple-700 dark:text-slate-300 dark:hover:text-purple-300 border border-slate-200/80 dark:border-slate-700 font-bold transition-all duration-150 active:scale-95 inline-flex items-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-code text-[10px]"></i>
                                <span>Inspecteer</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400 dark:text-slate-500">
                            <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 mx-auto flex items-center justify-center text-lg mb-2">
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
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $logs->links() }}
        </div>
        @endif
    </div>

    <!-- Inspector JSON Modal -->
    <div x-show="detailModalOpen" 
         x-transition.opacity
         @keydown.escape.window="detailModalOpen = false"
         class="fixed inset-0 z-50 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 pb-24 lg:pb-4" 
         style="display: none;">
        <div @click.away="detailModalOpen = false" 
             class="bg-white dark:bg-slate-900 rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-slate-200/90 dark:border-slate-800 space-y-4 max-h-[85vh] flex flex-col">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-code"></i>
                    </span>
                    <div>
                        <h3 class="font-black text-slate-900 dark:text-white text-sm">Audit Log Inspectie &bull; ID #<span x-text="selectedLog?.id"></span></h3>
                        <span class="text-[10px] text-slate-400 font-mono" x-text="selectedLog?.created_at"></span>
                    </div>
                </div>
                <button type="button" @click="detailModalOpen = false" aria-label="Sluiten" class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm cursor-pointer transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="space-y-3 overflow-y-auto pr-1 flex-1 text-xs">
                <div>
                    <span class="text-slate-400 dark:text-slate-500 font-bold block uppercase text-[10px]">Omschrijving:</span>
                    <p class="font-bold text-slate-800 dark:text-slate-200 text-sm" x-text="selectedLog?.description"></p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 bg-slate-50/90 dark:bg-slate-800/60 p-3 rounded-xl border border-slate-200/80 dark:border-slate-700/70">
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 font-bold block text-[10px]">Speler / Account:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200" x-text="selectedLog?.character_name || selectedLog?.user_name || 'Systeem'"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 font-bold block text-[10px]">Actie:</span>
                        <span class="font-mono font-bold text-slate-800 dark:text-slate-200" x-text="selectedLog?.action"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 font-bold block text-[10px]">Categorie:</span>
                        <span class="font-mono font-bold text-slate-800 dark:text-slate-200" x-text="selectedLog?.category_label || selectedLog?.category"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 font-bold block text-[10px]">Risicoscore:</span>
                        <span class="font-mono font-extrabold" :class="selectedLog?.risk_level === 'hoog' ? 'text-rose-600 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400'" x-text="selectedLog?.risk_score + '/100 (' + selectedLog?.risk_level + ')'"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 font-bold block text-[10px]">IP-adres:</span>
                        <span class="font-mono font-bold text-slate-800 dark:text-slate-200" x-text="selectedLog?.ip_address || 'N/A'"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 font-bold block text-[10px]">Bedrag:</span>
                        <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400" x-text="selectedLog?.amount ? '€' + Number(selectedLog.amount).toLocaleString('nl-NL') : 'N/A'"></span>
                    </div>
                </div>

                <div>
                    <span class="text-slate-400 dark:text-slate-500 font-bold block uppercase text-[10px] mb-1">Gedetecteerde Risico Redenen:</span>
                    <div class="p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200/80 dark:border-rose-800/70 text-rose-800 dark:text-rose-300 font-semibold" x-text="selectedLog?.risk_reason || 'Geen automatische risicoflags'"></div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-slate-400 dark:text-slate-500 font-bold block uppercase text-[10px]">Ruwe Payload / Metadata (JSON):</span>
                        <button type="button" 
                                @click="copyPayload()" 
                                class="px-2 py-0.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-[10px] transition flex items-center gap-1 cursor-pointer">
                            <i :class="copied ? 'fa-solid fa-check text-emerald-500' : 'fa-regular fa-copy'"></i>
                            <span x-text="copied ? 'Gekopieerd!' : 'Kopieer JSON'"></span>
                        </button>
                    </div>
                    <pre class="bg-slate-950 border border-slate-800 text-slate-100 p-3 rounded-xl font-mono text-[11px] overflow-x-auto max-h-48" x-text="JSON.stringify(selectedLog?.details, null, 2)"></pre>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-2">
                <div class="flex items-center gap-2 flex-wrap">
                    <template x-if="selectedLog?.ip_address">
                        <a :href="'{{ route('admin.audit_log.index') }}?ip=' + encodeURIComponent(selectedLog.ip_address)" 
                           class="px-2.5 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 font-bold text-[11px] transition">
                            <i class="fa-solid fa-network-wired mr-1"></i> Filter op IP
                        </a>
                    </template>
                    <template x-if="selectedLog?.character_id">
                        <a :href="'{{ route('admin.audit_log.index') }}?character_id=' + encodeURIComponent(selectedLog.character_id)" 
                           class="px-2.5 py-1.5 rounded-lg bg-purple-50 hover:bg-purple-100 dark:bg-purple-950/60 dark:hover:bg-purple-900/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 font-bold text-[11px] transition">
                            <i class="fa-solid fa-user mr-1"></i> Filter op speler
                        </a>
                    </template>
                    <template x-if="selectedLog?.character_name">
                        <a :href="'{{ route('admin.moderation.index') }}?search=' + encodeURIComponent(selectedLog.character_name)" 
                           class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 dark:hover:bg-rose-900/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 font-bold text-[11px] transition">
                            <i class="fa-solid fa-gavel mr-1"></i> Naar Moderatie
                        </a>
                    </template>
                </div>

                <button type="button" @click="detailModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-xs transition-all duration-150 active:scale-95 cursor-pointer">
                    Sluiten
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
