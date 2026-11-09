@extends('layouts.app')

@section('content')
@php
    $hourlyWage = $hourlyWage ?? 0;
    $dailyWage = $dailyWage ?? ($hourlyWage * 24);
    $recentAttacks = $recentAttacks ?? collect();
    $warsWon = $warsWon ?? 0;
    $warsLost = $warsLost ?? 0;
@endphp
<div class="space-y-6" x-data="{
    memberSearch: '',
    onlyOnline: false,
    members: @js($gangMembersData ?? []),
    get list() {
        let q = this.memberSearch.toLowerCase().trim();
        return this.members.filter(m => {
            if (q && !m.name.toLowerCase().includes(q) && !m.role_label.toLowerCase().includes(q)) return false;
            if (this.onlyOnline && !m.is_online) return false;
            return true;
        });
    }
}">

    <!-- Breadcrumb & Top Controls -->
    <div class="flex items-center justify-between text-xs font-bold text-slate-500 dark:text-slate-400">
        <div class="flex items-center gap-2">
            <a href="{{ route('gangs.index', ['tab' => 'ranglijst']) }}" class="hover:text-purple-600 dark:hover:text-purple-400 flex items-center gap-1.5 transition">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Gangs Ranglijst</span>
            </a>
            <span class="text-slate-300 dark:text-slate-700">&bull;</span>
            <span class="text-slate-900 dark:text-slate-200 flex items-center gap-1.5">
                <i class="fa-solid fa-shield-halved text-purple-600 dark:text-purple-400"></i>
                <span>{{ $gang->name }}</span>
                <span class="font-mono text-purple-600 dark:text-purple-400">[{{ $gang->tag }}]</span>
            </span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('gangs.index', ['tab' => 'ranglijst']) }}" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-trophy text-amber-500"></i> Ranglijst
            </a>
        </div>
    </div>

    @if($character->isImprisoned())
    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-800/70 flex items-center gap-3 text-rose-800 dark:text-rose-200 text-sm">
        <i class="fa-solid fa-lock text-base text-rose-600 shrink-0"></i>
        <span>Je zit in de isoleercel en kunt op dit moment geen bendelidmaatschap aanvragen.</span>
    </div>
    @endif

    @if($character->isComatose() || $character->needsReviveSerum())
    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-800/70 flex items-center gap-3 text-rose-800 dark:text-rose-200 text-sm">
        <i class="fa-solid fa-bed-pulse text-base text-rose-600 shrink-0"></i>
        <span>Je ligt in het ziekenhuis/coma en kunt op dit moment geen bendelidmaatschap aanvragen.</span>
    </div>
    @endif

    <!-- Top Hero Banner Card -->
    <!-- Top Hero Banner Card (High-Contrast, Readable in Both Light and Dark Mode) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs">
        <div class="min-h-[220px] sm:min-h-[240px] md:h-64 w-full overflow-hidden relative bg-purple-950 flex flex-col justify-between p-4 sm:p-6">
            <img src="{{ $gang->banner_url }}" alt="{{ $gang->name }} Banner" class="absolute inset-0 w-full h-full object-cover">
            <!-- Dark gradient overlay ensuring text on banner is ALWAYS 100% crystal clear in both light and dark modes -->
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-slate-950/50 to-black/30 pointer-events-none"></div>

            <!-- Top Right Action Controls -->
            <div class="relative z-10 flex items-center justify-end gap-2">
                <button type="button" 
                        @click="window.tbsOpenReportModal({ type: 'gang', id: {{ $gang->id }}, name: @js($gang->name) })"
                        class="px-3 py-1.5 rounded-xl bg-black/60 hover:bg-rose-900/80 text-white text-xs font-bold border border-white/20 shadow-xs flex items-center gap-1.5 backdrop-blur-xs transition cursor-pointer"
                        title="Rapporteer deze bende bij moderatie">
                    <i class="fa-solid fa-flag text-rose-400"></i>
                    <span class="hidden sm:inline">Rapporteer Bende</span>
                </button>
            </div>

            <!-- Gang Identity Hero Header on Banner (Always White & Sharp on dark banners) -->
            <div class="relative z-10 flex flex-col sm:flex-row sm:items-end gap-4 pt-4">
                <!-- Gang Emblem / Avatar -->
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-gradient-to-br from-purple-600 to-indigo-700 border-2 border-white/90 shadow-2xl flex items-center justify-center text-3xl sm:text-4xl text-white shrink-0 overflow-hidden ring-4 ring-black/40 backdrop-blur-xs">
                    @if($gang->avatar)
                        <img src="{{ $gang->avatar_url }}" alt="{{ $gang->name }}" class="w-full h-full object-cover">
                    @else
                        <i class="fa-solid {{ $gang->emblem ?? 'fa-crown' }}"></i>
                    @endif
                </div>

                <div class="space-y-1.5 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
                        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-2 drop-shadow-[0_2px_4px_rgba(0,0,0,0.85)]">
                            <span>{{ $gang->name }}</span>
                            <span class="text-purple-200 font-mono text-xs sm:text-sm font-extrabold bg-purple-900/80 border border-purple-400/50 px-2.5 py-0.5 rounded-lg backdrop-blur-xs shadow-xs">
                                [{{ $gang->tag }}]
                            </span>
                            <i class="fa-solid fa-circle-check text-purple-400 text-sm sm:text-base drop-shadow-sm" title="Geverifieerde Bende"></i>
                        </h1>
                    </div>

                    <!-- Meta details: Leader, Members, Status with crisp white text & drop shadow -->
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs font-bold text-white/90 drop-shadow-[0_1px_3px_rgba(0,0,0,0.85)]">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-crown text-amber-400"></i>
                            <span class="text-slate-300 font-medium">Leider:</span>
                            @if($gang->leader)
                                <a href="{{ route('profile.show', $gang->leader->name) }}" class="text-purple-300 hover:text-white hover:underline transition">
                                    {{ $gang->leader->name }}
                                </a>
                            @else
                                <span class="text-slate-400 italic">Onbekend</span>
                            @endif
                        </span>

                        <span class="text-white/40">&bull;</span>

                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-users text-purple-300"></i>
                            <span>{{ $gang->members->count() }} / {{ $gang->max_members ?? 10 }} leden</span>
                        </span>

                        <span class="text-white/40">&bull;</span>

                        <span class="inline-flex items-center gap-1.5 text-emerald-400">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>{{ $onlineMembersCount }} online</span>
                        </span>

                        @if((bool)($gang->is_open ?? true))
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-emerald-500/25 text-emerald-300 border border-emerald-400/40 backdrop-blur-xs shadow-xs">
                                <i class="fa-solid fa-lock-open text-[9px]"></i> Open Bende
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-black/50 text-slate-300 border border-white/10 backdrop-blur-xs">
                                <i class="fa-solid fa-lock text-[9px]"></i> Gesloten
                            </span>
                        @endif

                        @if(($gang->min_respect ?? 0) > 0)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-amber-500/25 text-amber-300 border border-amber-400/40 backdrop-blur-xs shadow-xs">
                                <i class="fa-solid fa-fire text-amber-400 text-[9px]"></i> Min. {{ number_format($gang->min_respect, 0, ',', '.') }} Respect
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Call-to-action toolbar below banner -->
        <div class="px-6 py-3.5 bg-slate-50/90 dark:bg-slate-800/60 border-t border-slate-200/80 dark:border-slate-800 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
                <i class="fa-solid fa-shield-halved text-purple-600 dark:text-purple-400"></i>
                <span>Openbaar Bende Profiel</span>
            </div>

            <!-- Call-to-action buttons for the viewer -->
            <div class="flex items-center gap-2.5 shrink-0">
                @if($isMyGang)
                    <span class="px-3.5 py-1.5 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-black text-xs inline-flex items-center gap-1.5 border border-purple-200/80 dark:border-purple-800/70">
                        <i class="fa-solid fa-check text-purple-600 dark:text-purple-400"></i> Jouw Bende
                    </span>
                    <a href="{{ route('gangs.index') }}" class="px-4 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 active:scale-95 cursor-pointer shadow-xs flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Bende Dashboard</span>
                    </a>
                @elseif(! $character->gang_id)
                    @if($hasPendingRequest)
                        <span class="px-3.5 py-1.5 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 font-extrabold text-xs inline-flex items-center gap-2 border border-amber-200/80 dark:border-amber-800/70 shadow-2xs">
                            <i class="fa-solid fa-clock-rotate-left text-amber-500"></i>
                            <span>Aanvraag In Behandeling</span>
                        </span>
                    @elseif((bool)($gang->is_open ?? true))
                        @if(($character->respect ?? 0) >= ($gang->min_respect ?? 0))
                            <form action="{{ route('gangs.request-join', $gang->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" @disabled($character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum()) class="px-4 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black text-xs uppercase tracking-wider transition-all duration-150 active:scale-95 shadow-xs flex items-center gap-2 cursor-pointer">
                                    <i class="fa-solid fa-user-plus"></i>
                                    <span>Lidmaatschap Aanvragen</span>
                                </button>
                            </form>
                        @else
                            <button type="button" disabled class="px-4 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 font-bold text-xs border border-slate-200/80 dark:border-slate-700/60 flex items-center gap-2 cursor-not-allowed" title="Je hebt minimaal {{ number_format($gang->min_respect, 0, ',', '.') }} respect nodig">
                                <i class="fa-solid fa-lock"></i>
                                <span>Min. {{ number_format($gang->min_respect, 0, ',', '.') }} Respect Vereist</span>
                            </button>
                        @endif
                    @else
                        <span class="px-3.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 font-bold text-xs inline-flex items-center gap-2 border border-slate-200/80 dark:border-slate-700/60">
                            <i class="fa-solid fa-lock"></i>
                            <span>Lidmaatschap Gesloten</span>
                        </span>
                    @endif
                @else
                    <!-- Viewer is member of another gang -->
                    @if($character->gang && in_array($character->gang_role, ['leader', 'co_leader']))
                        <a href="{{ route('gangs.index', ['tab' => 'oorlog']) }}" class="px-4 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 active:scale-95 shadow-xs flex items-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-crosshairs"></i>
                            <span>Bende Oorlog</span>
                        </a>
                    @else
                        <span class="px-3.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs inline-flex items-center gap-2 border border-slate-200/80 dark:border-slate-700">
                            <i class="fa-solid fa-shield text-purple-500"></i>
                            <span>Lid van [{{ $character->gang->tag }}]</span>
                        </span>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!-- 6 KEY STATS CARDS -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Totaal Respect -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 text-center space-y-1 shadow-xs hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200">
            <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">BENDE RESPECT</span>
            <div class="flex items-center justify-center gap-1.5 text-purple-700 dark:text-purple-400 text-lg font-black font-mono">
                <i class="fa-solid fa-fire text-xs text-purple-500"></i>
                <span>{{ number_format($totalRespect, 0, ',', '.') }}</span>
            </div>
            <span class="text-[10px] text-slate-400 dark:text-slate-500 block font-medium">Collectieve reputatie</span>
        </div>

        <!-- Bende Kas (Treasury) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 text-center space-y-1 shadow-xs hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200">
            <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">BENDE KAS</span>
            <div class="flex items-center justify-center gap-1.5 text-emerald-700 dark:text-emerald-400 text-lg font-black font-mono">
                <i class="fa-solid fa-vault text-xs text-emerald-600"></i>
                <span>€{{ number_format($gang->treasury, 0, ',', '.') }}</span>
            </div>
            <span class="text-[10px] text-slate-400 dark:text-slate-500 block font-medium">Gezamenlijke kluis</span>
        </div>

        <!-- Uurloon Bonus -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 text-center space-y-1 shadow-xs hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200">
            <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">UURLOON</span>
            <div class="flex items-center justify-center gap-1 text-amber-700 dark:text-amber-400 text-lg font-black font-mono">
                <i class="fa-solid fa-coins text-xs text-amber-500"></i>
                <span>€{{ number_format($hourlyWage, 0, ',', '.') }}</span>
            </div>
            <span class="text-[10px] text-slate-400 dark:text-slate-500 block font-medium">Dag: €{{ number_format($dailyWage, 0, ',', '.') }}</span>
        </div>

        <!-- Leden & Online -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 text-center space-y-1 shadow-xs hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200">
            <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">LEDEN</span>
            <div class="flex items-center justify-center gap-1.5 text-blue-700 dark:text-blue-400 text-lg font-black font-mono">
                <i class="fa-solid fa-users text-xs text-blue-500"></i>
                <span>{{ $gang->members->count() }}/{{ $gang->max_members ?? 10 }}</span>
            </div>
            <span class="text-[10px] text-emerald-600 dark:text-emerald-400 block font-bold">{{ $onlineMembersCount }} nu actief</span>
        </div>

        <!-- Smokkelkiosken -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 text-center space-y-1 shadow-xs hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200">
            <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">SMOKKELKIOSKEN</span>
            <div class="flex items-center justify-center gap-1.5 text-purple-700 dark:text-purple-400 text-lg font-black font-mono">
                <i class="fa-solid fa-store text-xs text-purple-500"></i>
                <span>{{ $gang->bling_shops ?? 0 }}</span>
            </div>
            <span class="text-[10px] text-slate-400 dark:text-slate-500 block font-medium">+€100/u (max 250 kiosken)</span>
        </div>

        <!-- Panden / Vastgoed -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 text-center space-y-1 shadow-xs hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200">
            <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">BENDE PANDEN</span>
            <div class="flex items-center justify-center gap-1.5 text-indigo-700 dark:text-indigo-400 text-lg font-black font-mono">
                <i class="fa-solid fa-building text-xs text-indigo-500"></i>
                <span>{{ $gang->properties->count() }}</span>
            </div>
            <span class="text-[10px] text-slate-400 dark:text-slate-500 block font-medium">Kliniek vastgoed</span>
        </div>
    </div>

    <!-- MAIN TWO COLUMN DETAILS LAYOUT -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left / Main Column: Members, Description & War Log -->
        <div class="lg:col-span-8 space-y-6">

            <!-- Bende Manifest / Beschrijving -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-scroll text-purple-600 dark:text-purple-400"></i>
                        <span>Bende Manifest & Omschrijving</span>
                    </h3>
                    <span class="text-[10px] font-mono font-bold text-slate-400 dark:text-slate-500">
                        Specialiteit: {{ $gang->specialty ?: 'Kantine Controle' }}
                    </span>
                </div>
                <div class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed italic bg-slate-50/90 dark:bg-slate-800/60 p-4 rounded-xl border border-slate-200/80 dark:border-slate-700/70">
                    {{ $gang->description ?: 'Deze bende heeft nog geen publiek manifest of omschrijving achtergelaten, medepatiënt.' }}
                </div>
            </div>

            <!-- ALLE LEDEN Table with Live Search & Online Toggle -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white">
                        <i class="fa-solid fa-users text-purple-600 dark:text-purple-400"></i>
                        <span>ALLE LEDEN</span>
                        <span class="text-slate-400 dark:text-slate-500 font-normal">({{ $gang->members->count() }})</span>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button type="button" 
                                @click="onlyOnline = !onlyOnline" 
                                :class="onlyOnline ? 'bg-emerald-600 text-white font-black shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 font-bold border border-slate-200/80 dark:border-slate-700'"
                                class="px-2.5 py-1.5 rounded-xl text-xs transition-all duration-150 active:scale-95 cursor-pointer flex items-center gap-1.5 shrink-0">
                            <span class="w-2 h-2 rounded-full" :class="onlyOnline ? 'bg-emerald-100' : 'bg-emerald-500 animate-pulse'"></span>
                            <span>Online ({{ $onlineMembersCount }})</span>
                        </button>
                        <div class="relative flex-1 sm:flex-none sm:w-48">
                            <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                            <input type="text" 
                                   x-model="memberSearch" 
                                   placeholder="Zoek bendelid..." 
                                   class="w-full bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl pl-7 pr-6 py-1.5 text-xs focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                            <button type="button" x-show="memberSearch" @click="memberSearch = ''" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-[10px]">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/90 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 border-b border-slate-200/80 dark:border-slate-800 text-[10px] font-extrabold uppercase tracking-wider">
                                <th class="py-2.5 px-3">PATIËNT</th>
                                <th class="py-2.5 px-3">ROL</th>
                                <th class="py-2.5 px-3 text-right">LEVEL</th>
                                <th class="py-2.5 px-3 text-right">RESPECT</th>
                                <th class="py-2.5 px-3 text-right">ACTIE</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 font-medium">
                            <template x-for="member in list" :key="member.id">
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                    <td class="py-3 px-3">
                                        <div class="flex items-center gap-3">
                                            <a :href="member.profile_url" class="relative shrink-0">
                                                <img :src="member.avatar_url" :alt="member.name" class="w-8 h-8 rounded-full object-cover border border-slate-200 dark:border-slate-700">
                                                <span class="absolute -bottom-0.5 -right-0.5 w-2 h-2 rounded-full border border-white dark:border-slate-900"
                                                      :class="member.is_online ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-600'"
                                                      :title="member.online_label">
                                                </span>
                                            </a>
                                            <div>
                                                <a :href="member.profile_url" class="font-bold text-slate-900 dark:text-white hover:text-purple-600 dark:hover:text-purple-400 transition" x-text="member.name"></a>
                                                <span class="text-[10px] text-slate-400 dark:text-slate-500 block" x-text="member.is_online ? 'Nu online' : ('Actief ' + member.last_active_at)"></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="text-xs font-bold"
                                              :class="{
                                                  'text-amber-600 dark:text-amber-400': member.gang_role === 'leader',
                                                  'text-purple-700 dark:text-purple-400': member.gang_role === 'co_leader',
                                                  'text-slate-500 dark:text-slate-400': member.gang_role !== 'leader' && member.gang_role !== 'co_leader'
                                              }"
                                              x-text="member.role_label">
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-right font-bold text-slate-700 dark:text-slate-200" x-text="member.level"></td>
                                    <td class="py-3 px-3 text-right font-extrabold text-purple-700 dark:text-purple-400 font-mono" x-text="member.respect_formatted"></td>
                                    <td class="py-3 px-3 text-right">
                                        <div class="inline-flex items-center gap-1.5 justify-end">
                                            <a :href="member.message_url" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 text-[10px] font-bold transition-all duration-150 active:scale-95 inline-flex items-center gap-1" title="Stuur privébericht">
                                                <i class="fa-solid fa-envelope text-[9px] text-purple-600 dark:text-purple-400"></i>
                                                <span class="hidden sm:inline">Bericht</span>
                                            </a>
                                            <a :href="member.profile_url" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 text-[10px] font-bold transition-all duration-150 active:scale-95 inline-flex items-center gap-1" title="Bekijk dossier">
                                                <i class="fa-solid fa-id-card text-[9px]"></i>
                                                <span class="hidden sm:inline">Dossier</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <div x-show="list.length === 0" class="py-8 text-center text-xs text-slate-400 dark:text-slate-500">
                        Geen bendeleden gevonden die voldoen aan je zoekcriteria.
                    </div>
                </div>
            </div>

            <!-- Recente Bende Oorlogen / Veldslagen -->
            @if($recentAttacks->isNotEmpty())
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-crosshairs text-rose-600 dark:text-rose-400"></i>
                        <span>Recente Veldslagen & Oorlogsgeschiedenis</span>
                    </h3>
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500">
                        Totaal: {{ $warsWon }} Overwinningen &bull; {{ $warsLost }} Nederlagen
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/90 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 border-b border-slate-200/80 dark:border-slate-800 text-[10px] font-extrabold uppercase tracking-wider">
                                <th class="py-2.5 px-3">AANVALLER</th>
                                <th class="py-2.5 px-3">VERDEDIGER</th>
                                <th class="py-2.5 px-3">UITSLAG</th>
                                <th class="py-2.5 px-3 text-right">BUIT</th>
                                <th class="py-2.5 px-3 text-right">TIJD</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 font-medium">
                            @foreach($recentAttacks as $att)
                                @php
                                    $isThisGangAttacker = $att->attacker_gang_id === $gang->id;
                                    $thisGangWon = ($isThisGangAttacker && $att->won) || (! $isThisGangAttacker && ! $att->won);
                                @endphp
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                    <td class="py-2.5 px-3">
                                        @if($att->attackerGang)
                                            <a href="{{ route('gangs.show', $att->attackerGang->id) }}" class="font-bold {{ $att->attacker_gang_id === $gang->id ? 'text-purple-600 dark:text-purple-400' : 'text-slate-800 dark:text-slate-200' }} hover:underline">
                                                [{{ $att->attackerGang->tag }}] {{ $att->attackerGang->name }}
                                            </a>
                                        @else
                                            <span class="text-slate-400 dark:text-slate-500 italic">Onbekend</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3">
                                        @if($att->targetGang)
                                            <a href="{{ route('gangs.show', $att->targetGang->id) }}" class="font-bold {{ $att->target_gang_id === $gang->id ? 'text-purple-600 dark:text-purple-400' : 'text-slate-800 dark:text-slate-200' }} hover:underline">
                                                [{{ $att->targetGang->tag }}] {{ $att->targetGang->name }}
                                            </a>
                                        @else
                                            <span class="text-slate-400 dark:text-slate-500 italic">Onbekend</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3">
                                        @if($thisGangWon)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/70">
                                                <i class="fa-solid fa-trophy text-[9px]"></i> Overwinning
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70">
                                                <i class="fa-solid fa-skull text-[9px]"></i> Nederlaag
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                        @if($att->stolen_cash > 0)
                                            €{{ number_format($att->stolen_cash, 0, ',', '.') }}
                                        @else
                                            <span class="text-slate-400 dark:text-slate-500 font-normal">Geen</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 text-right text-[10px] text-slate-400 dark:text-slate-500">
                                        {{ $att->created_at ? $att->created_at->diffForHumans() : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>

        <!-- Right Column / Sidebar: Gang Factsheet & Alliances -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Bende Factsheet & Gegevens (Matches internal gang view) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-id-card-clip text-purple-600 dark:text-purple-400"></i>
                        <span>Bende Gegevens</span>
                    </h4>
                    <span class="text-[10px] font-mono text-purple-600 dark:text-purple-400 font-extrabold">
                        [{{ $gang->tag }}]
                    </span>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800/80 text-xs">
                    <!-- Bende Leider -->
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400 flex items-center gap-2">
                            <i class="fa-solid fa-crown text-amber-500"></i> Bende Leider
                        </span>
                        @if($gang->leader)
                            <a href="{{ route('profile.show', $gang->leader->name) }}" class="font-bold text-purple-700 dark:text-purple-400 hover:underline flex items-center gap-1">
                                {{ $gang->leader->name }}
                                <i class="fa-solid fa-circle-check text-purple-600 text-[10px]"></i>
                            </a>
                        @else
                            <span class="text-slate-400 dark:text-slate-500 italic">Onbekend</span>
                        @endif
                    </div>

                    <!-- Kantine Smokkelaar -->
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400 flex items-center gap-2">
                            <i class="fa-solid fa-box-open text-purple-600 dark:text-purple-400"></i> Kantine Smokkelaar
                        </span>
                        @if($gang->personalShopper)
                            <a href="{{ route('profile.show', $gang->personalShopper->name) }}" class="font-bold text-slate-800 dark:text-slate-200 hover:text-purple-600 dark:hover:text-purple-400">
                                {{ $gang->personalShopper->name }}
                            </a>
                        @else
                            <span class="text-slate-400 dark:text-slate-500 italic">Nog niet aangewezen</span>
                        @endif
                    </div>

                    <!-- Kasbeheerder -->
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400 flex items-center gap-2">
                            <i class="fa-solid fa-vault text-emerald-600 dark:text-emerald-400"></i> Kasbeheerder
                        </span>
                        @if($gang->moneyMagnet)
                            <a href="{{ route('profile.show', $gang->moneyMagnet->name) }}" class="font-bold text-slate-800 dark:text-slate-200 hover:text-purple-600 dark:hover:text-purple-400">
                                {{ $gang->moneyMagnet->name }}
                            </a>
                        @else
                            <span class="text-slate-400 dark:text-slate-500 italic">Nog niet aangewezen</span>
                        @endif
                    </div>

                    <!-- Power (Aanval) -->
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400 flex items-center gap-2">
                            <i class="fa-solid fa-bolt-lightning text-purple-600 dark:text-purple-400"></i> Power (Aanval)
                        </span>
                        <span class="font-bold text-purple-700 dark:text-purple-400 font-mono">
                            {{ number_format($gang->attack_power ?? 500, 0, ',', '.') }}
                        </span>
                    </div>

                    <!-- Basis Verdediging -->
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400 flex items-center gap-2">
                            <i class="fa-solid fa-shield text-indigo-500"></i> Basis Verdediging
                        </span>
                        <span class="font-bold text-slate-900 dark:text-white font-mono">
                            {{ number_format($gang->protection_points ?? 1000, 0, ',', '.') }}
                        </span>
                    </div>

                    <!-- Enforcer Bewakers -->
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400 flex items-center gap-2">
                            <i class="fa-solid fa-user-shield text-blue-500"></i> Enforcer Bewakers
                        </span>
                        <span class="font-bold {{ $gang->npc_defense_bonus_percent > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }} font-mono">
                            +{{ $gang->npc_defense_bonus_percent }}%
                        </span>
                    </div>

                    <!-- Totale Verdediging -->
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400 flex items-center gap-2">
                            <i class="fa-solid fa-shield-halved text-emerald-600 dark:text-emerald-400"></i> Totale Verdediging
                        </span>
                        <span class="font-bold text-emerald-700 dark:text-emerald-400 font-mono">
                            {{ number_format($gang->total_defense, 0, ',', '.') }}
                        </span>
                    </div>

                    <!-- Oorlogsbalans -->
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400 flex items-center gap-2">
                            <i class="fa-solid fa-skull text-rose-500"></i> Oorlogsbalans
                        </span>
                        <span class="font-bold text-slate-900 dark:text-white">
                            <span class="text-emerald-600 dark:text-emerald-400">{{ $warsWon }} W</span> &bull; <span class="text-rose-600 dark:text-rose-400">{{ $warsLost }} V</span>
                        </span>
                    </div>

                    <!-- Minimum Respect -->
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400 flex items-center gap-2">
                            <i class="fa-solid fa-star text-amber-500"></i> Min. Respect
                        </span>
                        <span class="font-extrabold text-amber-700 dark:text-amber-400 font-mono">
                            {{ number_format($gang->min_respect ?? 0, 0, ',', '.') }}
                        </span>
                    </div>

                    <!-- Oprichtingsdatum -->
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400 flex items-center gap-2">
                            <i class="fa-solid fa-calendar-days text-slate-400 dark:text-slate-500"></i> Opgericht op
                        </span>
                        <span class="font-bold text-slate-700 dark:text-slate-300">
                            {{ $gang->created_at ? $gang->created_at->format('d-m-Y') : 'Onbekend' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Bondgenoten / Allianties Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-handshake text-blue-500"></i>
                        <span>Bondgenoten</span>
                    </h4>
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500">
                        {{ $gang->alliances->count() }} actieve pacten
                    </span>
                </div>

                @if($gang->alliances->isNotEmpty())
                    <div class="space-y-2">
                        @foreach($gang->alliances as $alliance)
                            @if($alliance->allyGang)
                                <a href="{{ route('gangs.show', $alliance->allyGang->id) }}" class="flex items-center justify-between p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-700/70 bg-slate-50/90 dark:bg-slate-800/60 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 transition-all duration-150">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 flex items-center justify-center text-xs font-bold shrink-0">
                                            <i class="fa-solid {{ $alliance->allyGang->emblem ?? 'fa-crown' }}"></i>
                                        </div>
                                        <div>
                                            <div class="text-xs font-extrabold text-slate-900 dark:text-white leading-tight flex items-center gap-1">
                                                <span>{{ $alliance->allyGang->name }}</span>
                                                <span class="text-purple-600 dark:text-purple-400 font-mono text-[10px]">[{{ $alliance->allyGang->tag }}]</span>
                                            </div>
                                            <span class="text-[10px] text-slate-400 dark:text-slate-500">{{ $alliance->allyGang->members->count() }} leden</span>
                                        </div>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-400 dark:text-slate-500"></i>
                                </a>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="py-6 text-center text-xs text-slate-400 dark:text-slate-500">
                        <i class="fa-solid fa-handshake-slash text-xl mb-1.5 opacity-60 block"></i>
                        Deze bende heeft momenteel geen bondgenootschappen gesloten.
                    </div>
                @endif
            </div>

            <!-- Bende Panden & Vastgoed Preview -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-building text-indigo-600 dark:text-indigo-400"></i>
                        <span>Vastgoed & Panden</span>
                    </h4>
                    <span class="text-[10px] font-mono font-bold text-indigo-600 dark:text-indigo-400">
                        {{ $gang->properties->count() }} Panden
                    </span>
                </div>

                @if($gang->properties->isNotEmpty())
                    <div class="space-y-2">
                        @foreach($gang->properties->take(5) as $prop)
                            <div class="p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-700/70 bg-slate-50/90 dark:bg-slate-800/60 flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                    <i class="fa-solid fa-building-wheat text-indigo-500 text-xs"></i>
                                    {{ $prop->name }}
                                </span>
                                <span class="text-[10px] font-mono text-purple-600 dark:text-purple-400 font-extrabold">
                                    Lvl {{ $prop->level }}
                                </span>
                            </div>
                        @endforeach
                        @if($gang->properties->count() > 5)
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 text-center block pt-1">
                                +{{ $gang->properties->count() - 5 }} meer panden in eigendom
                            </span>
                        @endif
                    </div>
                @else
                    <div class="py-4 text-center text-xs text-slate-400 dark:text-slate-500">
                        Geen vastgoed in bezit.
                    </div>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection
