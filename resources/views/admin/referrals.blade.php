@extends('admin.layout')

@section('admin-content')
<div class="space-y-6">

    <!-- Header Stats Banner -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex items-center gap-4 text-slate-900 dark:text-white">
            <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 text-purple-700 dark:text-purple-400 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Totaal Aangebracht</span>
                <span class="text-2xl font-black text-slate-900 dark:text-white">{{ number_format($totalReferrals) }}</span>
                <span class="text-[10px] text-slate-500 dark:text-slate-400 block">Nieuwe geregistreerde spelers</span>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex items-center gap-4 text-slate-900 dark:text-white">
            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/70 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-crown"></i>
            </div>
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Gratis VIP Toegekend</span>
                <span class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ number_format($totalVipMonthsGranted) }} mnd</span>
                <span class="text-[10px] text-slate-500 dark:text-slate-400 block">{{ number_format($rewardedCount) }} uitbetaalde aanmeldingen</span>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex items-center gap-4 text-slate-900 dark:text-white">
            <div class="w-12 h-12 rounded-xl bg-sky-50 dark:bg-sky-950/60 border border-sky-200/80 dark:border-sky-800/70 text-sky-600 dark:text-sky-400 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Wachtend op 3-Bundel</span>
                <span class="text-2xl font-black text-sky-600 dark:text-sky-400">{{ number_format($unrewardedCount) }}</span>
                <span class="text-[10px] text-slate-500 dark:text-slate-400 block">Nog niet afgeronde trio's</span>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex items-center gap-4 text-slate-900 dark:text-white">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/70 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-robot"></i>
            </div>
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Automatische Regel</span>
                <span class="text-base font-black text-emerald-700 dark:text-emerald-400">3 Spelers = 1 Mnd VIP</span>
                <span class="text-[10px] text-slate-500 dark:text-slate-400 block">Directe toekenning & bericht</span>
            </div>
        </div>
    </div>

    <!-- Top Promoters & Leaderboard -->
    @if($topReferrers->isNotEmpty())
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs text-slate-900 dark:text-white">
        <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2 mb-4">
            <i class="fa-solid fa-trophy text-amber-500"></i>
            <span>Top Aanbrengers van de Kliniek</span>
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            @foreach($topReferrers as $idx => $tr)
            <div class="p-3 bg-slate-50/90 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700/70 flex items-center gap-3">
                <span class="w-7 h-7 rounded-lg bg-purple-600 text-white flex items-center justify-center text-xs font-black shrink-0">
                    #{{ $idx + 1 }}
                </span>
                <div class="min-w-0">
                    <span class="text-xs font-black text-slate-900 dark:text-white block truncate">{{ $tr->referrer->name ?? 'Onbekend' }}</span>
                    <span class="text-[10px] font-bold text-purple-600 dark:text-purple-400">{{ $tr->count }} aangebracht ({{ (int) floor($tr->count / 3) }} mnd VIP)</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Log Table Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden text-slate-900 dark:text-white">
        <!-- Table Header & Filter -->
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-purple-600 dark:text-purple-400"></i>
                    <span>Referrals & Uitbetalingen Logboek</span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Elke geregistreerde speler via een persoonlijke link wordt hier vastgelegd.</p>
            </div>

            <!-- Search Form -->
            <form action="{{ route('admin.referrals') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative flex-1 sm:w-64">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Zoek op speler of IP..." 
                           class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                </div>
                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-bold text-xs transition-all duration-150 active:scale-95 cursor-pointer shadow-xs shrink-0">
                    Filter
                </button>
                @if(request('search'))
                <a href="{{ route('admin.referrals') }}" class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer">
                    Reset
                </a>
                @endif
            </form>
        </div>

        <!-- Table View -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/90 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-black uppercase text-[10px] tracking-wider">
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">Aanbrenger (Referrer)</th>
                        <th class="px-5 py-3">Nieuwe Patiënt (Aangemeld)</th>
                        <th class="px-5 py-3">IP-Adres</th>
                        <th class="px-5 py-3">Status Beloning</th>
                        <th class="px-5 py-3">Datum Registratie</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 font-medium text-slate-700 dark:text-slate-300">
                    @forelse($referrals as $ref)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-3.5 font-mono text-slate-400 dark:text-slate-500 font-bold">#{{ $ref->id }}</td>
                        <td class="px-5 py-3.5">
                            @if($ref->referrer)
                                <div class="flex items-center gap-2">
                                    <span class="font-extrabold text-slate-900 dark:text-white">{{ $ref->referrer->name }}</span>
                                    @if($ref->referrer->isVip())
                                        <span class="px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70 text-[9px] font-black uppercase">VIP</span>
                                    @endif
                                </div>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 block">{{ $ref->referrer->user->email ?? '-' }}</span>
                            @else
                                <span class="text-slate-400 dark:text-slate-500 italic">Verwijderd</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            @if($ref->referred)
                                <span class="font-bold text-slate-900 dark:text-white">{{ $ref->referred->name }}</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 block">{{ $ref->referred->user->email ?? '-' }}</span>
                            @else
                                <span class="text-slate-400 dark:text-slate-500 italic">Verwijderd</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 font-mono text-[11px] text-slate-600 dark:text-slate-300">
                            {{ $ref->ip_address ?: 'Onbekend' }}
                        </td>
                        <td class="px-5 py-3.5">
                            @if($ref->rewarded)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-[10px] font-black border border-emerald-200/80 dark:border-emerald-800/70">
                                    <i class="fa-solid fa-check text-emerald-600 dark:text-emerald-400"></i> Uitbetaald (1 Mnd VIP)
                                </span>
                                @if($ref->rewarded_at)
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 block mt-0.5">{{ $ref->rewarded_at->format('d-m-Y H:i') }}</span>
                                @endif
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 text-[10px] font-bold border border-amber-200/80 dark:border-amber-800/70">
                                    <i class="fa-solid fa-clock text-amber-500 dark:text-amber-400"></i> Wachtend op 3 aanmeldingen
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400 font-mono text-[11px]">
                            {{ $ref->created_at->format('d-m-Y H:i') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-400 dark:text-slate-500 italic">
                            Nog geen referral registraties vastgelegd. Zodra spelers vrienden uitnodigen via hun dashboardlink verschijnen ze hier.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($referrals->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $referrals->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
