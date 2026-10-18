@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ 
    activeGangTab: '{{ $activeTab ?? session('active_gang_tab', request('tab', ($myGang ? 'mijn_gang' : 'ranglijst'))) }}',
    beheerTab: '{{ session('beheer_tab', request('beheer_tab', 'leden')) }}',
    showCreateModal: false,
    init() {
        if (!'{{ session('active_gang_tab') }}' && !'{{ request('tab') }}' && sessionStorage.getItem('tbs_active_gang_tab')) {
            this.activeGangTab = sessionStorage.getItem('tbs_active_gang_tab');
        }
    },
    setGangTab(tab) {
        this.activeGangTab = tab;
        sessionStorage.setItem('tbs_active_gang_tab', tab);
    },
    setBeheerTab(tab) {
        this.beheerTab = tab;
    }
}">

    <!-- Top Hero Banner (Clean TBS White Style) -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
            <div>
                <div class="inline-flex items-center gap-2 bg-purple-50 text-purple-700 text-xs font-bold px-3 py-1 rounded-full border border-purple-200 mb-2">
                    <i class="fa-solid fa-users"></i> Facties & Allianties
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Kliniek Gangs</h1>
                <p class="text-slate-500 text-xs sm:text-sm mt-1 max-w-xl leading-relaxed">
                    Verenig je met andere gevangenen in een bende. Doneer aan de gezamenlijke kas, verhoog het collectieve uurloon, koop smokkelkiosken en voer oorlog om de vleugels van de kliniek!
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="px-3.5 py-2 rounded-xl bg-amber-50 border border-amber-200 text-xs font-bold text-amber-800 flex items-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-star text-amber-600"></i> {{ $character->stars ?? 0 }} Sterren
                </span>
                @if(!$character->gang_id)
                <button type="button" @click="showCreateModal = true" 
                        class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs uppercase tracking-wider transition shadow-xs cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-plus"></i> Bende Oprichten
                </button>
                @endif
            </div>
        </div>
    </div>

    @php
        $myLeaveMultiplier = $myGang ? min(50, max(0, (int) ($myGang->leave_fee_multiplier ?? 0))) : 0;
        $myLeaveFee = ($myGang && $character->gang_role !== 'leader') ? ($hourlyWage * $myLeaveMultiplier) : 0;
        $hasEnoughCashToLeave = (int) $character->cash >= $myLeaveFee;
    @endphp

    <!-- MAIN TWO COLUMN GANGS LAYOUT -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left Column: Gangs Menu Navigation -->
        <div class="lg:col-span-3 space-y-4">
            <div class="bg-white dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800 rounded-xl overflow-hidden shadow-xs">
                <!-- Gangs Header Tab with Pink + Button -->
                <div class="bg-slate-50 dark:bg-slate-800/80 px-4 py-3 flex items-center justify-between border-b border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 font-extrabold uppercase text-xs tracking-wider">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-users text-purple-600 dark:text-purple-400 text-xs"></i> Gangs Menu
                    </span>
                    @if(! $myGang)
                    <button type="button" @click="showCreateModal = true" title="Nieuwe Bende Oprichten (25 Sterren)" class="w-6 h-6 rounded-lg bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 flex items-center justify-center text-xs font-bold transition cursor-pointer">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                    @endif
                </div>

                <!-- Navigation List -->
                <div class="p-2 space-y-1 text-xs font-bold text-slate-700 dark:text-slate-200">
                    <button type="button" @click="setGangTab('ranglijst')" 
                            :class="activeGangTab === 'ranglijst' ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold border border-transparent shadow-2xs' : 'hover:bg-slate-100/70 dark:hover:bg-slate-800/60 text-slate-600 dark:text-slate-300 border border-transparent'"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition cursor-pointer">
                        <span class="flex items-center gap-2.5">
                            <i class="fa-solid fa-trophy text-amber-500 w-4 text-center"></i>
                            <span>Ranglijst</span>
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i>
                    </button>

                    @if($myGang)
                    <button type="button" @click="setGangTab('mijn_gang')" 
                            :class="activeGangTab === 'mijn_gang' ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold border border-transparent shadow-2xs' : 'hover:bg-slate-100/70 dark:hover:bg-slate-800/60 text-slate-600 dark:text-slate-300 border border-transparent'"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition cursor-pointer">
                        <span class="flex items-center gap-2.5">
                            <i class="fa-solid fa-shield-halved text-purple-600 dark:text-purple-400 w-4 text-center"></i>
                            <span>Mijn Gang</span>
                        </span>
                        <span class="text-[10px] text-purple-700 dark:text-purple-300 font-extrabold">[{{ $myGang->tag }}]</span>
                    </button>

                    <!-- Ledenlijst & Online Tab -->
                    <button type="button" @click="setGangTab('leden')" 
                            :class="activeGangTab === 'leden' ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold border border-transparent shadow-2xs' : 'hover:bg-slate-100/70 dark:hover:bg-slate-800/60 text-slate-600 dark:text-slate-300 border border-transparent'"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition cursor-pointer">
                        <span class="flex items-center gap-2.5">
                            <i class="fa-solid fa-users text-purple-600 dark:text-purple-400 w-4 text-center"></i>
                            <span>Ledenlijst</span>
                        </span>
                        <div class="flex items-center gap-1.5">
                            @if(($onlineMembersCount ?? 0) > 0)
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[9px] font-extrabold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900" title="{{ $onlineMembersCount ?? 0 }} leden nu online">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>{{ $onlineMembersCount ?? 0 }} online</span>
                                </span>
                            @else
                                <span class="text-[10px] text-slate-400 font-bold">{{ $myGang->members->count() }}</span>
                            @endif
                            <i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i>
                        </div>
                    </button>

                    <!-- Bende Oorlog Tab -->
                    <button type="button" @click="setGangTab('oorlog')" 
                            :class="activeGangTab === 'oorlog' ? 'bg-rose-50/90 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 font-bold border border-transparent shadow-2xs' : 'hover:bg-slate-100/70 dark:hover:bg-slate-800/60 text-slate-600 dark:text-slate-300 border border-transparent'"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition cursor-pointer">
                        <span class="flex items-center gap-2.5">
                            <i class="fa-solid fa-crosshairs text-rose-600 dark:text-rose-400 w-4 text-center"></i>
                            <span class="font-black">Bende Oorlog</span>
                        </span>
                        @if($myGang->attackCooldownRemaining() > 0)
                            <span class="text-[10px] font-mono text-amber-600 dark:text-amber-400 font-extrabold flex items-center gap-1">
                                <i class="fa-solid fa-stopwatch text-amber-500 text-[10px]"></i> {{ gmdate('i:s', $myGang->attackCooldownRemaining()) }}
                            </span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                        @endif
                    </button>

                    <!-- Bende Panden & Vastgoed -->
                    <a href="{{ route('gangs.panden') }}" 
                       class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition hover:bg-slate-100/70 dark:hover:bg-slate-800/60 text-slate-600 dark:text-slate-300 border border-transparent">
                        <span class="flex items-center gap-2.5">
                            <i class="fa-solid fa-building text-purple-600 dark:text-purple-400 w-4 text-center"></i>
                            <span class="font-extrabold text-purple-700 dark:text-purple-300">Bende Panden</span>
                        </span>
                        <span class="text-[10px] font-black px-1.5 py-0.5 rounded-md bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-mono">
                            {{ $myGang->properties->count() }}
                        </span>
                    </a>

                    @if($myGang && $myGang->isOfficer($character))
                    <button type="button" @click="setGangTab('beheer')" 
                            :class="activeGangTab === 'beheer' ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold border border-transparent shadow-2xs' : 'hover:bg-slate-100/70 dark:hover:bg-slate-800/60 text-slate-600 dark:text-slate-300 border border-transparent'"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition cursor-pointer">
                        <span class="flex items-center gap-2.5">
                            <i class="fa-solid fa-users-gear text-purple-600 dark:text-purple-400 w-4 text-center"></i>
                            <span>Bende Beheer</span>
                        </span>
                        @if($myGang->requests->where('status', 'pending')->count() > 0)
                            <span class="px-1.5 py-0.2 rounded-full bg-purple-600 text-white text-[9px] font-black">
                                {{ $myGang->requests->where('status', 'pending')->count() }}
                            </span>
                        @endif
                    </button>

                    <button type="button" @click="setGangTab('instellingen')" 
                            :class="activeGangTab === 'instellingen' ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold border border-transparent shadow-2xs' : 'hover:bg-slate-100/70 dark:hover:bg-slate-800/60 text-slate-600 dark:text-slate-300 border border-transparent'"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition cursor-pointer">
                        <span class="flex items-center gap-2.5">
                            <i class="fa-solid fa-sliders text-slate-500 dark:text-slate-400 w-4 text-center"></i>
                            <span>Instellingen</span>
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i>
                    </button>
                    @endif

                    <!-- Winkel Tab -->
                    <button type="button" @click="setGangTab('winkel')" 
                            :class="activeGangTab === 'winkel' ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold border border-transparent shadow-2xs' : 'hover:bg-slate-100/70 dark:hover:bg-slate-800/60 text-slate-600 dark:text-slate-300 border border-transparent'"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition cursor-pointer">
                        <span class="flex items-center gap-2.5">
                            <i class="fa-solid fa-store text-purple-600 dark:text-purple-400 w-4 text-center"></i>
                            <span>Winkel & Kiosken</span>
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i>
                    </button>

                    <!-- Doneren Tab -->
                    <button type="button" @click="setGangTab('doneren')" 
                            :class="activeGangTab === 'doneren' ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold border border-transparent shadow-2xs' : 'hover:bg-slate-100/70 dark:hover:bg-slate-800/60 text-slate-600 dark:text-slate-300 border border-transparent'"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition cursor-pointer">
                        <span class="flex items-center gap-2.5">
                            <i class="fa-solid fa-circle-plus text-emerald-600 dark:text-emerald-400 w-4 text-center"></i>
                            <span>Doneren</span>
                        </span>
                        <i class="fa-solid fa-plus text-[10px] opacity-60"></i>
                    </button>

                    <button type="button" @click="setGangTab('logboek')" 
                            :class="activeGangTab === 'logboek' ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold border border-transparent shadow-2xs' : 'hover:bg-slate-100/70 dark:hover:bg-slate-800/60 text-slate-600 dark:text-slate-300 border border-transparent'"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition cursor-pointer">
                        <span class="flex items-center gap-2.5">
                            <i class="fa-solid fa-book-bookmark text-blue-500 w-4 text-center"></i>
                            <span>Logboek</span>
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i>
                    </button>

                    <a href="{{ route('murder_stats.index') }}" 
                       class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-slate-100/70 dark:hover:bg-slate-800/60 text-slate-600 dark:text-slate-300 transition border border-transparent">
                        <span class="flex items-center gap-2.5">
                            <i class="fa-solid fa-skull text-rose-500 w-4 text-center"></i>
                            <span>Moorden</span>
                        </span>
                    </a>

                    <a href="{{ route('leaderboard.index') }}" 
                       class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-slate-100/70 dark:hover:bg-slate-800/60 text-slate-600 dark:text-slate-300 transition border border-transparent">
                        <span class="flex items-center gap-2.5">
                            <i class="fa-solid fa-chart-column text-indigo-500 w-4 text-center"></i>
                            <span>Algemene stats</span>
                        </span>
                    </a>

                    <!-- Bende Verlaten Knop in Menu -->
                    <div class="pt-2 mt-1 border-t border-slate-100 dark:border-slate-800">
                        <form action="{{ route('gangs.leave') }}" method="POST"
                              onsubmit="return confirm('{{ $character->gang_role === 'leader' ? ($myGang->members->count() > 1 ? 'Weet je zeker dat je de bende wilt verlaten? Het leiderschap wordt overgedragen aan een ander bendelid.' : 'Weet je zeker dat je de bende wilt verlaten? Omdat je het enige lid bent, wordt de bende definitief opgeheven!') : ($myLeaveFee > 0 ? 'Weet je zeker dat je ' . addslashes($myGang->name) . ' wilt verlaten? De vertrekboete bedraagt €' . number_format($myLeaveFee, 0, ',', '.') . ' (' . $myLeaveMultiplier . 'x uurloon) en wordt overgemaakt naar de bende kluis.' : 'Weet je zeker dat je ' . addslashes($myGang->name) . ' wilt verlaten?') }}');">
                            @csrf
                            <button type="submit" 
                                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 hover:text-rose-700 transition cursor-pointer font-bold text-xs group">
                                <span class="flex items-center gap-2.5">
                                    <i class="fa-solid fa-arrow-right-from-bracket text-rose-500 w-4 text-center group-hover:translate-x-0.5 transition"></i>
                                    <span>Bende Verlaten</span>
                                </span>
                                @if($character->gang_role !== 'leader' && $myLeaveFee > 0)
                                    <span class="text-[10px] font-mono text-rose-500 font-extrabold bg-rose-50 dark:bg-rose-950/60 px-1.5 py-0.5 rounded border border-rose-200 dark:border-rose-900">€{{ number_format($myLeaveFee, 0, ',', '.') }}</span>
                                @endif
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Create gang shortcut card -->
            @if(!$character->gang_id)
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs space-y-3">
                <span class="text-xs font-extrabold text-slate-900 uppercase tracking-wider block">Bende Oprichten</span>
                <p class="text-xs text-slate-500 leading-relaxed font-medium">
                    Richt je eigen factie op en heers over de oostvleugel van de TBS kliniek.
                </p>
                <div class="flex items-center justify-between pt-1">
                    <span class="text-xs font-bold text-amber-600 flex items-center gap-1">25 Sterren <i class="fa-solid fa-star text-amber-500 text-xs"></i></span>
                    <button type="button" @click="showCreateModal = true" class="px-3.5 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs transition cursor-pointer">
                        Oprichten
                    </button>
                </div>
            </div>

            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-5 shadow-xs space-y-2">
                <span class="text-xs font-extrabold text-emerald-900 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-hand-holding-dollar text-emerald-600"></i> TBS Patiënten Uitkering
                </span>
                <p class="text-xs text-emerald-800 leading-relaxed font-medium">
                    Zolang je geen bende hebt, ontvang je automatisch elk kwartier (15 min) <strong class="font-bold">€1.000</strong> direct op je <a href="{{ route('bank.index') }}" class="underline font-bold">bank</a>!
                </p>
            </div>
            @endif
        </div>

        <!-- Right Content Area: Tabs -->
        <div class="lg:col-span-9 space-y-6">

            <!-- TAB 1: MIJN GANG -->
            @if($myGang)
            <div x-show="activeGangTab === 'mijn_gang'" class="space-y-6">
                <!-- Gang Profile Top Banner -->
                <div class="rounded-2xl overflow-hidden border border-slate-200 shadow-xs bg-white">
                    <div class="h-40 sm:h-48 w-full overflow-hidden relative bg-purple-950">
                        <img src="{{ $myGang->banner_url }}" alt="Gang Header" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent pointer-events-none"></div>
                        <button type="button" 
                                @click="window.tbsOpenReportModal({ type: 'gang', id: {{ $myGang->id }}, name: '{{ addslashes($myGang->name) }}' })"
                                class="absolute top-4 right-4 px-3 py-1 rounded-lg bg-black/50 hover:bg-rose-900/80 text-white text-xs font-bold border border-white/20 shadow-xs flex items-center gap-1.5 backdrop-blur-xs transition cursor-pointer"
                                title="Rapporteer deze bende bij moderatie">
                            <i class="fa-solid fa-flag text-rose-400"></i> Rapporteer Bende
                        </button>
                    </div>

                    <!-- Gang Emblem and Tag Badge -->
                    <div class="px-6 pb-6 pt-3 relative z-10 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                        <div class="flex items-center gap-4 -mt-12 sm:-mt-14">
                            <div class="w-20 h-20 rounded-2xl bg-purple-600 border-4 border-white shadow-lg flex items-center justify-center text-3xl text-white shrink-0 overflow-hidden">
                                @if($myGang->avatar)
                                    <img src="{{ $myGang->avatar_url }}" alt="{{ $myGang->name }}" class="w-full h-full object-cover">
                                @else
                                    <i class="fa-solid {{ $myGang->emblem ?? 'fa-crown' }}"></i>
                                @endif
                            </div>
                            <div class="pt-8 sm:pt-0">
                                <h2 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                                    <span>{{ $myGang->name }}</span>
                                    <span class="text-purple-700 text-xs font-mono font-bold bg-purple-50 px-2 py-0.5 rounded border border-purple-200">[{{ $myGang->tag }}]</span>
                                    <i class="fa-solid fa-circle-check text-purple-600 text-sm"></i>
                                </h2>
                                <span class="text-xs font-black text-slate-950 flex items-center gap-1.5 mt-0.5">
                                    <i class="fa-solid fa-users text-xs text-purple-600"></i> {{ $myGang->members->count() }} / {{ $myGang->max_members ?? 10 }} leden
                                </span>
                            </div>
                        </div>

                        <!-- Top Quick Leave Button -->
                        <div class="flex items-center gap-2">
                            <form action="{{ route('gangs.leave') }}" method="POST"
                                  onsubmit="return confirm('{{ $character->gang_role === 'leader' ? ($myGang->members->count() > 1 ? 'Weet je zeker dat je de bende wilt verlaten? Het leiderschap wordt overgedragen aan een ander bendelid.' : 'Weet je zeker dat je de bende wilt verlaten? Omdat je het enige lid bent, wordt de bende definitief opgeheven!') : ($myLeaveFee > 0 ? 'Weet je zeker dat je ' . addslashes($myGang->name) . ' wilt verlaten? De vertrekboete bedraagt €' . number_format($myLeaveFee, 0, ',', '.') . ' (' . $myLeaveMultiplier . 'x uurloon) en wordt overgemaakt naar de bende kluis.' : 'Weet je zeker dat je ' . addslashes($myGang->name) . ' wilt verlaten?') }}');">
                                @csrf
                                <button type="submit" 
                                        class="px-3 py-1.5 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs transition cursor-pointer flex items-center gap-1.5 shadow-2xs">
                                    <i class="fa-solid fa-arrow-right-from-bracket text-rose-500"></i>
                                    <span>Bende Verlaten</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Gang Details Grid -->
                <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-3 gap-x-8 text-xs font-medium border-b border-slate-100 pb-6">
                        <div class="flex items-center justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500 flex items-center gap-2">
                                <i class="fa-solid fa-crown text-amber-500"></i> Bende Leider
                            </span>
                            <a href="{{ route('profile.show', $myGang->leader?->name ?? 'onbekend') }}" class="font-bold text-purple-700 hover:underline flex items-center gap-1">
                                {{ $myGang->leader?->name ?? 'Onbekend' }}
                                <i class="fa-solid fa-circle-check text-purple-600 text-[10px]"></i>
                            </a>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500 flex items-center gap-2">
                                <i class="fa-solid fa-box-open text-purple-600"></i> Kantine Smokkelaar
                            </span>
                            <span class="text-slate-700 font-bold">{{ $myGang->personalShopper?->name ?? 'Nog niet aangewezen' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500 flex items-center gap-2">
                                <i class="fa-solid fa-vault text-emerald-600"></i> Kasbeheerder
                            </span>
                            <span class="text-slate-700 font-bold">{{ $myGang->moneyMagnet?->name ?? 'Nog niet aangewezen' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500 flex items-center gap-2">
                                <i class="fa-solid fa-euro-sign text-emerald-600"></i> Bende Kas (Cash)
                            </span>
                            <span class="font-extrabold text-emerald-700 font-mono">€{{ number_format($myGang->treasury, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                            <div>
                                <span class="text-slate-500 flex items-center gap-2">
                                    <i class="fa-solid fa-money-bill-wave text-amber-500"></i> Uurloon / Dagloon
                                </span>
                                <span class="text-[9px] text-emerald-600 font-bold flex items-center gap-1 mt-0.5">
                                    <i class="fa-solid fa-circle-check"></i> Automatisch elk uur op je bank
                                </span>
                            </div>
                            <div class="text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <span class="font-extrabold text-amber-700">UURLOON €{{ number_format($hourlyWage, 0, ',', '.') }}</span>
                                    <span class="inline-flex items-center gap-1 text-[9px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.2 rounded-full" title="Wordt automatisch elk uur gestort op je bank">
                                        <i class="fa-solid fa-circle text-[5px] text-emerald-500 animate-pulse"></i> Auto
                                    </span>
                                </div>
                                <span class="text-[10px] text-slate-400 block font-bold">DAGLOON €{{ number_format($dailyWage, 0, ',', '.') }}</span>
                                @if(isset($wageDetails))
                                    <span class="text-[9px] text-purple-600 font-semibold block">
                                        Volgende uitbetaling over: {{ $wageDetails['next_payout_human'] }}
                                    </span>
                                    @if($wageDetails['pending_hours'] > 0)
                                        <form action="{{ route('gangs.claim-wage') }}" method="POST" class="mt-1">
                                            @csrf
                                            <button type="submit" class="text-[10px] font-extrabold px-2 py-0.5 rounded bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer transition shadow-2xs">
                                                Claim Nu (€{{ number_format($wageDetails['pending_amount'], 0, ',', '.') }})
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500 flex items-center gap-2">
                                <i class="fa-solid fa-skull text-rose-500"></i> Moorden vandaag
                            </span>
                            <span class="text-slate-700 font-bold">Van ons: 0 &bull; Op ons: 0</span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500 flex items-center gap-2">
                                <i class="fa-solid fa-fire text-purple-600"></i> Bende Respect
                            </span>
                            <span class="font-extrabold text-purple-700">{{ number_format($myGang->members->sum('respect'), 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500 flex items-center gap-2">
                                <i class="fa-solid fa-handshake text-blue-500"></i> Bondgenoten
                            </span>
                            <span class="text-slate-700 font-bold">
                                {{ $myGang->alliances->isEmpty() ? 'Geen' : $myGang->alliances->map(fn($a) => $a->allyGang->name)->join(', ') }}
                            </span>
                        </div>
                    </div>

                    <!-- 5 Stat Cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-center space-y-1">
                            <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block">SMOKKELKIOSKEN</span>
                            <div class="flex items-center justify-center gap-1.5 text-purple-700 text-lg font-extrabold font-mono">
                                <i class="fa-solid fa-store text-xs"></i>
                                <span>{{ $myGang->bling_shops ?? 0 }}</span>
                            </div>
                        </div>

                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-center space-y-1">
                            <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block">AANVALSPUNTEN</span>
                            <div class="flex items-center justify-center gap-1.5 text-amber-600 text-lg font-extrabold font-mono">
                                <i class="fa-solid fa-bolt text-xs"></i>
                                <span>{{ number_format(($myGang->attack_power ?? 500) / 10, 2, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-center space-y-1">
                            <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block">VERDEDIGING</span>
                            <div class="flex items-center justify-center gap-1.5 text-indigo-700 text-lg font-extrabold font-mono">
                                <i class="fa-solid fa-shield text-xs"></i>
                                <span>{{ number_format($myGang->protection_points ?? 1000, 2, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-center space-y-1">
                            <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block">POWER</span>
                            <div class="flex items-center justify-center gap-1.5 text-purple-700 text-lg font-extrabold font-mono">
                                <i class="fa-solid fa-bolt text-xs"></i>
                                <span>{{ number_format($myGang->attack_power ?? 500, 2, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-center space-y-1">
                            <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block">BESCHERMING</span>
                            <div class="flex items-center justify-center gap-1.5 text-emerald-700 text-lg font-extrabold font-mono">
                                <i class="fa-solid fa-shield text-xs"></i>
                                <span>{{ number_format(($myGang->protection_points ?? 1000) * 1.5, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Bio -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs text-slate-600 leading-relaxed">
                        {{ $myGang->description ?: 'Dit profiel is nog leeg, medepatiënt.' }}
                    </div>

                    <!-- ALLE LEDEN Table with Realtime Search & Online Filter -->
                    <div class="space-y-3 pt-2"
                         x-data="{
                             memberSearch: '',
                             onlyOnline: false,
                             members: {{ Js::from($gangMembersData ?? []) }},
                             get list() {
                                 let q = this.memberSearch.toLowerCase().trim();
                                 return this.members.filter(m => {
                                     if (q && !m.name.toLowerCase().includes(q) && !m.role_label.toLowerCase().includes(q)) return false;
                                     if (this.onlyOnline && !m.is_online) return false;
                                     return true;
                                 });
                             }
                         }">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-2">
                            <div class="flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-slate-900">
                                <i class="fa-solid fa-users text-purple-600"></i>
                                <span>ALLE LEDEN</span>
                                <span class="text-slate-400 font-normal">({{ $myGang->members->count() }})</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button" 
                                        @click="onlyOnline = !onlyOnline" 
                                        :class="onlyOnline ? 'bg-emerald-600 text-white font-extrabold shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                        class="px-2.5 py-1 rounded-lg text-xs font-bold transition cursor-pointer flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full" :class="onlyOnline ? 'bg-white' : 'bg-emerald-500 animate-pulse'"></span>
                                    <span>Online ({{ $onlineMembersCount ?? 0 }})</span>
                                </button>
                                <div class="relative w-44">
                                    <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                                    <input type="text" 
                                           x-model="memberSearch" 
                                           placeholder="Zoek lid..." 
                                           class="w-full bg-slate-50 border border-slate-200 text-slate-900 rounded-lg pl-7 pr-6 py-1 text-xs focus:outline-none focus:border-purple-600 transition">
                                    <button type="button" x-show="memberSearch" @click="memberSearch = ''" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-[10px]">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2">
                                        <th class="py-2.5 px-3">PATIËNT</th>
                                        <th class="py-2.5 px-3">ROL</th>
                                        <th class="py-2.5 px-3 text-right">LEVEL</th>
                                        <th class="py-2.5 px-3 text-right">RESPECT</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    <template x-for="member in list" :key="member.id">
                                        <tr class="hover:bg-slate-50 transition">
                                            <td class="py-3 px-3">
                                                <div class="flex items-center gap-3">
                                                    <a :href="member.profile_url" class="relative shrink-0">
                                                        <img :src="member.avatar_url" :alt="member.name" class="w-8 h-8 rounded-full object-cover border border-slate-200">
                                                        <span class="absolute -bottom-0.5 -right-0.5 w-2 h-2 rounded-full border border-white"
                                                              :class="member.is_online ? 'bg-emerald-500' : 'bg-slate-300'"
                                                              :title="member.online_label">
                                                        </span>
                                                    </a>
                                                    <a :href="member.profile_url" class="font-bold text-slate-900 hover:text-purple-600 transition" x-text="member.name"></a>
                                                </div>
                                            </td>
                                            <td class="py-3 px-3">
                                                <span class="text-xs font-bold"
                                                      :class="{
                                                          'text-amber-600': member.gang_role === 'leader',
                                                          'text-purple-700': member.gang_role === 'co_leader',
                                                          'text-slate-500': member.gang_role !== 'leader' && member.gang_role !== 'co_leader'
                                                      }"
                                                      x-text="member.role_label">
                                                </span>
                                            </td>
                                            <td class="py-3 px-3 text-right font-bold text-slate-700" x-text="member.level"></td>
                                            <td class="py-3 px-3 text-right font-extrabold text-purple-700 font-mono" x-text="member.respect_formatted"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                            <div x-show="list.length === 0" class="py-6 text-center text-xs text-slate-400">
                                Geen leden gevonden die voldoen aan je zoekopdracht.
                            </div>
                        </div>
                    </div>

                    <!-- Bende Verlaten Box -->
                    <div class="bg-rose-50/50 border border-rose-200 rounded-xl p-5 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <h4 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                                    <i class="fa-solid fa-arrow-right-from-bracket text-rose-600"></i>
                                    <span>Bende Verlaten</span>
                                </h4>
                                @if($character->gang_role === 'leader')
                                    <p class="text-xs text-slate-600">
                                        Als leider kun je de bende verlaten. Het leiderschap wordt dan overgedragen aan het volgende bendelid, of de factie wordt opgeheven als je het enige lid bent.
                                    </p>
                                @else
                                    <p class="text-xs text-slate-600 leading-relaxed">
                                        @if($myLeaveFee > 0)
                                            De vertrekboete (verlatingskosten) bedraagt <strong class="text-rose-700 font-bold font-mono">€{{ number_format($myLeaveFee, 0, ',', '.') }}</strong> ({{ $myLeaveMultiplier }}x het uurloon van €{{ number_format($hourlyWage, 0, ',', '.') }}). Deze boete betaal je aan de gezamenlijke kluis wanneer je vertrekt.
                                        @else
                                            Er is momenteel geen vertrekboete ingesteld door de leiding. Je kunt direct en kosteloos vertrekken.
                                        @endif
                                    </p>
                                    @if($myLeaveFee > 0)
                                        <div class="flex flex-wrap items-center gap-3 text-xs pt-1">
                                            <span class="text-slate-500">Contant op zak: <strong class="font-mono {{ $hasEnoughCashToLeave ? 'text-emerald-700 font-bold' : 'text-rose-600 font-extrabold' }}">€{{ number_format($character->cash, 0, ',', '.') }}</strong></span>
                                            @if(! $hasEnoughCashToLeave)
                                                <span class="text-rose-600 font-bold flex items-center gap-1">
                                                    <i class="fa-solid fa-circle-exclamation text-rose-500"></i> Te weinig cash op zak! Neem minimaal €{{ number_format($myLeaveFee - $character->cash, 0, ',', '.') }} op van je <a href="{{ route('bank.index') }}" class="underline font-bold text-purple-700">bank</a>.
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                @endif
                            </div>

                            <form action="{{ route('gangs.leave') }}" method="POST"
                                  onsubmit="return confirm('{{ $character->gang_role === 'leader' ? ($myGang->members->count() > 1 ? 'Weet je zeker dat je de bende wilt verlaten? Het leiderschap wordt overgedragen aan een ander bendelid.' : 'Weet je zeker dat je de bende wilt verlaten? Omdat je het enige lid bent, wordt de bende definitief opgeheven!') : ($myLeaveFee > 0 ? 'Weet je zeker dat je ' . addslashes($myGang->name) . ' wilt verlaten? Er wordt een vertrekboete van €' . number_format($myLeaveFee, 0, ',', '.') . ' overgemaakt naar de bende kluis.' : 'Weet je zeker dat je ' . addslashes($myGang->name) . ' wilt verlaten?') }}');">
                                @csrf
                                <button type="submit" 
                                        class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs shadow-xs transition cursor-pointer flex items-center gap-2 whitespace-nowrap">
                                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                    <span>Bende Verlaten</span>
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
            @endif

            <!-- TAB: DEDICATED LEDENLIJST & ONLINE (REALTIME ZOEKBALK) -->
            @if($myGang)
            <div x-show="activeGangTab === 'leden'" 
                 x-data="{
                     search: '',
                     statusFilter: 'all', // 'all', 'online', 'offline'
                     sortBy: 'respect_desc', // 'respect_desc', 'level_desc', 'name_asc', 'online_first'
                     members: {{ Js::from($gangMembersData ?? []) }},
                     get filteredMembers() {
                         let q = this.search.toLowerCase().trim();
                         return this.members.filter(m => {
                             if (q && !m.name.toLowerCase().includes(q) && !m.role_label.toLowerCase().includes(q)) {
                                 return false;
                             }
                             if (this.statusFilter === 'online' && !m.is_online) return false;
                             if (this.statusFilter === 'offline' && m.is_online) return false;
                             return true;
                         }).sort((a, b) => {
                             if (this.sortBy === 'respect_desc') return b.respect - a.respect;
                             if (this.sortBy === 'level_desc') return b.level - a.level;
                             if (this.sortBy === 'name_asc') return a.name.localeCompare(b.name);
                             if (this.sortBy === 'online_first') {
                                 if (a.is_online && !b.is_online) return -1;
                                 if (!a.is_online && b.is_online) return 1;
                                 return b.respect - a.respect;
                             }
                             return 0;
                         });
                     },
                     get onlineCount() {
                         return this.members.filter(m => m.is_online).length;
                     },
                     get offlineCount() {
                         return this.members.filter(m => !m.is_online).length;
                     }
                 }" 
                 class="space-y-6">

                <!-- Header Card -->
                <div class="bg-white dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                    {{ $myGang->name }} [{{ $myGang->tag }}]
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span x-text="onlineCount + ' Online'"></span>
                                </span>
                            </div>
                            <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2">
                                <i class="fa-solid fa-users text-purple-600 dark:text-purple-400 text-lg"></i>
                                <span>Bende Ledenlijst</span>
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                Realtime overzicht van alle bendelieden, live activiteitsstatus en direct contact.
                            </p>
                        </div>

                        <!-- Stats Pills -->
                        <div class="flex items-center gap-2 shrink-0">
                            <div class="px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-center">
                                <span class="text-[10px] font-bold text-slate-400 block uppercase">Totaal</span>
                                <span class="text-sm font-black text-slate-900 dark:text-slate-100 font-mono">{{ $myGang->members->count() }} / {{ $myGang->max_members ?? 10 }}</span>
                            </div>
                            <div class="px-3.5 py-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-center">
                                <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 block uppercase flex items-center justify-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Online
                                </span>
                                <span class="text-sm font-black text-emerald-700 dark:text-emerald-300 font-mono" x-text="onlineCount"></span>
                            </div>
                            <div class="px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-center">
                                <span class="text-[10px] font-bold text-slate-400 block uppercase">Offline</span>
                                <span class="text-sm font-black text-slate-600 dark:text-slate-400 font-mono" x-text="offlineCount"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Search & Filter Controls -->
                    <div class="space-y-3 pt-1">
                        <!-- Realtime Search Bar -->
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input type="text" 
                                   x-model="search"
                                   placeholder="Zoek bendelid op naam of rol (realtime)..." 
                                   class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl pl-9 pr-9 py-2.5 text-xs font-semibold focus:outline-none focus:border-purple-600 focus:bg-white dark:focus:bg-slate-900 transition">
                            <button type="button" 
                                    x-show="search.length > 0" 
                                    @click="search = ''" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs cursor-pointer">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <!-- Filter Buttons & Sorter -->
                        <div class="flex flex-wrap items-center justify-between gap-2.5 pt-1">
                            <!-- Status Filters (Alle / Online / Offline) -->
                            <div class="flex items-center gap-1.5">
                                <button type="button" 
                                        @click="statusFilter = 'all'" 
                                        :class="statusFilter === 'all' ? 'bg-purple-600 text-white font-extrabold shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200'"
                                        class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer flex items-center gap-1.5">
                                    <span>Alle Leden</span>
                                    <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="statusFilter === 'all' ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300'" x-text="members.length"></span>
                                </button>
                                <button type="button" 
                                        @click="statusFilter = 'online'" 
                                        :class="statusFilter === 'online' ? 'bg-emerald-600 text-white font-extrabold shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200'"
                                        class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full" :class="statusFilter === 'online' ? 'bg-white' : 'bg-emerald-500 animate-pulse'"></span>
                                    <span>Nu Online</span>
                                    <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="statusFilter === 'online' ? 'bg-white/20 text-white' : 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300'" x-text="onlineCount"></span>
                                </button>
                                <button type="button" 
                                        @click="statusFilter = 'offline'" 
                                        :class="statusFilter === 'offline' ? 'bg-slate-700 text-white font-extrabold shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200'"
                                        class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer flex items-center gap-1.5">
                                    <span>Offline</span>
                                    <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="statusFilter === 'offline' ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300'" x-text="offlineCount"></span>
                                </button>
                            </div>

                            <!-- Sorting Dropdown -->
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] font-bold text-slate-400 hidden sm:inline">Sorteren:</span>
                                <select x-model="sortBy" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-bold focus:outline-none focus:border-purple-600 transition cursor-pointer">
                                    <option value="respect_desc">Hoogste Respect</option>
                                    <option value="level_desc">Hoogste Level</option>
                                    <option value="online_first">Online Leden Eerst</option>
                                    <option value="name_asc">Naam (A-Z)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leden Grid / Cards View -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                    <template x-for="m in filteredMembers" :key="m.id">
                        <div class="bg-white dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-xs hover:border-purple-300 dark:hover:border-purple-800 transition flex items-center justify-between gap-3 group">
                            <!-- Left: Avatar & Info -->
                            <div class="flex items-center gap-3.5 min-w-0">
                                <a :href="m.profile_url" class="relative shrink-0">
                                    <img :src="m.avatar_url" :alt="m.name" class="w-11 h-11 rounded-2xl object-cover border border-slate-200 dark:border-slate-700">
                                    <!-- Online Indicator Ring/Dot -->
                                    <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full border-2 border-white dark:border-[#111827]"
                                          :class="m.is_online ? 'bg-emerald-500 ring-2 ring-emerald-300/40' : 'bg-slate-300 dark:bg-slate-600'"
                                          :title="m.online_label">
                                    </span>
                                </a>

                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <a :href="m.profile_url" class="font-extrabold text-sm text-slate-900 dark:text-slate-100 hover:text-purple-600 dark:hover:text-purple-400 transition truncate" x-text="m.name"></a>
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider shrink-0"
                                              :class="{
                                                  'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800': m.gang_role === 'leader',
                                                  'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800': m.gang_role === 'co_leader',
                                                  'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700': m.gang_role !== 'leader' && m.gang_role !== 'co_leader'
                                              }"
                                              x-text="m.role_label">
                                        </span>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs text-slate-500 dark:text-slate-400 mt-1">
                                        <span>Level <strong class="text-slate-700 dark:text-slate-300" x-text="m.level"></strong></span>
                                        <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                                        <span>Respect <strong class="text-purple-700 dark:text-purple-400 font-mono" x-text="m.respect_formatted"></strong></span>
                                    </div>

                                    <!-- Status line -->
                                    <div class="mt-1 flex items-center gap-1.5 text-[11px]">
                                        <template x-if="m.is_online">
                                            <span class="text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                <span>Nu Online</span>
                                            </span>
                                        </template>
                                        <template x-if="!m.is_online">
                                            <span class="text-slate-400" x-text="m.online_label"></span>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Actions -->
                            <div class="flex items-center gap-1.5 shrink-0">
                                <a :href="m.message_url" title="Stuur privébericht" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-purple-100 hover:text-purple-700 dark:bg-slate-800 dark:hover:bg-purple-950/60 dark:text-slate-300 dark:hover:text-purple-300 flex items-center justify-center text-xs transition cursor-pointer">
                                    <i class="fa-solid fa-envelope"></i>
                                </a>
                                <a :href="m.profile_url" title="Bekijk profiel" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center text-xs transition cursor-pointer">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Empty State -->
                <div x-show="filteredMembers.length === 0" class="bg-white dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800 rounded-2xl p-8 text-center space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center mx-auto text-xl">
                        <i class="fa-solid fa-user-slash"></i>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">Geen bendeleden gevonden</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Er zijn geen bendelieden die voldoen aan je huidige zoekopdracht of filter.
                        </p>
                    </div>
                    <button type="button" @click="search = ''; statusFilter = 'all';" class="px-3.5 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs transition cursor-pointer">
                        Filters wissen
                    </button>
                </div>
            </div>
            @endif

            <!-- TAB 2: WINKEL -->
            @php
                $gangTreasury = $myGang ? (int) ($myGang->treasury ?? 0) : 0;
                $gangStars = $myGang ? (int) ($myGang->gang_stars ?? 0) : 0;
                $personalCash = (int) ($character->cash ?? 0);
                $personalStars = (int) ($character->stars ?? 0);

                // Shop items can ONLY be bought with money from the gangpot (gang treasury)
                $possibleBlingShops = (int) floor($gangTreasury / 50000);
                $possibleProtection = (int) floor($gangTreasury / 15000);
                $possiblePower = (int) floor($gangTreasury / 15000);

                $currentMembersLimit = $myGang ? ($myGang->max_members ?? 10) : 10;
                $remainingCapacity = max(0, 35 - $currentMembersLimit);
                $spendableStars = max($gangStars, $personalStars);
                $possibleMaxMembers = min($remainingCapacity, (int) floor($spendableStars / 25));
            @endphp
            <div x-show="activeGangTab === 'winkel'" class="space-y-6">
                <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-6">
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-900 uppercase tracking-wide">Gang Winkel</h2>
                        <p class="text-xs text-slate-500 mt-1">
                            Verhoog het uurloon met Smokkelkiosken, koop bescherming tegen aanvallen, en haal power om andere gangs te verslaan!
                        </p>
                    </div>

                    <!-- Notice: Gangpot Only -->
                    <div class="p-4 rounded-xl bg-purple-50 border border-purple-200 text-purple-900 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                        <div class="flex items-start sm:items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-purple-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                                <i class="fa-solid fa-vault text-base"></i>
                            </div>
                            <div class="space-y-0.5">
                                <p class="font-black text-purple-950 text-xs sm:text-sm">
                                    Aankopen gaan uitsluitend via de Bende Kas (Gangpot)
                                </p>
                                <p class="text-[11px] text-purple-800 leading-snug">
                                    Persoonlijk zakgeld kan hier niet direct gebruikt worden. Doneer eerst geld aan de kas om ermee te kunnen winkelen!
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="setGangTab('doneren')" class="px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs uppercase shrink-0 transition cursor-pointer flex items-center justify-center gap-1.5 shadow-xs">
                            <i class="fa-solid fa-circle-plus text-xs"></i>
                            <span>Nu Doneren</span>
                        </button>
                    </div>

                    <!-- Balances Header -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-center">
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                            <div class="text-base sm:text-xl font-extrabold text-emerald-700 font-mono">
                                €{{ number_format($gangTreasury, 0, ',', '.') }}
                            </div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase flex items-center justify-center gap-1">
                                <i class="fa-solid fa-vault text-emerald-600 text-[10px]"></i> Bende Kas (Gangpot)
                            </span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                            <div class="text-base sm:text-xl font-extrabold text-slate-700 font-mono">
                                €{{ number_format($personalCash, 0, ',', '.') }}
                            </div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase flex items-center justify-center gap-1">
                                <i class="fa-solid fa-wallet text-slate-400 text-[10px]"></i> Jouw Contant (Niet gedoneerd)
                            </span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                            <div class="text-base sm:text-xl font-extrabold text-amber-600 font-mono">
                                {{ $gangStars }} <span class="text-xs text-slate-400 font-normal">/ {{ $personalStars }} eigen</span>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase flex items-center justify-center gap-1">
                                <i class="fa-solid fa-star text-amber-500 text-[10px]"></i> Sterren (Kas / Eigen)
                            </span>
                        </div>
                    </div>

                    <!-- Shop Items List -->
                    <div class="space-y-4">
                        
                        <!-- Item 1: Smokkelkiosken (Kantine Kiosk) -->
                        <div x-data="{ qty: {{ $possibleBlingShops }} }" class="bg-slate-50 border border-slate-200 rounded-xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                                    <i class="fa-solid fa-store text-purple-600"></i>
                                    <span>Smokkelkiosken (Kantine Kiosk)</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Verhoogt het uurloon met +€100/uur per kiosk. Prijs: <strong class="text-slate-900">€50.000</strong>
                                    <span class="text-slate-400 mx-1">&bull;</span>
                                    Uit Gangpot: <strong class="text-purple-700 font-extrabold">{{ number_format($possibleBlingShops, 0, ',', '.') }}x</strong>
                                </p>
                            </div>
                            <form action="{{ route('gangs.shop-buy') }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                <input type="hidden" name="item_type" value="bling_shops">
                                <input type="number" 
                                       name="quantity" 
                                       x-model.number="qty"
                                       min="{{ $possibleBlingShops > 0 ? 1 : 0 }}" 
                                       max="{{ max(1, $possibleBlingShops) }}" 
                                       value="{{ $possibleBlingShops }}" 
                                       class="w-20 bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 text-center focus:outline-none focus:border-purple-600">
                                <button type="submit" 
                                        :disabled="qty < 1 || qty > {{ max(0, $possibleBlingShops) }}"
                                        @if($possibleBlingShops <= 0) disabled @endif
                                        class="px-5 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-xs uppercase cursor-pointer transition shadow-xs">
                                    Koop
                                </button>
                            </form>
                        </div>

                        <!-- Item 2: Bescherming -->
                        <div x-data="{ qty: {{ $possibleProtection }} }" class="bg-slate-50 border border-slate-200 rounded-xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                                    <i class="fa-solid fa-shield text-indigo-600"></i>
                                    <span>Bescherming</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Prijs per stuk: <strong class="text-slate-900">€15.000</strong>
                                    <span class="text-slate-400 mx-1">&bull;</span>
                                    Uit Gangpot: <strong class="text-indigo-700 font-extrabold">{{ number_format($possibleProtection, 0, ',', '.') }}x</strong>
                                </p>
                            </div>
                            <form action="{{ route('gangs.shop-buy') }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                <input type="hidden" name="item_type" value="protection">
                                <input type="number" 
                                       name="quantity" 
                                       x-model.number="qty"
                                       min="{{ $possibleProtection > 0 ? 1 : 0 }}" 
                                       max="{{ max(1, $possibleProtection) }}" 
                                       value="{{ $possibleProtection }}" 
                                       class="w-20 bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 text-center focus:outline-none focus:border-purple-600">
                                <button type="submit" 
                                        :disabled="qty < 1 || qty > {{ max(0, $possibleProtection) }}"
                                        @if($possibleProtection <= 0) disabled @endif
                                        class="px-5 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-xs uppercase cursor-pointer transition shadow-xs">
                                    Koop
                                </button>
                            </form>
                        </div>

                        <!-- Item 3: Power -->
                        <div x-data="{ qty: {{ $possiblePower }} }" class="bg-slate-50 border border-slate-200 rounded-xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                                    <i class="fa-solid fa-bolt text-amber-500"></i>
                                    <span>Power</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Prijs per stuk: <strong class="text-slate-900">€15.000</strong>
                                    <span class="text-slate-400 mx-1">&bull;</span>
                                    Uit Gangpot: <strong class="text-amber-700 font-extrabold">{{ number_format($possiblePower, 0, ',', '.') }}x</strong>
                                </p>
                            </div>
                            <form action="{{ route('gangs.shop-buy') }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                <input type="hidden" name="item_type" value="power">
                                <input type="number" 
                                       name="quantity" 
                                       x-model.number="qty"
                                       min="{{ $possiblePower > 0 ? 1 : 0 }}" 
                                       max="{{ max(1, $possiblePower) }}" 
                                       value="{{ $possiblePower }}" 
                                       class="w-20 bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 text-center focus:outline-none focus:border-purple-600">
                                <button type="submit" 
                                        :disabled="qty < 1 || qty > {{ max(0, $possiblePower) }}"
                                        @if($possiblePower <= 0) disabled @endif
                                        class="px-5 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-xs uppercase cursor-pointer transition shadow-xs">
                                    Koop
                                </button>
                            </form>
                        </div>

                        <!-- Item 4: Meer leden -->
                        <div x-data="{ qty: {{ $possibleMaxMembers }} }" class="bg-slate-50 border border-slate-200 rounded-xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                                    <i class="fa-solid fa-user-plus text-purple-600"></i>
                                    <span>Meer leden capaciteit</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Prijs per stuk: <strong class="text-amber-600 inline-flex items-center gap-1"><i class="fa-solid fa-star text-amber-500"></i> 25 Sterren</strong>
                                    <span class="text-slate-400 mx-1">&bull;</span>
                                    Mogelijk: <strong class="text-purple-700 font-extrabold">{{ number_format($possibleMaxMembers, 0, ',', '.') }}x</strong>
                                </p>
                                <span class="text-[11px] text-slate-400 font-bold block mt-1">
                                    Huidig limiet: {{ $myGang ? $myGang->members->count() : 0 }} / {{ $currentMembersLimit }} (max 35)
                                </span>
                            </div>
                            <form action="{{ route('gangs.shop-buy') }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                <input type="hidden" name="item_type" value="max_members">
                                <input type="number" 
                                       name="quantity" 
                                       x-model.number="qty"
                                       min="{{ $possibleMaxMembers > 0 ? 1 : 0 }}" 
                                       max="{{ max(1, $possibleMaxMembers) }}" 
                                       value="{{ $possibleMaxMembers }}" 
                                       class="w-20 bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 text-center focus:outline-none focus:border-purple-600">
                                <button type="submit" 
                                        :disabled="qty < 1 || qty > {{ max(0, $possibleMaxMembers) }}"
                                        @if($possibleMaxMembers <= 0) disabled @endif
                                        class="px-5 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-xs uppercase cursor-pointer transition shadow-xs">
                                    Koop
                                </button>
                            </form>
                        </div>

                        <!-- Item 5: Bende Portofoon / Gang Chat -->
                        <div class="bg-gradient-to-r {{ $myGang && $myGang->has_gang_chat ? 'from-emerald-50 to-emerald-100/50 border-emerald-300 dark:from-emerald-950/30 dark:to-emerald-900/20 dark:border-emerald-700/60' : 'from-purple-50 to-indigo-50/50 border-purple-200 dark:from-purple-950/30 dark:to-indigo-950/20 dark:border-purple-800/60' }} border-2 rounded-xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                                        <i class="fa-solid fa-walkie-talkie text-purple-600 dark:text-purple-400"></i>
                                        <span>Bende Portofoon (Besloten Gang Chat)</span>
                                    </h3>
                                    @if($myGang && $myGang->has_gang_chat)
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-500 text-white text-[9px] font-black uppercase">Ontgrendeld ✓</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-amber-500 text-slate-950 text-[9px] font-black uppercase">Exclusief ⭐</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-xl">
                                    Eenmalige aanschaf voor de hele bende. Ontgrendelt een privé en beveiligd communicatiekanaal in de live chat, uitsluitend toegankelijk voor ingeschreven leden van <strong>{{ $myGang->name ?? 'je bende' }}</strong>.
                                </p>
                                <span class="text-[11px] text-amber-600 dark:text-amber-400 font-extrabold inline-flex items-center gap-1 mt-1.5">
                                    <i class="fa-solid fa-star text-amber-500"></i> Eenmalig 500 Sterren
                                </span>
                            </div>
                            <div>
                                @if($myGang && $myGang->has_gang_chat)
                                    <a href="{{ route('chat.index', ['channel' => 'gang_' . $myGang->id]) }}" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase tracking-wide transition shadow-xs flex items-center gap-2">
                                        <i class="fa-solid fa-comments"></i>
                                        <span>Open Bende Chat</span>
                                    </a>
                                @else
                                    @php
                                        $has500Stars = ($character->stars ?? 0) >= 500 || (($myGang->gang_stars ?? 0) >= 500 && $myGang->isOfficer($character));
                                    @endphp
                                    <form action="{{ route('gangs.shop-buy') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="item_type" value="gang_chat">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" 
                                                @if(! $has500Stars) disabled @endif
                                                class="px-5 py-2.5 rounded-xl {{ $has500Stars ? 'bg-amber-500 hover:bg-amber-400 text-slate-950 font-black cursor-pointer shadow-sm active:scale-95' : 'bg-slate-200 text-slate-400 cursor-not-allowed font-bold' }} text-xs uppercase tracking-wider transition flex items-center gap-2">
                                            <i class="fa-solid fa-unlock-keyhole"></i>
                                            <span>Koop Gang Chat (500 ⭐)</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- TAB 3: DONEREN -->
            <div x-show="activeGangTab === 'doneren'" class="space-y-6" x-data="{ donationView: 'top', donateAmount: {{ $character->cash > 0 ? min($character->cash, 5000) : 500 }}, starAmount: 10 }">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-7 shadow-xs space-y-6">
                    
                    <!-- Section Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200 dark:border-purple-800/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl shrink-0 shadow-2xs">
                                <i class="fa-solid fa-vault"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-0.5">
                                    <span class="text-[10px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/70 border border-purple-200 dark:border-purple-800/60 px-2 py-0.5 rounded-md">Bende Financiën</span>
                                </div>
                                <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight">Doneren aan Gang Kas</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    Doneer contant geld om smokkelkiosken te financieren en het collectieve uurloon voor alle leden te verhogen!
                                </p>
                            </div>
                        </div>

                        <!-- Stats Pill Highlights -->
                        @if($myGang)
                        <div class="flex items-center gap-2 shrink-0">
                            <div class="px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300 shadow-2xs">
                                <span class="text-[10px] text-slate-400 uppercase tracking-wider block font-bold">Collectief Uurloon</span>
                                <span class="font-black text-emerald-600 dark:text-emerald-400 font-mono">+€{{ number_format($hourlyWage, 0, ',', '.') }}/u</span>
                            </div>
                            <div class="px-3.5 py-2 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200 dark:border-purple-800/60 text-xs text-purple-700 dark:text-purple-300 shadow-2xs">
                                <span class="text-[10px] text-purple-400 uppercase tracking-wider block font-bold">Kiosken</span>
                                <span class="font-black text-purple-900 dark:text-purple-200 font-mono">{{ $myGang->bling_shops ?? 0 }}</span>
                            </div>
                        </div>
                        @endif
                    </div>

                    @if($myGang)
                    <!-- Cash Donation Box -->
                    <div class="bg-gradient-to-br from-slate-50 to-purple-50/30 dark:from-slate-800/50 dark:to-purple-950/20 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                <i class="fa-solid fa-wallet text-purple-600 dark:text-purple-400"></i>
                                <span>Contant op zak:</span>
                                <strong class="text-emerald-600 dark:text-emerald-400 font-black font-mono">€{{ number_format($character->cash, 0, ',', '.') }}</strong>
                            </span>

                            <!-- Quick Amount Fill Buttons -->
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <button type="button" @click="donateAmount = Math.min({{ $character->cash }}, 10000)" 
                                        class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-purple-400 transition cursor-pointer">
                                    +€10.000
                                </button>
                                <button type="button" @click="donateAmount = Math.min({{ $character->cash }}, 50000)" 
                                        class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-purple-400 transition cursor-pointer">
                                    +€50.000
                                </button>
                                <button type="button" @click="donateAmount = {{ $character->cash }}" 
                                        class="px-2.5 py-1 text-[11px] font-black rounded-lg bg-purple-100 dark:bg-purple-950/80 border border-purple-300 dark:border-purple-700 text-purple-800 dark:text-purple-300 hover:bg-purple-200 transition cursor-pointer">
                                    Alles / Max
                                </button>
                            </div>
                        </div>

                        <form action="{{ route('gangs.donate') }}" method="POST" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            @csrf
                            <div class="relative flex-1">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-black text-sm">
                                    €
                                </div>
                                <input type="number" 
                                       name="amount" 
                                       x-model.number="donateAmount"
                                       min="50" 
                                       max="{{ $character->cash }}" 
                                       required
                                       class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl pl-8 pr-4 py-2.5 text-xs font-black text-slate-900 dark:text-white focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600 font-mono shadow-2xs">
                            </div>
                            <button type="submit" 
                                    :disabled="donateAmount < 50 || donateAmount > {{ $character->cash }}"
                                    class="py-2.5 px-6 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed text-white font-black text-xs uppercase tracking-wider cursor-pointer transition shadow-xs shrink-0 flex items-center justify-center gap-2">
                                <i class="fa-solid fa-hand-holding-dollar"></i>
                                <span>Doneer Cash</span>
                            </button>
                        </form>
                    </div>

                    <!-- Donaties van vandaag Section (Geen dubbele namen!) -->
                    <div class="space-y-3.5 pt-2">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                            <div class="flex items-center gap-2">
                                <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white">
                                    Donaties van Vandaag
                                </h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300">
                                    {{ $todayDonations->count() }} Donateurs
                                </span>
                            </div>

                            <!-- Toggle tussen Top Donateurs (Uniek) en Live Transactielog -->
                            <div class="inline-flex rounded-xl bg-slate-100 dark:bg-slate-800 p-1 text-xs">
                                <button type="button" 
                                        @click="donationView = 'top'" 
                                        :class="donationView === 'top' ? 'bg-white dark:bg-slate-700 text-purple-700 dark:text-purple-300 shadow-2xs font-black' : 'text-slate-500 hover:text-slate-900 font-bold'"
                                        class="px-3 py-1 rounded-lg transition cursor-pointer flex items-center gap-1.5">
                                    <i class="fa-solid fa-trophy text-[10px]"></i>
                                    <span>Top Donateurs</span>
                                </button>
                                <button type="button" 
                                        @click="donationView = 'log'" 
                                        :class="donationView === 'log' ? 'bg-white dark:bg-slate-700 text-purple-700 dark:text-purple-300 shadow-2xs font-black' : 'text-slate-500 hover:text-slate-900 font-bold'"
                                        class="px-3 py-1 rounded-lg transition cursor-pointer flex items-center gap-1.5">
                                    <i class="fa-solid fa-clock-rotate-left text-[10px]"></i>
                                    <span>Live Log</span>
                                </button>
                            </div>
                        </div>

                        <!-- 1. TOP DONATEURS TAB (Geaggregeerd per lid: GEEN dubbele namen!) -->
                        <div x-show="donationView === 'top'" class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800">
                                        <th class="py-2.5 px-3">RANG</th>
                                        <th class="py-2.5 px-3">PATIËNT</th>
                                        <th class="py-2.5 px-3 text-center">AANTAL</th>
                                        <th class="py-2.5 px-3 text-right">TOTAAL VANDAAG</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                                    @forelse($todayDonations as $index => $d)
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                        <!-- Rank Badge -->
                                        <td class="py-3 px-3 w-14">
                                            @if($index === 0)
                                                <span class="w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-950/70 border border-amber-300 dark:border-amber-700/80 text-amber-700 dark:text-amber-300 font-black text-xs flex items-center justify-center shadow-2xs">
                                                    🥇
                                                </span>
                                            @elseif($index === 1)
                                                <span class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-black text-xs flex items-center justify-center shadow-2xs">
                                                    🥈
                                                </span>
                                            @elseif($index === 2)
                                                <span class="w-6 h-6 rounded-full bg-orange-100 dark:bg-orange-950/70 border border-orange-300 dark:border-orange-700/80 text-orange-700 dark:text-orange-300 font-black text-xs flex items-center justify-center shadow-2xs">
                                                    🥉
                                                </span>
                                            @else
                                                <span class="text-slate-400 font-mono font-bold text-xs pl-1.5">
                                                    #{{ $index + 1 }}
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Patient Info -->
                                        <td class="py-3 px-3">
                                            <div class="flex items-center gap-2.5">
                                                <a href="{{ route('profile.show', $d->character->name) }}" class="font-black text-purple-700 dark:text-purple-400 hover:text-purple-900 dark:hover:text-purple-300 hover:underline flex items-center gap-1.5">
                                                    <span>{{ $d->character->name }}</span>
                                                </a>
                                                @if($d->character->gang_role === 'leader')
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60">
                                                        Leider
                                                    </span>
                                                @elseif($d->character->gang_role === 'co_leader')
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-indigo-100 dark:bg-indigo-950/70 text-indigo-800 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/60">
                                                        Co-Leider
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Donation Count -->
                                        <td class="py-3 px-3 text-center">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                                {{ $d->donation_count }}x
                                            </span>
                                        </td>

                                        <!-- Total Amount Donated -->
                                        <td class="py-3 px-3 text-right">
                                            <div class="font-extrabold text-emerald-600 dark:text-emerald-400 font-mono text-sm">
                                                €{{ number_format($d->amount, 0, ',', '.') }}
                                            </div>
                                            @if($totalDonatedToday > 0)
                                            <span class="text-[10px] text-slate-400 font-mono">
                                                {{ round(($d->amount / $totalDonatedToday) * 100) }}% van dagtotaal
                                            </span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-xs text-slate-400">
                                            <i class="fa-solid fa-hand-holding-dollar text-2xl text-slate-300 dark:text-slate-700 mb-2 block"></i>
                                            <span>Nog geen donaties vandaag. Wees de eerste donateur!</span>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- 2. LIVE LOG TAB (Individuele transacties met tijd) -->
                        <div x-show="donationView === 'log'" style="display: none;" class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800">
                                        <th class="py-2.5 px-3">TIJD</th>
                                        <th class="py-2.5 px-3">PATIËNT</th>
                                        <th class="py-2.5 px-3 text-right">BEDRAG</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                                    @forelse($todayRecentLogs ?? [] as $log)
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                        <td class="py-2.5 px-3 font-mono text-[11px] text-slate-400">
                                            {{ $log->created_at->format('H:i') }}
                                        </td>
                                        <td class="py-2.5 px-3 font-bold text-slate-800 dark:text-slate-200">
                                            <a href="{{ route('profile.show', $log->character->name) }}" class="text-purple-700 dark:text-purple-400 hover:underline">
                                                {{ $log->character->name }}
                                            </a>
                                        </td>
                                        <td class="py-2.5 px-3 text-right font-extrabold text-emerald-600 dark:text-emerald-400 font-mono">
                                            +€{{ number_format($log->amount, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="py-6 text-center text-xs text-slate-400">
                                            Geen transacties gelogd voor vandaag.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary Footer Cards -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-400">Totaal gedoneerd vandaag:</span>
                                <strong class="text-sm font-black text-slate-900 dark:text-white font-mono">€{{ number_format($totalDonatedToday, 0, ',', '.') }}</strong>
                            </div>
                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-400">Smokkelkiosken in bezit:</span>
                                <strong class="text-sm font-black text-purple-700 dark:text-purple-400 font-mono">{{ $myGang->bling_shops ?? 0 }} kiosken</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Sterren doneren Card -->
                    <div class="bg-gradient-to-br from-amber-50/50 to-orange-50/30 dark:from-slate-800/70 dark:to-amber-950/20 border border-amber-200 dark:border-amber-800/50 rounded-2xl p-5 space-y-3.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-amber-500 text-white flex items-center justify-center text-sm shadow-2xs shrink-0">
                                    <i class="fa-solid fa-star"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-slate-900 dark:text-white">
                                        Sterren doneren aan bende
                                    </h3>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                        Doneer sterren om het maximale ledenaantal van je bende te vergroten.
                                    </p>
                                </div>
                            </div>
                            <button type="button" @click="setGangTab('beheer'); setBeheerTab('donaties')" class="px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 text-purple-700 dark:text-purple-300 text-xs font-extrabold border border-purple-200 dark:border-purple-800 shadow-2xs hover:bg-purple-50 transition cursor-pointer">
                                <i class="fa-solid fa-list-check mr-1"></i> Logs
                            </button>
                        </div>

                        <div class="flex items-center justify-between text-xs text-slate-600 dark:text-slate-400">
                            <span>Jouw saldo: <strong class="text-amber-600 dark:text-amber-400 font-extrabold">{{ $character->stars ?? 0 }} Sterren ⭐</strong></span>
                            <div class="flex items-center gap-1.5">
                                <button type="button" @click="starAmount = 5" class="px-2 py-0.5 text-[10px] font-bold rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-amber-400 transition cursor-pointer">5 ⭐</button>
                                <button type="button" @click="starAmount = 10" class="px-2 py-0.5 text-[10px] font-bold rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-amber-400 transition cursor-pointer">10 ⭐</button>
                                <button type="button" @click="starAmount = 25" class="px-2 py-0.5 text-[10px] font-bold rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-amber-400 transition cursor-pointer">25 ⭐</button>
                            </div>
                        </div>

                        <form action="{{ route('gangs.donate-stars') }}" method="POST" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            @csrf
                            <div class="relative flex-1">
                                <i class="fa-solid fa-star absolute left-3.5 top-3 text-amber-500 text-xs"></i>
                                <input type="number" 
                                       name="stars" 
                                       x-model.number="starAmount"
                                       min="1" 
                                       max="{{ $character->stars ?? 0 }}" 
                                       required
                                       class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl pl-8 pr-4 py-2 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:border-amber-500 font-mono shadow-2xs">
                            </div>
                            <button type="submit" 
                                    :disabled="starAmount < 1 || starAmount > {{ $character->stars ?? 0 }}"
                                    class="py-2.5 px-6 rounded-xl bg-amber-500 hover:bg-amber-600 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed text-white font-black text-xs uppercase tracking-wider cursor-pointer transition shadow-xs shrink-0 flex items-center justify-center gap-2">
                                <i class="fa-solid fa-gem"></i>
                                <span>Doneer Sterren</span>
                            </button>
                        </form>
                    </div>

                    @else
                    <p class="text-xs text-slate-400 py-4">Je bent nog geen lid van een bende. Sluit je eerst aan bij een bende via de ranglijst!</p>
                    @endif
                </div>
            </div>

            <!-- TAB 4: RANGLIJST -->
            <div x-show="activeGangTab === 'ranglijst'" class="space-y-6">
                <!-- Gangs Banner Art -->
                <div class="rounded-2xl overflow-hidden border border-slate-200 shadow-xs bg-white p-6 sm:p-8 flex items-center justify-between">
                    <div class="space-y-1">
                        <div class="inline-flex items-center gap-2 bg-purple-50 text-purple-700 text-xs font-bold px-3 py-1 rounded-full border border-purple-200 mb-2">
                            <i class="fa-solid fa-trophy"></i> Prestigieuze Facties
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">
                            Gangs Ranglijst
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500">
                            Strijd om de macht en controleer de vleugels van de kliniek.
                        </p>
                    </div>
                    <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl border border-slate-200 overflow-hidden shadow-xs shrink-0 hidden sm:block">
                        <img src="{{ asset('assets/characters/avatar_female_1.jpg') }}" alt="Gangs Hero" class="w-full h-full object-cover">
                    </div>
                </div>

                <!-- Ranglijst Table -->
                <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-4">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2">
                                    <th class="py-3 px-2 w-10 text-center">#</th>
                                    <th class="py-3 px-3">BENDE & LEIDER</th>
                                    <th class="py-3 px-3 text-right">RESPECT</th>
                                    <th class="py-3 px-3 text-right">KAS</th>
                                    <th class="py-3 px-3 text-right">ACTIE</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                @forelse($gangRankings as $index => $g)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3.5 px-2 text-center font-bold font-mono text-xs {{ $index < 3 ? 'text-purple-700' : 'text-slate-400' }}">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="py-3.5 px-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-base shrink-0 font-bold shadow-xs">
                                                <i class="fa-solid {{ $g['icon'] }}"></i>
                                            </div>
                                            <div>
                                                <div class="font-extrabold text-slate-900 text-xs leading-tight flex items-center gap-1.5">
                                                    <span>{{ $g['name'] }}</span>
                                                    <span class="text-purple-600 font-mono text-[10px] font-bold">[{{ $g['tag'] }}]</span>
                                                    @if($index < 3)
                                                        <i class="fa-solid fa-circle-check text-purple-600 text-[10px]"></i>
                                                    @endif
                                                </div>
                                                <div class="text-[11px] text-slate-400 font-medium">
                                                    {{ $g['leader_tag'] }} &bull; {{ $g['members_count'] }} leden
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-3 text-right font-extrabold text-purple-700 font-mono text-xs">
                                        {{ $g['slay'] }}
                                    </td>
                                    <td class="py-3.5 px-3 text-right font-bold text-emerald-700 font-mono text-xs">
                                        {{ $g['cash'] }}
                                    </td>
                                    <td class="py-3.5 px-3 text-right">
                                        @if($myGang && $g['id'] === $myGang->id)
                                            <span class="px-2.5 py-1 rounded-lg bg-purple-100 text-purple-800 font-extrabold text-[11px] inline-flex items-center gap-1 border border-purple-200">
                                                <i class="fa-solid fa-check text-[10px]"></i> Jouw Gang
                                            </span>
                                        @elseif(! $character->gang_id)
                                            @if($g['has_pending_request'])
                                                <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 font-extrabold text-[11px] inline-flex items-center gap-1 border border-amber-200" title="Je hebt al een openstaande aanvraag ingediend">
                                                    <i class="fa-solid fa-clock-rotate-left text-[10px]"></i> In Behandeling
                                                </span>
                                            @elseif($g['is_open'])
                                                <form action="{{ route('gangs.request-join', $g['model']) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs transition cursor-pointer shadow-xs inline-flex items-center gap-1.5">
                                                        <i class="fa-solid fa-user-plus text-[10px]"></i> Aanvragen
                                                    </button>
                                                </form>
                                            @else
                                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-400 font-bold text-[11px] inline-flex items-center gap-1 border border-slate-200" title="Deze bende accepteert momenteel geen nieuwe lidmaatschapsverzoeken">
                                                    <i class="fa-solid fa-lock text-[10px]"></i> Gesloten
                                                </span>
                                            @endif
                                        @elseif($myGang && $g['id'] !== $myGang->id && $myGang->isOfficer($character))
                                            <button type="button" @click="setGangTab('oorlog')" class="px-2.5 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-[11px] transition cursor-pointer inline-flex items-center gap-1 shadow-xs">
                                                <i class="fa-solid fa-crosshairs text-[10px]"></i> Aanvallen
                                            </button>
                                        @else
                                            <span class="text-slate-400 text-xs font-mono">&bull;&bull;&bull;</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center">
                                        <div class="flex flex-col items-center justify-center gap-2 text-slate-400">
                                            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 text-xl">
                                                <i class="fa-solid fa-users-slash"></i>
                                            </div>
                                            <span class="font-bold text-slate-700 text-xs">Er zijn momenteel nog geen bendes opgericht!</span>
                                            <span class="text-[11px] text-slate-400 max-w-sm">Richt als eerste een bende op met 25 Sterren om over de kliniek te heersen.</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB: BENDE BEHEER (Eigen Volwaardige Pagina) -->
            @if($myGang && $myGang->isOfficer($character))
            <div x-show="activeGangTab === 'beheer'" class="space-y-6">
                <!-- Beheer Header Card -->
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-purple-50 border border-purple-200 text-purple-700 text-[10px] font-extrabold uppercase tracking-wider mb-1.5">
                                <i class="fa-solid fa-shield-halved"></i> Factie Administratie
                            </div>
                            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                                <span>Bende Beheer</span>
                                <span class="text-purple-700 font-mono text-base">[{{ $myGang->tag }}]</span>
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">Beheer de leden van {{ $myGang->name }}, verwerk toetredingsverzoeken en onderhoud bende-relaties.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @php
                                $officerRoleName = 'Bendelid';
                                if ($character->gang_role === 'leader') {
                                    $officerRoleName = 'Bendeleider';
                                } elseif ($character->gang_role === 'co_leader') {
                                    $officerRoleName = 'Co-Leider';
                                } elseif ((int) $myGang->money_magnet === (int) $character->id) {
                                    $officerRoleName = 'Kasbeheerder';
                                } elseif ((int) $myGang->personal_shopper === (int) $character->id) {
                                    $officerRoleName = 'Kantine Smokkelaar';
                                }
                            @endphp
                            <span class="px-3 py-1 rounded-xl bg-purple-50 border border-purple-200 text-purple-700 text-xs font-black uppercase tracking-wider inline-flex items-center gap-1.5">
                                <i class="fa-solid {{ $character->gang_role === 'leader' ? 'fa-crown text-amber-500' : 'fa-shield-halved text-purple-600' }} text-xs"></i>
                                <span>{{ $officerRoleName }}</span>
                            </span>
                            <button type="button" @click="setGangTab('instellingen')" 
                                    class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-sliders text-slate-500"></i>
                                <span>Naar Instellingen</span>
                            </button>
                        </div>
                    </div>

                    <!-- Sub-navigatie Tabs (Pills) -->
                    <div class="flex flex-wrap items-center gap-2 pt-4">
                        <button type="button" @click="setBeheerTab('leden')" 
                                :class="beheerTab === 'leden' ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                class="px-3.5 py-2 rounded-xl text-xs font-black transition cursor-pointer flex items-center gap-1.5">
                            <i class="fa-solid fa-users"></i>
                            <span>Leden ({{ $myGang->members->count() }})</span>
                        </button>
                        <button type="button" @click="setBeheerTab('verzoeken')" 
                                :class="beheerTab === 'verzoeken' ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                class="px-3.5 py-2 rounded-xl text-xs font-black transition cursor-pointer flex items-center gap-1.5">
                            <i class="fa-solid fa-user-plus"></i>
                            <span>Verzoeken</span>
                            @if($myGang->requests->where('status', 'pending')->count() > 0)
                                <span class="px-1.5 py-0.2 rounded-full bg-rose-500 text-white text-[9px] font-black">
                                    {{ $myGang->requests->where('status', 'pending')->count() }}
                                </span>
                            @endif
                        </button>
                        <button type="button" @click="setBeheerTab('massa_bericht')" 
                                :class="beheerTab === 'massa_bericht' ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                class="px-3.5 py-2 rounded-xl text-xs font-black transition cursor-pointer flex items-center gap-1.5">
                            <i class="fa-solid fa-envelope"></i>
                            <span>Massa Bericht</span>
                        </button>
                        <button type="button" @click="setBeheerTab('bondgenoten')" 
                                :class="beheerTab === 'bondgenoten' ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                class="px-3.5 py-2 rounded-xl text-xs font-black transition cursor-pointer flex items-center gap-1.5">
                            <i class="fa-solid fa-handshake"></i>
                            <span>Bondgenoten ({{ $myGang->alliances->count() }})</span>
                        </button>
                        <button type="button" @click="setBeheerTab('donaties')" 
                                :class="beheerTab === 'donaties' ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                class="px-3.5 py-2 rounded-xl text-xs font-black transition cursor-pointer flex items-center gap-1.5">
                            <i class="fa-solid fa-euro-sign"></i>
                            <span>Donaties</span>
                        </button>
                        @if($character->gang_role === 'leader')
                        <button type="button" @click="setBeheerTab('verwijderen')" 
                                :class="beheerTab === 'verwijderen' ? 'bg-rose-600 text-white shadow-xs' : 'bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200'"
                                class="px-3.5 py-2 rounded-xl text-xs font-black transition cursor-pointer flex items-center gap-1.5 ml-auto">
                            <i class="fa-solid fa-trash-can"></i>
                            <span>Bende Opheffen</span>
                        </button>
                        @endif
                    </div>
                </div>

                <!-- 1. Leden Beheer Tab Panel -->
                <div x-show="beheerTab === 'leden'" class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-users text-purple-600"></i>
                            <span>Bendeleden Overzicht ({{ $myGang->members->count() }} leden)</span>
                        </h3>
                        <span class="text-xs text-slate-500 font-medium">Max capaciteit: {{ $myGang->max_members ?? 10 }} leden</span>
                    </div>

                    <div class="space-y-2.5">
                        @foreach($myGang->members as $mem)
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs transition hover:bg-slate-100/60">
                            <div class="flex items-center gap-3">
                                <div class="relative shrink-0">
                                    <img src="{{ $mem->avatar_url }}" alt="{{ $mem->name }}" class="w-10 h-10 rounded-xl object-cover border border-slate-200 shadow-2xs">
                                    <span class="absolute -bottom-1 -right-1 w-2.5 h-2.5 rounded-full border-2 border-white {{ $mem->isOnline() ? 'bg-emerald-500' : 'bg-slate-300' }}" title="{{ $mem->online_status_label }}"></span>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('profile.show', $mem->name) }}" class="font-extrabold text-slate-900 hover:text-purple-700 hover:underline">
                                            {{ $mem->name }}
                                        </a>
                                        @if($mem->gang_role === 'leader')
                                            <span class="px-2 py-0.5 rounded-md bg-amber-50 border border-amber-200 text-amber-700 text-[10px] font-black uppercase inline-flex items-center gap-1">
                                                <i class="fa-solid fa-crown text-amber-500 text-[9px]"></i> Leider
                                            </span>
                                        @elseif($mem->gang_role === 'co_leader')
                                            <span class="px-2 py-0.5 rounded-md bg-purple-50 border border-purple-200 text-purple-700 text-[10px] font-black uppercase inline-flex items-center gap-1">
                                                <i class="fa-solid fa-star text-purple-600 text-[9px]"></i> Co-Leider
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-md bg-slate-200 text-slate-700 text-[10px] font-bold">
                                                Bendelid
                                            </span>
                                        @endif

                                        @if($myGang->personal_shopper === $mem->id)
                                            <span class="px-2 py-0.5 rounded-md bg-indigo-50 border border-indigo-200 text-indigo-700 text-[10px] font-bold inline-flex items-center gap-1">
                                                <i class="fa-solid fa-box text-indigo-500 text-[9px]"></i> Kantine Smokkelaar
                                            </span>
                                        @endif
                                        @if($myGang->money_magnet === $mem->id)
                                            <span class="px-2 py-0.5 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] font-bold inline-flex items-center gap-1">
                                                <i class="fa-solid fa-vault text-emerald-600 text-[9px]"></i> Kasbeheerder
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-3">
                                        <span>Level {{ $mem->level ?? 1 }}</span>
                                        <span>&bull;</span>
                                        <span>Respect: <strong class="text-purple-700 font-mono">{{ number_format($mem->respect ?? 0, 0, ',', '.') }}</strong></span>
                                        <span>&bull;</span>
                                        <span>Lid sinds: {{ $mem->created_at?->diffForHumans() ?? 'onbekend' }}</span>
                                    </div>
                                </div>
                            </div>

                            @if($myGang->isOfficer($character) && $mem->id !== $character->id && $mem->gang_role !== 'leader')
                            <div class="flex items-center gap-2 self-end sm:self-auto shrink-0">
                                @if($character->gang_role === 'leader')
                                <form action="{{ route('gangs.promote', $mem) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="role" value="{{ $mem->gang_role === 'co_leader' ? 'member' : 'co_leader' }}">
                                    <button type="submit" class="px-3 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 rounded-xl text-xs font-bold transition cursor-pointer">
                                        {{ $mem->gang_role === 'co_leader' ? 'Degradeer' : 'Promoveer tot Co-Leider' }}
                                    </button>
                                </form>
                                @endif
                                <form action="{{ route('gangs.kick', $mem) }}" method="POST"
                                      data-confirm="Weet je zeker dat je bendelid '{{ $mem->name }}' wilt verwijderen uit {{ $myGang->name }}?"
                                      data-confirm-title="Bendelid Verwijderen"
                                      data-confirm-btn="Lid Verwijderen"
                                      data-confirm-type="danger"
                                      data-confirm-icon="fa-user-xmark">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded-xl text-xs font-bold transition cursor-pointer">
                                        Kick
                                    </button>
                                </form>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>



                <!-- 2. VERZOEKEN TAB -->
                <div x-show="beheerTab === 'verzoeken'" class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-user-plus text-purple-600"></i>
                                <span>Lidmaatschapsverzoeken</span>
                            </h3>
                            @if($myGang->requests->where('status', 'pending')->count() > 0)
                                <span class="px-2 py-0.5 rounded-full bg-purple-100 text-purple-700 text-[10px] font-black">
                                    {{ $myGang->requests->where('status', 'pending')->count() }} openstaand
                                </span>
                            @endif
                        </div>
                        <span class="text-xs text-slate-500 font-medium">Toelatingseis: min. {{ number_format($myGang->min_respect ?? 0, 0, ',', '.') }} respect</span>
                    </div>

                    <div class="space-y-2.5">
                        @forelse($myGang->requests->where('status', 'pending') as $req)
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs transition hover:bg-slate-100/60">
                            <div class="flex items-center gap-3">
                                <div class="relative shrink-0">
                                    <img src="{{ $req->character->avatar_url }}" alt="{{ $req->character->name }}" class="w-10 h-10 rounded-xl object-cover border border-slate-200 shadow-2xs">
                                    <span class="absolute -bottom-1 -right-1 w-2.5 h-2.5 rounded-full border-2 border-white {{ $req->character->isOnline() ? 'bg-emerald-500' : 'bg-slate-300' }}" title="{{ $req->character->online_status_label }}"></span>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('profile.show', $req->character->name) }}" class="font-extrabold text-slate-900 hover:text-purple-700 hover:underline">
                                            {{ $req->character->name }}
                                        </a>
                                        <span class="text-[11px] text-slate-500">
                                            (Level {{ $req->character->level ?? 1 }} &bull; {{ number_format($req->character->respect ?? 0, 0, ',', '.') }} respect)
                                        </span>
                                    </div>
                                    @if($req->message)
                                        <p class="text-xs text-slate-600 mt-1 italic bg-white p-2 rounded-lg border border-slate-200/80">"{{ $req->message }}"</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2 self-end sm:self-auto shrink-0">
                                <form action="{{ route('gangs.handle-request', $req) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="action" value="accept">
                                    <button type="submit" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-xs transition cursor-pointer flex items-center gap-1.5">
                                        <i class="fa-solid fa-check"></i> Accepteren
                                    </button>
                                </form>
                                <form action="{{ route('gangs.handle-request', $req) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="px-3.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded-xl font-bold text-xs transition cursor-pointer flex items-center gap-1.5">
                                        <i class="fa-solid fa-xmark"></i> Weigeren
                                    </button>
                                </form>
                            </div>
                        </div>
                        @empty
                        <div class="py-10 text-center space-y-2">
                            <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 text-lg">
                                <i class="fa-solid fa-inbox"></i>
                            </div>
                            <p class="text-xs text-slate-500 font-medium">Er zijn momenteel geen openstaande verzoeken om lid te worden.</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- 3. MASSA BERICHT TAB -->
                <div x-show="beheerTab === 'massa_bericht'" class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-5">
                    <div class="border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-paper-plane text-purple-600"></i>
                            <span>Massabericht naar alle Bendeleden</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">Stuur met één klik een officiële bende-mededeling rechtstreeks naar de inbox van ieder bendelid.</p>
                    </div>

                    <form action="{{ route('gangs.mass-message') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Onderwerp</label>
                            <input type="text" name="subject" required placeholder="Bijv. Belangrijke vergadering op de binnenplaats" 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-900 focus:outline-none focus:border-purple-600 focus:bg-white transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Bericht Inhoud</label>
                            <textarea name="message" required rows="4" placeholder="Typ hier je instructies of mededeling aan alle bendeleden..." 
                                      class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3.5 text-xs font-medium text-slate-900 focus:outline-none focus:border-purple-600 focus:bg-white transition resize-y"></textarea>
                        </div>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs shadow-xs transition cursor-pointer flex items-center gap-2">
                            <i class="fa-solid fa-paper-plane"></i>
                            <span>Verstuur naar alle {{ $myGang->members->count() }} leden</span>
                        </button>
                    </form>
                </div>

                <!-- 4. BONDGENOTEN TAB -->
                <div x-show="beheerTab === 'bondgenoten'" class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-6">
                    <div class="border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-handshake text-purple-600"></i>
                            <span>Bondgenoten & Allianties</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">
                            Bondgenoten zijn bevriende bendes binnen de kliniek. Jullie kunnen elkaars kassen niet plunderen en staan sterker in bendeoorlogen.
                        </p>
                    </div>

                    <!-- Add Ally Form -->
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                        <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider mb-3">Nieuwe Bondgenoot Toevoegen</h4>
                        <form action="{{ route('gangs.add-alliance') }}" method="POST" class="space-y-3">
                            @csrf
                            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                <label class="text-xs font-bold text-slate-700 sm:w-28 shrink-0">Kies Bende:</label>
                                <select name="ally_gang_id" class="w-full bg-white border border-slate-200 text-slate-800 rounded-xl px-3.5 py-2 text-xs font-bold focus:outline-none focus:border-purple-600">
                                    @php
                                        $otherGangs = $realGangs->where('id', '!=', $myGang->id);
                                    @endphp
                                    @forelse($otherGangs as $og)
                                        <option value="{{ $og->id }}">{{ $og->name }} [{{ $og->tag }}]</option>
                                    @empty
                                        <option value="">Geen andere bendes beschikbaar</option>
                                    @endforelse
                                </select>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs shadow-xs transition cursor-pointer flex items-center gap-1.5">
                                    <i class="fa-solid fa-plus"></i> Bondgenoot Toevoegen
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Current Allies -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-extrabold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-shield-halved text-purple-600"></i>
                            <span>Actieve Bondgenoten ({{ $myGang->alliances->count() }})</span>
                        </h4>
                        <div class="space-y-2">
                            @forelse($myGang->alliances as $ally)
                                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center font-bold">
                                            <i class="fa-solid fa-handshake"></i>
                                        </div>
                                        <div>
                                            <span class="font-extrabold text-slate-900">{{ $ally->allyGang->name ?? 'Onbekend' }}</span>
                                            <span class="text-[11px] text-purple-700 font-bold ml-1">[{{ $ally->allyGang->tag ?? '---' }}]</span>
                                        </div>
                                    </div>
                                    <form action="{{ route('gangs.remove-alliance', $ally) }}" method="POST"
                                          data-confirm="Weet je zeker dat je het bondgenootschap met {{ $ally->allyGang->name ?? 'deze bende' }} wilt beëindigen?"
                                          data-confirm-title="Bondgenootschap Beëindigen"
                                          data-confirm-btn="Beëindigen"
                                          data-confirm-type="warning">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded-xl text-xs font-bold transition cursor-pointer">
                                            Beëindigen
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <p class="text-xs text-slate-500 py-3 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200">
                                    Je factie heeft momenteel geen actieve bondgenoten ingesteld.
                                </p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Inbound Allies -->
                    <div class="space-y-3 pt-2">
                        <h4 class="text-xs font-extrabold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-heart text-pink-500"></i>
                            <span>Bendes die ons vertrouwen</span>
                        </h4>
                        <div class="space-y-2">
                            @forelse($inboundAlliances as $inbound)
                                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-pink-100 text-pink-600 flex items-center justify-center font-bold">
                                            <i class="fa-solid fa-shield"></i>
                                        </div>
                                        <div>
                                            <span class="font-extrabold text-slate-900">{{ $inbound->gang->name ?? 'Onbekend' }}</span>
                                            <span class="text-[11px] text-slate-500 ml-1">[{{ $inbound->gang->tag ?? '---' }}]</span>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 font-extrabold text-[10px]">
                                        Vertrouwt Ons
                                    </span>
                                </div>
                            @empty
                                <p class="text-xs text-slate-500 py-3 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200">
                                    Geen externe bendes hebben ons geregistreerd als bondgenoot.
                                </p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- 5. DONATIES TAB -->
                <div x-show="beheerTab === 'donaties'" class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-vault text-purple-600"></i>
                            <span>Donaties & Bende Kluis Bijdragen</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">
                            Inzicht in alle financiële giften van leden aan de gezamenlijke kas van {{ $myGang->name }}.
                        </p>
                    </div>

                    <!-- Filter Row -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Filter op periode:</label>
                            <select class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-3.5 py-2 text-xs font-bold focus:outline-none focus:border-purple-600">
                                <option value="vandaag" selected>Vandaag</option>
                                <option value="gisteren">Gisteren</option>
                                <option value="deze_week">Deze week</option>
                                <option value="deze_maand">Deze maand</option>
                                <option value="alles">Alles / Totaal</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Filter op week:</label>
                            <select class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-3.5 py-2 text-xs font-bold focus:outline-none focus:border-purple-600">
                                <option value="">Selecteer een week</option>
                                @for($w = (int) date('W'); $w >= max(1, (int) date('W') - 8); $w--)
                                    <option value="{{ $w }}">Week {{ $w }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <!-- Donations Table -->
                    <div class="overflow-x-auto pt-2">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-200 text-slate-500 font-extrabold uppercase text-[11px] tracking-wider">
                                    <th class="py-3 px-3">Bendelid</th>
                                    <th class="py-3 px-3 text-right">Totaal Gedoneerd</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($memberDonations as $md)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3 px-3">
                                        <a href="{{ route('profile.show', $md->character->name) }}" class="font-extrabold text-purple-700 hover:text-purple-900 hover:underline">
                                            {{ $md->character->name }}
                                        </a>
                                    </td>
                                    <td class="py-3 px-3 text-right font-extrabold text-slate-900 font-mono">
                                        €{{ number_format($md->total_amount, 0, ',', '.') }}
                                        <span class="text-slate-400 font-normal text-[11px] ml-1">({{ $md->donation_count }} donaties)</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="py-8 text-center text-xs text-slate-400">
                                        Er zijn (nog) geen donaties geregistreerd voor deze factie.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 6. BENDE VERWIJDEREN TAB -->
                <div x-show="beheerTab === 'verwijderen'" class="bg-white border border-rose-200 rounded-2xl p-6 shadow-xs space-y-4">
                    <div class="border-b border-rose-100 pb-3">
                        <h3 class="text-sm font-extrabold text-rose-600 flex items-center gap-2">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span>Bende Definitief Opheffen</span>
                        </h3>
                        <p class="text-xs text-slate-600 mt-1">
                            Het opheffen van een bende is onomkeerbaar. Alle upgrades, kluissaldo en status gaan verloren.
                        </p>
                    </div>

                    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 leading-relaxed space-y-2">
                        <p class="font-bold">Let goed op:</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Alle {{ $myGang->members->count() }} leden worden direct ontbonden en staan weer op zichzelf.</li>
                            <li>De bende kluis met een saldo van <strong>€{{ number_format($myGang->bank ?? 0, 0, ',', '.') }}</strong> verdwijnt permanent.</li>
                            <li>Alle allianties en ranglijstposities vervallen.</li>
                        </ul>
                    </div>

                    <form action="{{ route('gangs.disband') }}" method="POST"
                          data-confirm="Weet je zeker dat je bende '{{ $myGang->name }}' definitief wilt opheffen? Alle leden worden ontbonden en de gezamenlijke kas verdwijnt onherroepelijk!"
                          data-confirm-title="Bende Definitief Opheffen"
                          data-confirm-btn="Bende Verwijderen"
                          data-confirm-type="danger"
                          data-confirm-icon="fa-trash-can">
                        @csrf
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs shadow-xs transition cursor-pointer flex items-center gap-2">
                            <i class="fa-solid fa-trash-can"></i>
                            <span>Ja, Bende Definitief Opheffen</span>
                        </button>
                    </form>
                </div>
            </div>
            @endif

            <!-- DEDICATED TAB: BENDE INSTELLINGEN (EIGEN PAGINA) -->
            @if($myGang && $myGang->isOfficer($character))
            <div x-show="activeGangTab === 'instellingen'" class="space-y-6">

                <!-- Header Banner -->
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-purple-50 text-purple-700 border border-purple-200 text-[10px] font-black uppercase tracking-wider mb-1.5">
                            <i class="fa-solid fa-sliders"></i> Configuratie
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-gear text-purple-600"></i>
                            <span>Bende Instellingen</span>
                        </h2>
                        <p class="text-xs text-slate-500 mt-1 max-w-xl">
                            Beheer de visuele identiteit, sleutelfuncties, toegangseisen en het publieke manifest van <strong>{{ $myGang->name }}</strong>.
                        </p>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" @click="setGangTab('beheer')" 
                                class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-extrabold transition cursor-pointer flex items-center gap-1.5">
                            <i class="fa-solid fa-users-gear text-slate-500"></i>
                            <span>Naar Beheer</span>
                        </button>
                    </div>
                </div>

                <!-- 1. VISUELE VORMGEVING (AVATAR & BANNER) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Avatar Card -->
                    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4">
                        <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                            <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-image text-purple-600"></i>
                                <span>Bende Embleem / Avatar</span>
                            </h3>
                            <span class="text-[11px] text-slate-400 font-medium">JPG, PNG (max. 2MB)</span>
                        </div>

                        <div class="flex items-center gap-4">
                            <img src="{{ $myGang->avatar_url }}" alt="Gang Avatar" 
                                 class="w-20 h-20 rounded-2xl object-cover border-2 border-slate-200 shadow-sm shrink-0">
                            
                            <form action="{{ route('gangs.settings') }}" method="POST" enctype="multipart/form-data" class="flex-1 space-y-3">
                                @csrf
                                <input type="hidden" name="action_type" value="avatar">
                                <input type="file" name="avatar" accept="image/*" required 
                                       class="block w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 bg-slate-50 border border-slate-200 rounded-xl p-1.5 cursor-pointer">
                                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-xs transition cursor-pointer flex items-center gap-1.5">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                    <span>Avatar Uploaden</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Banner Card -->
                    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4">
                        <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                            <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-panorama text-purple-600"></i>
                                <span>Achtergrond Banner</span>
                            </h3>
                            <span class="text-[11px] text-slate-400 font-medium">Bovenzijde bende profiel</span>
                        </div>

                        @if($myGang->banner)
                        <div class="h-28 w-full rounded-xl overflow-hidden border border-slate-200 shadow-2xs">
                            <img src="{{ $myGang->banner_url }}" alt="Huidige Banner" class="w-full h-full object-cover">
                        </div>
                        @endif

                        <form action="{{ route('gangs.settings') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            <input type="hidden" name="action_type" value="banner">
                            <p class="text-xs text-slate-500">Kies een sfeervolle achtergrondafbeelding die getoond wordt op de profielpagina van je bende.</p>
                            <input type="file" name="banner" accept="image/*" required 
                                   class="block w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 bg-slate-50 border border-slate-200 rounded-xl p-1.5 cursor-pointer">
                            <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-xs transition cursor-pointer flex items-center gap-1.5">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <span>Banner Uploaden</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- 2. BENDE ROLLEN & SLEUTELFUNCTIES -->
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-5">
                    <div class="border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-id-badge text-purple-600"></i>
                            <span>Bende Rollen & Sleutelfuncties</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">
                            Wijs sleutelposities toe aan loyale bendeleden om speciale bevoegdheden en voordelen in de kliniek te activeren.
                        </p>
                    </div>

                    <!-- Information callout banner -->
                    <div class="p-4 rounded-xl bg-purple-50 border border-purple-200 text-xs text-purple-900 space-y-2">
                        <div class="font-bold flex items-center gap-1.5 text-purple-800">
                            <i class="fa-solid fa-circle-info"></i> Sleutelfuncties Toelichting:
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-slate-700 text-[11px]">
                            <li><strong>Bendeleider:</strong> Heeft volledige autoriteit over bende upgrades, oorlogen, bondgenoten en leden.</li>
                            <li><strong>Kantine Smokkelaar:</strong> Beheert de bende-smokkelkiosken en zorgt voor gunstige voorraden.</li>
                            <li><strong>Kasbeheerder:</strong> Houdt toezicht op de kluisdonaties en financiële reserves van de bende.</li>
                        </ul>
                    </div>

                    <form action="{{ route('gangs.settings') }}" method="POST" class="space-y-5">
                        @csrf
                        <input type="hidden" name="action_type" value="management">

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <!-- Bendeleider -->
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                    <i class="fa-solid fa-crown text-amber-500"></i> Bendeleider
                                </label>
                                <select name="boss_babe_id" class="w-full bg-slate-50 border border-slate-200 text-slate-900 rounded-xl px-3 py-2 text-xs font-bold focus:outline-none focus:border-purple-600 focus:bg-white transition">
                                    @foreach($myGang->members as $m)
                                        <option value="{{ $m->id }}" {{ $myGang->leader_character_id === $m->id ? 'selected' : '' }}>
                                            {{ $m->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Kantine Smokkelaar -->
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                    <i class="fa-solid fa-box text-indigo-500"></i> Kantine Smokkelaar
                                </label>
                                <select name="personal_shopper_id" class="w-full bg-slate-50 border border-slate-200 text-slate-900 rounded-xl px-3 py-2 text-xs font-bold focus:outline-none focus:border-purple-600 focus:bg-white transition">
                                    <option value="">Geen aangewezen</option>
                                    @foreach($myGang->members as $m)
                                        <option value="{{ $m->id }}" {{ $myGang->personal_shopper === $m->id ? 'selected' : '' }}>
                                            {{ $m->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Kasbeheerder -->
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                    <i class="fa-solid fa-vault text-emerald-600"></i> Kasbeheerder
                                </label>
                                <select name="money_magnet_id" class="w-full bg-slate-50 border border-slate-200 text-slate-900 rounded-xl px-3 py-2 text-xs font-bold focus:outline-none focus:border-purple-600 focus:bg-white transition">
                                    <option value="">Geen aangewezen</option>
                                    @foreach($myGang->members as $m)
                                        <option value="{{ $m->id }}" {{ $myGang->money_magnet === $m->id ? 'selected' : '' }}>
                                            {{ $m->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs shadow-xs transition cursor-pointer flex items-center gap-2">
                                <i class="fa-solid fa-check"></i>
                                <span>Sleutelfuncties Opslaan</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- 3. TOELATINGSEISEN & VERZOEKEN INSTELLINGEN -->
                <div class="space-y-6">
                    <!-- Lidmaatschapsverzoeken Toggle Card -->
                    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4">
                        <div class="border-b border-slate-100 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                                    <i class="fa-solid fa-user-shield text-purple-600"></i>
                                    <span>Lidmaatschapsverzoeken (Toelating)</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-1">
                                    Bepaal of nieuwe patiënten een verzoek mogen indienen om lid te worden van <strong>{{ $myGang->name }}</strong>.
                                </p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-[11px] font-black self-start sm:self-auto {{ $myGang->is_open ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                <i class="fa-solid {{ $myGang->is_open ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                                {{ $myGang->is_open ? 'Verzoeken Toegestaan (Open)' : 'Verzoeken Uitgeschakeld (Gesloten)' }}
                            </span>
                        </div>

                        <form action="{{ route('gangs.settings') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="action_type" value="requests_toggle">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="flex items-start gap-3 p-4 rounded-xl border cursor-pointer transition {{ $myGang->is_open ? 'border-purple-400 bg-purple-50/40 shadow-2xs' : 'border-slate-200 bg-slate-50 hover:border-slate-300' }}">
                                    <input type="radio" name="is_open" value="1" {{ $myGang->is_open ? 'checked' : '' }} class="mt-0.5 text-purple-600 focus:ring-purple-500">
                                    <div class="text-xs">
                                        <span class="font-extrabold text-slate-900 flex items-center gap-1.5">
                                            <i class="fa-solid fa-door-open text-emerald-600"></i>
                                            <span>Verzoeken Inschakelen (Open)</span>
                                        </span>
                                        <span class="text-slate-500 block text-[11px] mt-1 leading-relaxed">
                                            Spelers kunnen via de benderanglijst een lidmaatschapsverzoek indienen. De leiding kan verzoeken goedkeuren of weigeren.
                                        </span>
                                    </div>
                                </label>

                                <label class="flex items-start gap-3 p-4 rounded-xl border cursor-pointer transition {{ ! $myGang->is_open ? 'border-purple-400 bg-purple-50/40 shadow-2xs' : 'border-slate-200 bg-slate-50 hover:border-slate-300' }}">
                                    <input type="radio" name="is_open" value="0" {{ ! $myGang->is_open ? 'checked' : '' }} class="mt-0.5 text-purple-600 focus:ring-purple-500">
                                    <div class="text-xs">
                                        <span class="font-extrabold text-slate-900 flex items-center gap-1.5">
                                            <i class="fa-solid fa-lock text-rose-500"></i>
                                            <span>Verzoeken Uitschakelen (Gesloten)</span>
                                        </span>
                                        <span class="text-slate-500 block text-[11px] mt-1 leading-relaxed">
                                            Nieuwe patiënten kunnen geen aanvraag indienen. Je bende toont de badge 'Gesloten' in het overzicht.
                                        </span>
                                    </div>
                                </label>
                            </div>

                            <button type="submit" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs shadow-xs transition cursor-pointer flex items-center gap-1.5">
                                <i class="fa-solid fa-check"></i>
                                <span>Verzoek Status Opslaan</span>
                            </button>
                        </form>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Minimaal Respect -->
                        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4">
                            <div class="border-b border-slate-100 pb-3">
                                <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                                    <i class="fa-solid fa-shield-halved text-purple-600"></i>
                                    <span>Toelatingseis (Minimaal Respect)</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-1">
                                    Minimale respectscore die een patiënt moet hebben om een aanvraag te kunnen doen om lid te worden.
                                </p>
                            </div>

                            <form action="{{ route('gangs.settings') }}" method="POST" class="space-y-4">
                                @csrf
                                <input type="hidden" name="action_type" value="min_respect">

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Minimaal vereist respect</label>
                                    <input type="number" name="min_respect" value="{{ $myGang->min_respect ?? 0 }}" min="0" step="100"
                                           class="w-full bg-slate-50 border border-slate-200 text-slate-900 rounded-xl px-3.5 py-2.5 text-xs font-bold focus:outline-none focus:border-purple-600 focus:bg-white transition">
                                </div>

                                <button type="submit" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs shadow-xs transition cursor-pointer flex items-center gap-1.5">
                                    <i class="fa-solid fa-check"></i>
                                    <span>Minimaal Respect Opslaan</span>
                                </button>
                            </form>
                        </div>

                        <!-- Verlatingskosten -->
                        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4" x-data="{ multiplier: {{ min(50, (int) ($myGang->leave_fee_multiplier ?? 48)) }}, hourly: {{ $hourlyWage }} }">
                            <div class="border-b border-slate-100 pb-3">
                                <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                                    <i class="fa-solid fa-door-open text-purple-600"></i>
                                    <span>Verlatingskosten (Vertrekboete)</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-1">
                                    Boete die een bendelid betaalt aan de kluis wanneer hij/zij besluit de factie te verlaten (maximaal 50x het uurloon).
                                </p>
                            </div>

                            <form action="{{ route('gangs.settings') }}" method="POST" class="space-y-4">
                                @csrf
                                <input type="hidden" name="action_type" value="leave_fee">

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Factor van het Uurloon (0 - 50x)</label>
                                    <div class="relative">
                                        <input type="number" name="leave_fee_multiplier" x-model.number="multiplier" min="0" max="50"
                                               class="w-full bg-slate-50 border border-slate-200 text-slate-900 rounded-xl px-3.5 py-2.5 text-xs font-bold focus:outline-none focus:border-purple-600 focus:bg-white transition pr-28">
                                        <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-black text-purple-700 font-mono" 
                                              x-text="'€' + ((multiplier || 0) * hourly).toLocaleString('nl-NL')">
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-1">
                                        Huidig uurloon: €{{ number_format($hourlyWage, 0, ',', '.') }}. Maximaal 50x = €{{ number_format($hourlyWage * 50, 0, ',', '.') }}.
                                    </p>
                                </div>

                                <button type="submit" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs shadow-xs transition cursor-pointer flex items-center gap-1.5">
                                    <i class="fa-solid fa-check"></i>
                                    <span>Verlatingskosten Opslaan</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- 4. BENDE MANIFEST / PROFIELTEKST -->
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-pen-nib text-purple-600"></i>
                            <span>Publiek Bende Manifest & Regels</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">
                            Schrijf het officiële manifest van de bende. Dit wordt getoond op de publieke bende pagina voor alle patiënten in de kliniek.
                        </p>
                    </div>

                    <form action="{{ route('gangs.settings') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="action_type" value="profile">

                        <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-2xs">
                            <!-- Clean BBCode Toolbar -->
                            <div class="flex flex-wrap items-center gap-1 p-2 bg-slate-50 border-b border-slate-200 text-slate-700 text-xs">
                                <button type="button" onclick="insertGangBbcode('[img]', '[/img]')" title="Afbeelding" class="w-7 h-7 rounded-lg hover:bg-slate-200 flex items-center justify-center transition cursor-pointer">
                                    <i class="fa-regular fa-image"></i>
                                </button>
                                <button type="button" onclick="insertGangBbcode('[video]', '[/video]')" title="Video" class="w-7 h-7 rounded-lg hover:bg-slate-200 flex items-center justify-center transition cursor-pointer">
                                    <i class="fa-regular fa-circle-play"></i>
                                </button>
                                <button type="button" onclick="insertGangBbcode('[url]', '[/url]')" title="Link" class="w-7 h-7 rounded-lg hover:bg-slate-200 flex items-center justify-center transition cursor-pointer">
                                    <i class="fa-solid fa-link"></i>
                                </button>
                                <button type="button" onclick="insertGangBbcode('😊', '')" title="Emoji" class="w-7 h-7 rounded-lg hover:bg-slate-200 flex items-center justify-center transition cursor-pointer">
                                    <i class="fa-regular fa-face-smile"></i>
                                </button>
                                <div class="w-px h-4 bg-slate-200 mx-1"></div>
                                <button type="button" onclick="insertGangBbcode('[b]', '[/b]')" title="Vetgedrukt" class="w-7 h-7 font-black rounded-lg hover:bg-slate-200 flex items-center justify-center transition cursor-pointer">
                                    B
                                </button>
                                <button type="button" onclick="insertGangBbcode('[i]', '[/i]')" title="Cursief" class="w-7 h-7 italic font-serif rounded-lg hover:bg-slate-200 flex items-center justify-center transition cursor-pointer">
                                    I
                                </button>
                                <button type="button" onclick="insertGangBbcode('[u]', '[/u]')" title="Onderstreept" class="w-7 h-7 underline rounded-lg hover:bg-slate-200 flex items-center justify-center transition cursor-pointer">
                                    U
                                </button>
                                <button type="button" onclick="insertGangBbcode('[s]', '[/s]')" title="Doorgehaald" class="w-7 h-7 line-through rounded-lg hover:bg-slate-200 flex items-center justify-center transition cursor-pointer">
                                    S
                                </button>
                                <div class="w-px h-4 bg-slate-200 mx-1"></div>
                                <button type="button" onclick="insertGangBbcode('[left]', '[/left]')" title="Links uitlijnen" class="w-7 h-7 rounded-lg hover:bg-slate-200 flex items-center justify-center transition cursor-pointer">
                                    <i class="fa-solid fa-align-left"></i>
                                </button>
                                <button type="button" onclick="insertGangBbcode('[center]', '[/center]')" title="Centreren" class="w-7 h-7 rounded-lg hover:bg-slate-200 flex items-center justify-center transition cursor-pointer">
                                    <i class="fa-solid fa-align-center"></i>
                                </button>
                                <button type="button" onclick="insertGangBbcode('[right]', '[/right]')" title="Rechts uitlijnen" class="w-7 h-7 rounded-lg hover:bg-slate-200 flex items-center justify-center transition cursor-pointer">
                                    <i class="fa-solid fa-align-right"></i>
                                </button>
                            </div>

                            <!-- Textarea -->
                            <textarea id="gangProfileEditor" name="description" rows="8" placeholder="Schrijf hier het manifest van je factie..." 
                                      class="w-full bg-white p-4 text-xs font-medium text-slate-900 placeholder:text-slate-400 focus:outline-none resize-y leading-relaxed">{{ $myGang->description }}</textarea>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs shadow-xs transition cursor-pointer flex items-center gap-2">
                                <i class="fa-solid fa-floppy-disk"></i>
                                <span>Manifest Opslaan</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- TAB: BENDE OORLOG (AANVALLEN) -->
            @if($myGang)
            <div x-show="activeGangTab === 'oorlog'" class="space-y-6">
                <!-- Header Banner -->
                <div class="rounded-2xl overflow-hidden border border-slate-200 shadow-xs bg-white p-6 sm:p-8 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold mb-1">
                                <i class="fa-solid fa-fire text-rose-600"></i> Bende Rivaliteit & Plunderingen
                            </span>
                            <h2 class="text-2xl font-black tracking-tight text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-crosshairs text-rose-600"></i>
                                <span>Bende Oorlog</span>
                            </h2>
                            <p class="text-xs sm:text-sm text-slate-500 max-w-xl leading-relaxed">
                                Zet de opgebouwde <strong>Power (Aanvalskracht)</strong> van je bende in om rivaliserende bendes te overvallen. Breek door hun <strong>Bescherming (Verdediging)</strong> heen om hun kas te plunderen en aanzien te veroveren!
                            </p>
                        </div>

                        <!-- Role authority indicator -->
                        <div class="shrink-0">
                            @if($myGang->isOfficer($character))
                                <div class="px-3.5 py-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-black flex items-center gap-2 shadow-xs">
                                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                    <span>Bevoegd: {{ $officerRoleName ?? 'Bende Beheerder' }}</span>
                                </div>
                            @else
                                <div class="px-3.5 py-2 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-black flex items-center gap-2 shadow-xs">
                                    <i class="fa-solid fa-shield text-amber-600"></i>
                                    <span>Alleen Beheerders kunnen aanvallen</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Battle Stats Bar -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                        <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-amber-100 border border-amber-200 text-amber-700 flex items-center justify-center text-lg font-black shrink-0">
                                <i class="fa-solid fa-bolt"></i>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-slate-500 uppercase block">Jullie Power (Aanval)</span>
                                <span class="text-base font-black text-amber-800 font-mono">{{ number_format($myGang->attack_power ?? 500, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-indigo-100 border border-indigo-200 text-indigo-700 flex items-center justify-center text-lg font-black shrink-0">
                                <i class="fa-solid fa-shield"></i>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-slate-500 uppercase block">Jullie Bescherming (Def)</span>
                                <span class="text-base font-black text-indigo-800 font-mono">{{ number_format($myGang->protection_points ?? 1000, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg {{ $myGang->attackCooldownRemaining() > 0 ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-emerald-100 text-emerald-700 border-emerald-200' }} border flex items-center justify-center text-lg font-black shrink-0">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-slate-500 uppercase block">Aanvalsstatus</span>
                                @if($myGang->attackCooldownRemaining() > 0)
                                    <span class="text-xs font-black text-amber-800 font-mono flex items-center gap-1.5">
                                        <i class="fa-solid fa-stopwatch text-amber-600"></i> Wachten: {{ gmdate('i:s', $myGang->attackCooldownRemaining()) }}
                                    </span>
                                @else
                                    <span class="text-xs font-black text-emerald-700 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Gereed voor aanval!
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rival Gangs Target List -->
                <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-skull-crossbones text-rose-600"></i>
                            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wide">Rivaliserende Bendes</h3>
                        </div>
                        <span class="text-xs font-bold text-slate-400">
                            Kies een doelwit om aan te vallen
                        </span>
                    </div>

                    @php
                        $rivalGangs = $realGangs->where('id', '!=', $myGang->id);
                    @endphp

                    @if($rivalGangs->isEmpty())
                        <div class="text-center py-10 text-slate-400 text-xs">
                            <i class="fa-solid fa-users-slash text-3xl mb-2 block opacity-40"></i>
                            Er zijn momenteel geen andere actieve bendes in de TBS kliniek om aan te vallen.
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($rivalGangs as $rg)
                                @php
                                    $isAlly = $myGang->alliances()->where('ally_gang_id', $rg->id)->exists();
                                    $hasShield = $rg->isUnderShield();
                                    $onCooldown = $myGang->attackCooldownRemaining() > 0;
                                    $canAttack = $myGang->isOfficer($character) && ! $isAlly && ! $hasShield && ! $onCooldown;
                                @endphp
                                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition hover:border-purple-300">
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-lg font-black shrink-0 border border-purple-200">
                                            <i class="fa-solid {{ $rg->emblem ?: 'fa-crown' }}"></i>
                                        </div>
                                        <div class="min-w-0 space-y-0.5">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h4 class="text-sm font-black text-slate-900 truncate">{{ $rg->name }}</h4>
                                                <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 font-extrabold text-[10px] border border-purple-200">
                                                    [{{ $rg->tag }}]
                                                </span>
                                                @if($isAlly)
                                                    <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-extrabold text-[10px] border border-blue-200 inline-flex items-center gap-1">
                                                        <i class="fa-solid fa-handshake text-blue-600 text-[9px]"></i> Bondgenoot
                                                    </span>
                                                @elseif($hasShield)
                                                    <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-800 font-extrabold text-[10px] border border-amber-200 inline-flex items-center gap-1">
                                                        <i class="fa-solid fa-shield text-amber-600 text-[9px]"></i> Schild ({{ gmdate('i:s', $rg->shieldSecondsRemaining()) }})
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-800 font-extrabold text-[10px] border border-emerald-200 inline-flex items-center gap-1">
                                                        <i class="fa-solid fa-crosshairs text-emerald-600 text-[9px]"></i> Kwetsbaar
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-[11px] text-slate-500 flex items-center gap-3 flex-wrap">
                                                <span>Leider: <strong class="text-slate-800">{{ $rg->leader?->name ?? 'Onbekend' }}</strong></span>
                                                <span>&bull;</span>
                                                <span>Kas: <strong class="text-emerald-700 font-mono">€{{ number_format($rg->treasury, 0, ',', '.') }}</strong></span>
                                                <span>&bull;</span>
                                                <span>Verdediging: <strong class="text-indigo-700 font-mono">{{ number_format($rg->protection_points ?? 1000, 0, ',', '.') }}</strong></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="shrink-0 flex items-center justify-end">
                                        <form action="{{ route('gangs.attack') }}" method="POST"
                                              data-confirm="Weet je zeker dat je bende '{{ $rg->name }}' wilt overvallen? Breek door hun verdediging heen om de buit te plunderen!"
                                              data-confirm-title="Bende Oorlog Overval"
                                              data-confirm-btn="Overval Bende"
                                              data-confirm-type="attack"
                                              data-confirm-icon="fa-crosshairs">
                                            @csrf
                                            <input type="hidden" name="target_gang_id" value="{{ $rg->id }}">
                                            <button type="submit" 
                                                    @if(! $canAttack) disabled @endif
                                                    class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-black uppercase tracking-wider transition shadow-xs flex items-center gap-2 cursor-pointer">
                                                <i class="fa-solid fa-crosshairs"></i>
                                                <span>Overval Bende</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- TAB: LOGBOEK -->
            <div x-show="activeGangTab === 'logboek'" class="space-y-6">
                <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-book-bookmark text-blue-600"></i>
                            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wide">Bende Gevechts- & Oorlogslogboek</h3>
                        </div>
                        <span class="text-xs font-bold text-slate-400">
                            Laatste {{ $recentAttacks->count() }} confrontaties
                        </span>
                    </div>

                    @if($recentAttacks->isEmpty())
                        <div class="text-center py-10 text-slate-400 text-xs">
                            <i class="fa-solid fa-shield-halved text-3xl mb-2 block opacity-40"></i>
                            Er hebben nog geen bende-aanvallen plaatsgevonden in de kliniek.
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($recentAttacks as $atk)
                                <div class="p-4 rounded-xl border {{ $atk->won ? 'bg-emerald-50/50 border-emerald-200' : 'bg-rose-50/50 border-rose-200' }} flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="px-2 py-0.5 rounded-md font-black text-[10px] uppercase {{ $atk->won ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                                {{ $atk->won ? 'Overwinning' : 'Afgeslagen' }}
                                            </span>
                                            <strong class="text-slate-900 font-extrabold">{{ $atk->attackerGang?->name ?? 'Onbekend' }}</strong>
                                            <span class="text-slate-400">vs</span>
                                            <strong class="text-slate-900 font-extrabold">{{ $atk->targetGang?->name ?? 'Onbekend' }}</strong>
                                            <span class="text-slate-400 text-[11px]">&bull; Bevolen door <strong>{{ $atk->character?->name ?? 'Leiding' }}</strong></span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 leading-relaxed font-medium">
                                            {{ $atk->summary }}
                                        </p>
                                    </div>
                                    <div class="shrink-0 text-right font-mono text-[11px] text-slate-400">
                                        {{ $atk->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
    </div>
    </div>

    <!-- CREATE GANG MODAL (25 STARS) -->
    <div x-show="showCreateModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white border border-slate-200 rounded-2xl p-6 max-w-md w-full text-slate-900 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wide">Nieuwe Bende Oprichten</h3>
                <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-700 text-lg cursor-pointer">&times;</button>
            </div>
            @if($myGang)
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-2">
                    <p class="font-extrabold flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                        <span>Je bent al lid van een bende!</span>
                    </p>
                    <p class="leading-relaxed">
                        Je bent momenteel aangesloten bij <strong>{{ $myGang->name }}</strong> [{{ $myGang->tag }}]. Je kunt maximaal in 1 bende tegelijk zitten. Om een nieuwe bende op te richten, moet je eerst je huidige bende verlaten via het tabblad <em>Mijn Gang</em>.
                    </p>
                </div>
                <div class="flex justify-end pt-2">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold cursor-pointer">Sluiten</button>
                </div>
            @else
                <p class="text-xs text-slate-500 font-medium">
                    Kosten: <strong class="text-amber-600 inline-flex items-center gap-1">25 Sterren <i class="fa-solid fa-star text-amber-500 text-xs"></i></strong> (Je hebt er {{ $character->stars ?? 0 }}).
                </p>
                <form action="{{ route('gangs.create') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Bende Naam</label>
                        <input type="text" name="name" required placeholder="bijv. NoMercy" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 focus:outline-none focus:border-purple-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tag (2-4 letters)</label>
                        <input type="text" name="tag" required placeholder="bijv. NM" maxlength="4" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 uppercase focus:outline-none focus:border-purple-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Specialiteit</label>
                        <input type="text" name="specialty" placeholder="Kantine Controle" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 focus:outline-none focus:border-purple-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Beschrijving</label>
                        <textarea name="description" rows="2" class="w-full bg-white border border-slate-300 rounded-lg p-2.5 text-xs font-medium text-slate-900 focus:outline-none focus:border-purple-600"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold cursor-pointer">Annuleren</button>
                        <button type="submit" @if(($character->stars ?? 0) < 25) disabled @endif class="px-5 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 disabled:opacity-40 text-white font-bold text-xs uppercase cursor-pointer inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-star text-amber-300 text-xs"></i>
                            <span>Oprichten (25 Sterren)</span>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <!-- Bottom Podium Ranglijst -->
    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-5">
        <div class="flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-3">
            <i class="fa-solid fa-trophy text-amber-500"></i>
            <span>RANGLIJST TOP PATIËNTEN</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 lg:grid-cols-10 gap-3 pt-2">
            @foreach($topRankings ?? [] as $index => $player)
            <div class="flex flex-col items-center text-center group">
                <div class="relative mb-2">
                    <img src="{{ $player->avatar_url }}" alt="{{ $player->name }}" 
                         class="w-14 h-14 sm:w-16 sm:h-16 rounded-full object-cover border-2 {{ $index === 0 ? 'border-amber-400 ring-2 ring-amber-400/30' : ($index === 1 ? 'border-purple-400' : 'border-slate-200') }} group-hover:scale-105 transition-transform shadow-xs">
                    
                    <span class="absolute -top-1 -left-1 w-6 h-6 rounded-full {{ $index === 0 ? 'bg-amber-400 text-slate-950 font-black' : ($index < 3 ? 'bg-purple-600 text-white font-bold' : 'bg-slate-800 text-white font-bold') }} text-xs flex items-center justify-center shadow">
                        {{ $index + 1 }}
                    </span>

                    <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 text-white text-[9px] flex items-center justify-center">
                        <i class="fa-solid fa-check"></i>
                    </span>
                </div>

                <a href="{{ route('profile.show', $player->name) }}" class="text-xs font-bold text-slate-900 group-hover:text-purple-600 transition truncate w-full block">
                    {{ $player->name }}
                </a>
                <span class="text-[10px] font-bold text-slate-400">
                    {{ $player->respect >= 1000000 ? number_format($player->respect / 1000000, 2) . 'M' : ($player->respect >= 1000 ? number_format($player->respect / 1000, 1) . 'K' : $player->respect) }}
                </span>
            </div>
            @endforeach
        </div>
    </div>

</div>

<script>
function insertGangBbcode(tagStart, tagEnd) {
    const textarea = document.getElementById('gangProfileEditor');
    if (!textarea) return;
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    const selected = text.substring(start, end);
    const replacement = tagStart + (selected || '') + tagEnd;
    textarea.value = text.substring(0, start) + replacement + text.substring(end);
    textarea.focus();
    textarea.setSelectionRange(start + tagStart.length, start + tagStart.length + selected.length);
}
</script>
@endsection
