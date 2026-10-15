@extends('admin.layout')

@section('admin-content')
<div class="space-y-6">

    <!-- Header Stats Banner -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Totaal Aangebracht</span>
                <span class="text-2xl font-black text-slate-900">{{ number_format($totalReferrals) }}</span>
                <span class="text-[10px] text-slate-500 block">Nieuwe geregistreerde spelers</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-crown"></i>
            </div>
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Gratis VIP Toegekend</span>
                <span class="text-2xl font-black text-amber-600">{{ number_format($totalVipMonthsGranted) }} mnd</span>
                <span class="text-[10px] text-slate-500 block">{{ number_format($rewardedCount) }} uitbetaalde aanmeldingen</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Wachtend op 3-Bundel</span>
                <span class="text-2xl font-black text-blue-600">{{ number_format($unrewardedCount) }}</span>
                <span class="text-[10px] text-slate-500 block">Nog niet afgeronde trio's</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-robot"></i>
            </div>
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Automatische Regel</span>
                <span class="text-base font-black text-emerald-700">3 Spelers = 1 Mnd VIP</span>
                <span class="text-[10px] text-slate-500 block">Directe toekenning & bericht</span>
            </div>
        </div>
    </div>

    <!-- Top Promoters & Leaderboard -->
    @if($topReferrers->isNotEmpty())
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs">
        <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2 mb-4">
            <i class="fa-solid fa-trophy text-amber-500"></i>
            <span>Top Aanbrengers van de Kliniek</span>
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            @foreach($topReferrers as $idx => $tr)
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center gap-3">
                <span class="w-7 h-7 rounded-lg bg-purple-600 text-white flex items-center justify-center text-xs font-black shrink-0">
                    #{{ $idx + 1 }}
                </span>
                <div class="min-w-0">
                    <span class="text-xs font-black text-slate-900 block truncate">{{ $tr->referrer->name ?? 'Onbekend' }}</span>
                    <span class="text-[10px] font-bold text-purple-700">{{ $tr->count }} aangebracht ({{ (int) floor($tr->count / 3) }} mnd VIP)</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Log Table Card -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        <!-- Table Header & Filter -->
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-purple-600"></i>
                    <span>Referrals & Uitbetalingen Logboek</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Elke geregistreerde speler via een persoonlijke link wordt hier vastgelegd.</p>
            </div>

            <!-- Search Form -->
            <form action="{{ route('admin.referrals') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative flex-1 sm:w-64">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Zoek op speler of IP..." 
                           class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:outline-none focus:border-purple-500 transition">
                </div>
                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs transition cursor-pointer shadow-xs shrink-0">
                    Filter
                </button>
                @if(request('search'))
                <a href="{{ route('admin.referrals') }}" class="px-2.5 py-1.5 rounded-xl bg-slate-100 text-slate-600 hover:text-slate-900 text-xs font-bold transition">
                    Reset
                </a>
                @endif
            </form>
        </div>

        <!-- Table View -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-black uppercase text-[10px] tracking-wider">
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">Aanbrenger (Referrer)</th>
                        <th class="px-5 py-3">Nieuwe Patiënt (Aangemeld)</th>
                        <th class="px-5 py-3">IP-Adres</th>
                        <th class="px-5 py-3">Status Beloning</th>
                        <th class="px-5 py-3">Datum Registratie</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($referrals as $ref)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-3.5 font-mono text-slate-400 font-bold">#{{ $ref->id }}</td>
                        <td class="px-5 py-3.5">
                            @if($ref->referrer)
                                <div class="flex items-center gap-2">
                                    <span class="font-extrabold text-slate-900">{{ $ref->referrer->name }}</span>
                                    @if($ref->referrer->isVip())
                                        <span class="px-1.5 py-0.2 rounded bg-amber-100 text-amber-800 text-[9px] font-black uppercase">VIP</span>
                                    @endif
                                </div>
                                <span class="text-[10px] text-slate-400 block">{{ $ref->referrer->user->email ?? '-' }}</span>
                            @else
                                <span class="text-slate-400 italic">Verwijderd</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            @if($ref->referred)
                                <span class="font-bold text-slate-900">{{ $ref->referred->name }}</span>
                                <span class="text-[10px] text-slate-400 block">{{ $ref->referred->user->email ?? '-' }}</span>
                            @else
                                <span class="text-slate-400 italic">Verwijderd</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 font-mono text-[11px]">
                            {{ $ref->ip_address ?: 'Onbekend' }}
                        </td>
                        <td class="px-5 py-3.5">
                            @if($ref->rewarded)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-black border border-emerald-200">
                                    <i class="fa-solid fa-check text-emerald-600"></i> Uitbetaald (1 Mnd VIP)
                                </span>
                                @if($ref->rewarded_at)
                                    <span class="text-[10px] text-slate-400 block mt-0.5">{{ $ref->rewarded_at->format('d-m-Y H:i') }}</span>
                                @endif
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 text-[10px] font-bold border border-amber-200">
                                    <i class="fa-solid fa-clock text-amber-500"></i> Wachtend op 3 aanmeldingen
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 font-mono text-[11px]">
                            {{ $ref->created_at->format('d-m-Y H:i') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-400 italic">
                            Nog geen referral registraties vastgelegd. Zodra spelers vrienden uitnodigen via hun dashboardlink verschijnen ze hier.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($referrals->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $referrals->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
