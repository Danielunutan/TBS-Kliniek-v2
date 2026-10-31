@extends('admin.layout')

@section('admin-content')
<div class="space-y-6">

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-1">Geregistreerd</span>
            <div class="flex items-center justify-between">
                <span class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white font-mono">{{ $totalUsers }}</span>
                <i class="fa-solid fa-users text-purple-500 text-lg"></i>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-1">Patiënten</span>
            <div class="flex items-center justify-between">
                <span class="text-xl sm:text-2xl font-black text-purple-700 dark:text-purple-400 font-mono">{{ $totalCharacters }}</span>
                <i class="fa-solid fa-id-card text-purple-500 text-lg"></i>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-1">Actieve VIPs</span>
            <div class="flex items-center justify-between">
                <span class="text-xl sm:text-2xl font-black text-amber-500 dark:text-amber-400 font-mono">{{ $totalVips }}</span>
                <i class="fa-solid fa-crown text-amber-500 text-lg"></i>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-1">Actieve Gangs</span>
            <div class="flex items-center justify-between">
                <span class="text-xl sm:text-2xl font-black text-sky-600 dark:text-sky-400 font-mono">{{ $totalGangs }}</span>
                <i class="fa-solid fa-skull-crossbones text-sky-500 text-lg"></i>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-1">In Isoleercel</span>
            <div class="flex items-center justify-between">
                <span class="text-xl sm:text-2xl font-black text-rose-600 dark:text-rose-400 font-mono">{{ $inmatesInPrison }}</span>
                <i class="fa-solid fa-lock text-rose-500 text-lg"></i>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-1">Geld Circulatie</span>
            <div class="flex items-center justify-between">
                <span class="text-base sm:text-lg font-black text-emerald-600 dark:text-emerald-400 font-mono truncate" title="€{{ number_format($totalCash, 0, ',', '.') }}">
                    €{{ $totalCash >= 1000000 ? number_format($totalCash / 1000000, 1) . 'M' : number_format($totalCash, 0, ',', '.') }}
                </span>
                <i class="fa-solid fa-money-bill-wave text-emerald-500 text-lg"></i>
            </div>
        </div>
    </div>

    <!-- Quick Actions Banner -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 text-slate-900 dark:text-white border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Snelle Medewerkers Handelingen</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-xl">
                Ken direct VIP-lidmaatschap toe aan een Discord koper of pas de winkansen en kistbeloningen aan voor de hele kliniek.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            @if(Auth::user()->isOwner() || Auth::user()->role === 'admin' || Auth::user()->is_staff)
            <form action="{{ route('admin.system.migrate') }}" method="POST" class="inline"
                  data-confirm="Wil je alle openstaande database migraties én de balanscontrole direct uitvoeren?"
                  data-confirm-btn="Migraties Uitvoeren"
                  data-confirm-type="warning"
                  data-confirm-icon="fa-database">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/60 dark:hover:bg-amber-900/60 text-xs font-black uppercase tracking-wider transition-all duration-150 active:scale-95 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70 cursor-pointer">
                    <i class="fa-solid fa-database mr-1 text-amber-600 dark:text-amber-400"></i> Migraties Uitvoeren
                </button>
            </form>
            <form action="{{ route('admin.system.normalize') }}" method="POST" class="inline"
                  data-confirm="Wil je alle overschreden stats (omzetten naar XP + begrenzen op level-plafond), winkel/depot prijzen, dubbele uitrusting en verlopen timers nu controleren en rechtzetten?"
                  data-confirm-btn="Alles Controleren & Rechtzetten"
                  data-confirm-type="warning"
                  data-confirm-icon="fa-scale-balanced">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-xs font-black uppercase tracking-wider transition-all duration-150 active:scale-95 text-white shadow-xs cursor-pointer">
                    <i class="fa-solid fa-scale-balanced mr-1"></i> Stats &amp; Balans Rechtzetten
                </button>
            </form>
            @endif
            <a href="{{ route('admin.tickets.index') }}" class="px-4 py-2 rounded-xl bg-purple-50 hover:bg-purple-100 dark:bg-purple-950/60 dark:hover:bg-purple-900/60 text-xs font-black uppercase tracking-wider transition-all duration-150 active:scale-95 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70">
                <i class="fa-solid fa-headset mr-1 text-purple-600 dark:text-purple-400"></i> Support Tickets
            </a>
            <a href="{{ route('admin.players') }}" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-xs font-black uppercase tracking-wider transition-all duration-150 active:scale-95 text-white shadow-xs">
                <i class="fa-solid fa-crown mr-1"></i> VIP Toekennen
            </a>
            <a href="{{ route('admin.gameplay') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-xs font-black uppercase tracking-wider transition-all duration-150 active:scale-95 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700">
                <i class="fa-solid fa-sliders mr-1 text-slate-500 dark:text-slate-400"></i> Winkansen
            </a>
        </div>
    </div>

    <!-- Systeem & Spelbalans Herstelmigratie Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 text-slate-900 dark:text-white border {{ (($exceededStatsCount ?? 0) > 0 || ($migrationStatus['pending_count'] ?? 0) > 0) ? 'border-amber-300 dark:border-amber-700/80 bg-amber-50/30 dark:bg-amber-950/20' : 'border-emerald-200/90 dark:border-emerald-800/70' }} shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl {{ (($exceededStatsCount ?? 0) > 0 || ($migrationStatus['pending_count'] ?? 0) > 0) ? 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/80' : 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/70' }} border flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white">
                        Systeem &amp; Spelbalans Herstelmigratie
                    </h3>
                    @if(($migrationStatus['pending_count'] ?? 0) > 0)
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70">
                            {{ $migrationStatus['pending_count'] }} openstaande migratie(s)
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/70">
                            Alle {{ $migrationStatus['total'] ?? 0 }} migraties uitgevoerd
                        </span>
                    @endif
                    @if(($exceededStatsCount ?? 0) > 0)
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70">
                            {{ $exceededStatsCount }} speler(s) boven level-plafond
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/70">
                            Alle stats binnen level-plafond
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-3xl leading-relaxed">
                    Voert openstaande database-migraties uit, controleert alle spelers en zet overschreden eigenschappen (boven <code class="px-1 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-mono text-[11px]">100 + level &times; 50</code>) automatisch om naar <strong>+45 XP per punt</strong>, synchroniseert Winkel/Depot prijzen (zoals de Oude Sleutel), stort dubbele uitrusting terug op de bank en ruimt verlopen timers, isoleercelstraffen en rad-draaien op.
                </p>
                @if(!empty($migrationStatus['pending_files']))
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach($migrationStatus['pending_files'] as $pendingFile)
                            <span class="px-2 py-0.5 rounded bg-amber-100/80 dark:bg-amber-950/60 text-amber-900 dark:text-amber-200 font-mono text-[10px] border border-amber-200 dark:border-amber-800/80">
                                {{ $pendingFile }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        <div class="shrink-0 flex flex-wrap items-center gap-2">
            <form action="{{ route('admin.system.migrate') }}" method="POST">
                @csrf
                <button type="submit" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/60 dark:hover:bg-amber-900/60 text-amber-900 dark:text-amber-200 border border-amber-300 dark:border-amber-700 text-xs font-black uppercase tracking-wider transition-all duration-150 active:scale-95 shadow-2xs flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-database text-[11px] text-amber-600 dark:text-amber-400"></i>
                    <span>Migraties Uitvoeren</span>
                </button>
            </form>
            <form action="{{ route('admin.system.normalize') }}" method="POST">
                @csrf
                <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white text-xs font-black uppercase tracking-wider transition-all duration-150 active:scale-95 shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-play text-[10px]"></i>
                    <span>Herstelmigratie Nu Uitvoeren</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Recent Registrations Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4 text-slate-900 dark:text-white">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-purple-600 dark:text-purple-400"></i> Recent Ingeschreven Patiënten
            </h3>
            <a href="{{ route('admin.players') }}" class="text-xs font-bold text-purple-600 dark:text-purple-400 hover:underline">
                Alle spelers bekijken &raquo;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50/90 dark:bg-slate-800/60 text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200/80 dark:border-slate-800">
                        <th class="py-2.5 px-3">PATIËNT</th>
                        <th class="py-2.5 px-3">EMAIL</th>
                        <th class="py-2.5 px-3">GANG</th>
                        <th class="py-2.5 px-3 text-right">CONTANT</th>
                        <th class="py-2.5 px-3 text-right">BANK</th>
                        <th class="py-2.5 px-3 text-center">VIP STATUS</th>
                        <th class="py-2.5 px-3 text-right">ACTIE</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 font-medium">
                    @forelse($recentPlayers as $p)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="py-3 px-3">
                            <div class="flex items-center gap-2.5">
                                <img src="{{ $p->avatar_url }}" alt="{{ $p->name }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 dark:border-slate-700">
                                <div>
                                    <div class="font-extrabold text-slate-900 dark:text-white text-xs">{{ $p->name }}</div>
                                    <div class="text-[10px] text-slate-400 dark:text-slate-500">Lvl {{ $p->level }} • {{ $p->respect }} Respect</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-3 text-slate-500 dark:text-slate-400 font-mono text-[11px]">
                            {{ $p->user?->email ?? '-' }}
                        </td>
                        <td class="py-3 px-3 text-slate-600 dark:text-slate-300">
                            {{ $p->gang?->name ?? 'Geen Gang' }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono text-emerald-600 dark:text-emerald-400 font-bold">
                            €{{ number_format($p->cash, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono text-sky-600 dark:text-sky-400 font-bold">
                            €{{ number_format($p->bank, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-3 text-center">
                            @if($p->is_vip)
                                <span class="bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full inline-flex items-center gap-1 border border-amber-200/80 dark:border-amber-800/70">
                                    <i class="fa-solid fa-crown text-amber-600 dark:text-amber-400"></i> VIP Actief
                                </span>
                            @else
                                <span class="bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] font-bold px-2.5 py-0.5 rounded-full border border-slate-200/80 dark:border-slate-700">
                                    Standaard
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-right">
                            <a href="{{ route('admin.players', ['search' => $p->name]) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-purple-600 hover:text-white dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-purple-600 dark:hover:text-white text-slate-700 text-xs font-bold transition">
                                Beheer
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-6 text-center text-slate-400 dark:text-slate-500 text-xs">Geen recente spelers gevonden.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
