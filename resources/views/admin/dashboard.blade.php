@extends('admin.layout')

@section('admin-content')
<div class="space-y-6">

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Geregistreerd</span>
            <div class="flex items-center justify-between">
                <span class="text-xl sm:text-2xl font-black text-slate-900 font-mono">{{ $totalUsers }}</span>
                <i class="fa-solid fa-users text-purple-500 text-lg"></i>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Patiënten</span>
            <div class="flex items-center justify-between">
                <span class="text-xl sm:text-2xl font-black text-purple-700 font-mono">{{ $totalCharacters }}</span>
                <i class="fa-solid fa-id-card text-purple-500 text-lg"></i>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Actieve VIPs</span>
            <div class="flex items-center justify-between">
                <span class="text-xl sm:text-2xl font-black text-amber-500 font-mono">{{ $totalVips }}</span>
                <i class="fa-solid fa-crown text-amber-500 text-lg"></i>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Actieve Gangs</span>
            <div class="flex items-center justify-between">
                <span class="text-xl sm:text-2xl font-black text-blue-600 font-mono">{{ $totalGangs }}</span>
                <i class="fa-solid fa-skull-crossbones text-blue-500 text-lg"></i>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">In Isoleercel</span>
            <div class="flex items-center justify-between">
                <span class="text-xl sm:text-2xl font-black text-rose-600 font-mono">{{ $inmatesInPrison }}</span>
                <i class="fa-solid fa-lock text-rose-500 text-lg"></i>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Geld Circulatie</span>
            <div class="flex items-center justify-between">
                <span class="text-base sm:text-lg font-black text-emerald-600 font-mono truncate" title="€{{ number_format($totalCash, 0, ',', '.') }}">
                    €{{ $totalCash >= 1000000 ? number_format($totalCash / 1000000, 1) . 'M' : number_format($totalCash, 0, ',', '.') }}
                </span>
                <i class="fa-solid fa-money-bill-wave text-emerald-500 text-lg"></i>
            </div>
        </div>
    </div>

    <!-- Quick Actions Banner -->
    <div class="bg-linear-to-r from-purple-900 to-slate-900 rounded-2xl p-6 text-white border border-purple-500/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-extrabold">Snelle Medewerkers Handelingen</h3>
            <p class="text-xs text-purple-200 mt-1 max-w-xl">
                Ken direct VIP-lidmaatschap toe aan een Discord koper of pas de winkansen en kistbeloningen aan voor de hele kliniek.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <a href="{{ route('admin.players') }}" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-xs font-black uppercase tracking-wider transition text-white shadow">
                <i class="fa-solid fa-crown mr-1"></i> VIP Toekennen
            </a>
            <a href="{{ route('admin.gameplay') }}" class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-xs font-black uppercase tracking-wider transition text-white border border-white/20">
                <i class="fa-solid fa-sliders mr-1"></i> Winkansen
            </a>
        </div>
    </div>

    <!-- Recent Registrations Table -->
    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-purple-600"></i> Recent Ingeschreven Patiënten
            </h3>
            <a href="{{ route('admin.players') }}" class="text-xs font-bold text-purple-600 hover:underline">
                Alle spelers bekijken &raquo;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                        <th class="py-2.5 px-3">PATIËNT</th>
                        <th class="py-2.5 px-3">EMAIL</th>
                        <th class="py-2.5 px-3">GANG</th>
                        <th class="py-2.5 px-3 text-right">CONTANT</th>
                        <th class="py-2.5 px-3 text-right">BANK</th>
                        <th class="py-2.5 px-3 text-center">VIP STATUS</th>
                        <th class="py-2.5 px-3 text-right">ACTIE</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($recentPlayers as $p)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-3">
                            <div class="flex items-center gap-2.5">
                                <img src="{{ $p->avatar_url }}" alt="{{ $p->name }}" class="w-8 h-8 rounded-full object-cover border border-slate-200">
                                <div>
                                    <div class="font-extrabold text-slate-900 text-xs">{{ $p->name }}</div>
                                    <div class="text-[10px] text-slate-400">Lvl {{ $p->level }} • {{ $p->respect }} Respect</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-3 text-slate-500 font-mono text-[11px]">
                            {{ $p->user?->email ?? '-' }}
                        </td>
                        <td class="py-3 px-3 text-slate-600">
                            {{ $p->gang?->name ?? 'Geen Gang' }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono text-emerald-600 font-bold">
                            €{{ number_format($p->cash, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono text-blue-600 font-bold">
                            €{{ number_format($p->bank, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-3 text-center">
                            @if($p->is_vip)
                                <span class="bg-amber-100 text-amber-800 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full inline-flex items-center gap-1 border border-amber-200">
                                    <i class="fa-solid fa-crown text-amber-600"></i> VIP Actief
                                </span>
                            @else
                                <span class="bg-slate-100 text-slate-600 text-[10px] font-bold px-2.5 py-0.5 rounded-full">
                                    Standaard
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-right">
                            <a href="{{ route('admin.players', ['search' => $p->name]) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-purple-600 hover:text-white text-slate-700 text-xs font-bold transition">
                                Beheer
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-6 text-center text-slate-400 text-xs">Geen recente spelers gevonden.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
