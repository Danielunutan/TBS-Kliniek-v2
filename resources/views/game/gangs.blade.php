@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ 
    activeGangTab: '{{ $activeTab ?? session('active_gang_tab', request('tab', ($myGang ? 'mijn_gang' : 'alle_bendes'))) }}',
    beheerTab: '{{ session('beheer_tab', request('beheer_tab', 'leden')) }}',
    showCreateModal: false,
    attackCooldown: {{ (int) ($myGang ? $myGang->attackCooldownRemaining() : 0) }},
    formatTime(s) {
        if (s <= 0) return '00:00';
        const m = Math.floor(s / 60);
        const sec = Math.floor(s % 60);
        return (m < 10 ? '0' : '') + m + ':' + (sec < 10 ? '0' : '') + sec;
    },
    init() {
        if (!'{{ session('active_gang_tab') }}' && !'{{ request('tab') }}' && sessionStorage.getItem('tbs_active_gang_tab')) {
            this.activeGangTab = sessionStorage.getItem('tbs_active_gang_tab');
        }
        if (!'{{ session('beheer_tab') }}' && !'{{ request('beheer_tab') }}' && sessionStorage.getItem('tbs_gang_beheer_tab')) {
            this.beheerTab = sessionStorage.getItem('tbs_gang_beheer_tab');
        }
        if (this.attackCooldown > 0) {
            const timer = setInterval(() => {
                if (this.attackCooldown > 0) this.attackCooldown--;
                if (this.attackCooldown <= 0) clearInterval(timer);
            }, 1000);
        }
    },
    setGangTab(tab) {
        this.activeGangTab = tab;
        sessionStorage.setItem('tbs_active_gang_tab', tab);
    },
    setBeheerTab(tab) {
        this.beheerTab = tab;
        sessionStorage.setItem('tbs_gang_beheer_tab', tab);
    }
}">

    <!-- Top Hero Banner (Rich TBS Kliniek Style - Hidden on Mijn Gang since it has full profile banner) -->
    <div x-show="activeGangTab !== 'mijn_gang'" class="bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-6 shadow-xs relative overflow-hidden">
        @if($myGang)
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 relative z-10">
            <div class="flex items-center gap-3.5 sm:gap-4">
                <div class="relative shrink-0">
                    <img src="{{ $myGang->avatar_url }}" alt="{{ $myGang->name }}" 
                         class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl object-cover border-2 border-purple-500/40 shadow-sm">
                    <span class="absolute -bottom-1 -right-1 px-1.5 py-0.5 rounded-md bg-purple-600 text-white font-mono text-[9px] font-black uppercase shadow-2xs">
                        {{ $myGang->tag }}
                    </span>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-1.5 mb-1">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-purple-50 dark:bg-purple-950/70 border border-purple-200 dark:border-purple-800 text-[10px] font-extrabold uppercase tracking-wider text-purple-700 dark:text-purple-300">
                            <i class="fa-solid fa-users text-purple-600 dark:text-purple-400"></i> Factie
                        </span>
                        @php
                            $myRoleBadge = 'Bendelid';
                            $myRoleIcon = 'fa-user';
                            $myRoleClass = 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-200 dark:border-slate-700';
                            if ($character->gang_role === 'leader') {
                                $myRoleBadge = 'Bendeleider';
                                $myRoleIcon = 'fa-crown text-amber-500';
                                $myRoleClass = 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800';
                            } elseif ((int) $myGang->personal_shopper === (int) $character->id && (int) $myGang->money_magnet === (int) $character->id) {
                                $myRoleBadge = 'Kantine & Kas (Co-Leider)';
                                $myRoleIcon = 'fa-shield-halved text-purple-600 dark:text-purple-400';
                                $myRoleClass = 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800';
                            } elseif ((int) $myGang->personal_shopper === (int) $character->id) {
                                $myRoleBadge = 'Kantine Smokkelaar (Co-Leider)';
                                $myRoleIcon = 'fa-box text-indigo-500';
                                $myRoleClass = 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800';
                            } elseif ((int) $myGang->money_magnet === (int) $character->id) {
                                $myRoleBadge = 'Kasbeheerder (Co-Leider)';
                                $myRoleIcon = 'fa-vault text-emerald-600 dark:text-emerald-400';
                                $myRoleClass = 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800';
                            } elseif ($character->gang_role === 'co_leader') {
                                $myRoleBadge = 'Co-Leider';
                                $myRoleIcon = 'fa-star text-purple-600 dark:text-purple-400';
                                $myRoleClass = 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800';
                            }
                        @endphp
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $myRoleClass }}">
                            <i class="fa-solid {{ $myRoleIcon }} text-[9px]"></i>
                            <span>{{ $myRoleBadge }}</span>
                        </span>
                        @if(($onlineMembersCount ?? 0) > 0)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>{{ $onlineMembersCount ?? 0 }} online</span>
                            </span>
                        @endif
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        <span>{{ $myGang->name }}</span>
                        <span class="text-sm font-extrabold text-purple-600 dark:text-purple-400 font-mono">[{{ $myGang->tag }}]</span>
                    </h1>
                </div>
            </div>

            <!-- Vitals Pills Bar -->
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:flex items-center gap-2 shrink-0">
                <div class="bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800/70 rounded-xl px-3 py-1.5 text-center">
                    <span class="text-[9px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-400 block">Kluis</span>
                    <span class="text-xs sm:text-sm font-black text-emerald-800 dark:text-emerald-300 font-mono">€{{ number_format($gangTreasury ?? $myGang->treasury, 0, ',', '.') }}</span>
                </div>
                <div class="bg-purple-50 dark:bg-purple-950/50 border border-purple-200 dark:border-purple-800/70 rounded-xl px-3 py-1.5 text-center">
                    <span class="text-[9px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-400 block">Respect</span>
                    <span class="text-xs sm:text-sm font-black text-purple-800 dark:text-purple-300 font-mono">{{ number_format($totalRespect ?? $myGang->respect, 0, ',', '.') }}</span>
                </div>
                <div class="bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800/70 rounded-xl px-3 py-1.5 text-center">
                    <span class="text-[9px] font-black uppercase tracking-wider text-amber-700 dark:text-amber-400 block">Uurloon</span>
                    <span class="text-xs sm:text-sm font-black text-amber-800 dark:text-amber-300 font-mono">+€{{ number_format($hourlyWage ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 text-center">
                    <span class="text-[9px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Leden</span>
                    <span class="text-xs sm:text-sm font-black text-slate-800 dark:text-slate-200 font-mono">{{ $myGang->members->count() }}/{{ $myGang->max_members ?? 10 }}</span>
                </div>
            </div>
        </div>
        @else
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
            <div>
                <div class="inline-flex items-center gap-2 bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 text-xs font-bold px-3 py-1 rounded-full border border-purple-200 dark:border-purple-800/60 mb-2">
                    <i class="fa-solid fa-users"></i> Facties & Allianties
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Kliniek Bendes</h1>
                <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-1 max-w-xl leading-relaxed">
                    Verenig je met andere patiënten in een bende. Doneer aan de gezamenlijke kas, verhoog het collectieve uurloon, koop smokkelkiosken en heers over de kliniek!
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="px-3.5 py-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-xs font-bold text-amber-800 dark:text-amber-300 flex items-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-star text-amber-600 dark:text-amber-400"></i> {{ $character->stars ?? 0 }} Sterren
                </span>
                <button type="button" @click="showCreateModal = true" 
                        class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs uppercase tracking-wider transition shadow-xs cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-plus"></i> Bende Oprichten
                </button>
            </div>
        </div>
        @endif
    </div>

    @php
        $myLeaveMultiplier = $myGang ? min(50, max(0, (int) ($myGang->leave_fee_multiplier ?? 0))) : 0;
        $myLeaveFee = ($myGang && $character->gang_role !== 'leader') ? ($hourlyWage * $myLeaveMultiplier) : 0;
        $hasEnoughCashToLeave = (int) $character->cash >= $myLeaveFee;
    @endphp

    <!-- ========================================================================================= -->
    <!-- 🔥 BENDE COMMAND CENTER: HORIZONTALE COMMAND BAR (DESKTOP & MOBIEL FULL-WIDTH)              -->
    <!-- ========================================================================================= -->
    <div class="bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
        <!-- Bovenste Status & Quick Action Balk -->
        <div class="px-4 py-3 bg-slate-50/70 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-800/80 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-950/70 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm shadow-2xs font-black">
                    <i class="fa-solid fa-users-viewfinder"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs sm:text-sm font-black uppercase tracking-wide text-slate-900 dark:text-white">Bende Command Center</span>
                        @if($myGang)
                            <span class="px-2 py-0.5 rounded-md bg-purple-600 text-white font-mono text-[10px] font-black uppercase shadow-2xs">
                                [{{ $myGang->tag }}]
                            </span>
                        @endif
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 font-medium flex items-center gap-2">
                        @if($myGang)
                            <span>Factie: <strong class="text-slate-800 dark:text-slate-200 font-bold">{{ $myGang->name }}</strong></span>
                            <span>•</span>
                            <span class="text-purple-600 dark:text-purple-400 font-bold">{{ $myRoleBadge }}</span>
                        @else
                            <span>Geen actieve bende aangesloten</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Snelle Factie Vitals / Acties Rechts -->
            <div class="flex items-center gap-2">
                @if($myGang)
                    <!-- Live Kluis/Kas Pill -->
                    <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/60 text-emerald-700 dark:text-emerald-300 text-xs font-black" title="Bende Kluis Saldo">
                        <i class="fa-solid fa-vault text-emerald-500"></i>
                        <span>€{{ number_format($myGang->bank ?? 0, 0, ',', '.') }}</span>
                    </span>

                    <!-- Online Leden Pill -->
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold" title="Leden online">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>{{ $onlineMembersCount ?? 0 }} Online</span>
                    </span>

                    <!-- Bende Verlaten Knop (Discreet & Beveiligd) -->
                    <form action="{{ route('gangs.leave') }}" method="POST"
                          onsubmit="return confirm('{{ $character->gang_role === 'leader' ? ($myGang->members->count() > 1 ? 'Weet je zeker dat je de bende wilt verlaten? Het leiderschap wordt overgedragen aan een ander bendelid.' : 'Weet je zeker dat je de bende wilt verlaten? Omdat je het enige lid bent, wordt de bende definitief opgeheven!') : ($myLeaveFee > 0 ? 'Weet je zeker dat je ' . addslashes($myGang->name) . ' wilt verlaten? De vertrekboete bedraagt €' . number_format($myLeaveFee, 0, ',', '.') . ' (' . $myLeaveMultiplier . 'x uurloon) en wordt overgemaakt naar de bende kluis.' : 'Weet je zeker dat je ' . addslashes($myGang->name) . ' wilt verlaten?') }}');">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 border border-transparent hover:border-rose-200 dark:hover:border-rose-900 transition cursor-pointer"
                                title="Bende verlaten">
                            <i class="fa-solid fa-arrow-right-from-bracket text-rose-500 text-[11px]"></i>
                            <span class="hidden md:inline">Verlaten</span>
                        </button>
                    </form>
                @else
                    <button type="button" @click="showCreateModal = true" 
                            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs shadow-xs transition cursor-pointer">
                        <i class="fa-solid fa-plus"></i>
                        <span>Bende Oprichten (25 ⭐)</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- Horizontale Navigatie Tabs (2 Strakke, Georganiseerde Rijen) -->
        <div class="px-2.5 sm:px-3.5 py-2.5 bg-slate-50/80 dark:bg-slate-900/60 border-t border-slate-100 dark:border-slate-800/80 flex flex-col gap-1.5">
            @if($myGang)
            <!-- Rij 1: Bende Basis & Operaties -->
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5" style="-webkit-overflow-scrolling: touch;">
                <!-- 1. Mijn Bende -->
                <button type="button" @click="setGangTab('mijn_gang')" 
                        :class="activeGangTab === 'mijn_gang' ? 'bg-purple-600 text-white shadow-xs border-purple-600 font-black' : 'bg-white dark:bg-slate-800/90 text-slate-700 dark:text-slate-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-purple-50/70 dark:hover:bg-purple-950/40 border-slate-200/80 dark:border-slate-700/60 hover:border-purple-300 dark:hover:border-purple-700 font-bold shadow-2xs'"
                        class="h-8 px-3 rounded-lg text-xs shrink-0 inline-flex items-center gap-1.5 border transition-all duration-150 cursor-pointer group">
                    <i class="fa-solid fa-shield-halved text-xs" :class="activeGangTab === 'mijn_gang' ? 'text-white' : 'text-purple-600 dark:text-purple-400 group-hover:scale-110 transition-transform'"></i>
                    <span>Mijn Bende</span>
                </button>

                <!-- 2. Ledenlijst -->
                <button type="button" @click="setGangTab('leden')" 
                        :class="activeGangTab === 'leden' ? 'bg-purple-600 text-white shadow-xs border-purple-600 font-black' : 'bg-white dark:bg-slate-800/90 text-slate-700 dark:text-slate-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-purple-50/70 dark:hover:bg-purple-950/40 border-slate-200/80 dark:border-slate-700/60 hover:border-purple-300 dark:hover:border-purple-700 font-bold shadow-2xs'"
                        class="h-8 px-3 rounded-lg text-xs shrink-0 inline-flex items-center gap-1.5 border transition-all duration-150 cursor-pointer group">
                    <i class="fa-solid fa-users text-xs" :class="activeGangTab === 'leden' ? 'text-white' : 'text-purple-600 dark:text-purple-400 group-hover:scale-110 transition-transform'"></i>
                    <span>Leden</span>
                    <span class="inline-flex items-center justify-center px-1.5 h-4 min-w-[18px] rounded-full text-[10px] font-mono font-black" :class="activeGangTab === 'leden' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'">
                        {{ $myGang->members->count() }}
                    </span>
                </button>

                <!-- 3. Oorlog & Aanval -->
                <button type="button" @click="setGangTab('oorlog')" 
                        :class="activeGangTab === 'oorlog' ? 'bg-rose-600 text-white shadow-xs border-rose-600 font-black' : 'bg-white dark:bg-slate-800/90 text-slate-700 dark:text-slate-300 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50/70 dark:hover:bg-rose-950/40 border-slate-200/80 dark:border-slate-700/60 hover:border-rose-300 dark:hover:border-rose-700 font-bold shadow-2xs'"
                        class="h-8 px-3 rounded-lg text-xs shrink-0 inline-flex items-center gap-1.5 border transition-all duration-150 cursor-pointer group">
                    <i class="fa-solid fa-crosshairs text-xs" :class="activeGangTab === 'oorlog' ? 'text-white' : 'text-rose-500 group-hover:scale-110 transition-transform'"></i>
                    <span>Oorlog</span>
                    <span x-show="attackCooldown > 0" class="inline-flex items-center gap-1 px-1.5 h-4 rounded-md text-[10px] font-mono font-extrabold" :class="activeGangTab === 'oorlog' ? 'bg-white/20 text-white' : 'bg-amber-500/15 text-amber-600 dark:text-amber-400'">
                        <i class="fa-solid fa-stopwatch text-[9px]"></i> <span x-text="formatTime(attackCooldown)"></span>
                    </span>
                    <span x-show="attackCooldown <= 0" class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                </button>

                <!-- 4. Panden -->
                <a href="{{ route('gangs.panden') }}" 
                   class="h-8 px-3 rounded-lg text-xs font-bold shrink-0 inline-flex items-center gap-1.5 bg-white dark:bg-slate-800/90 text-slate-700 dark:text-slate-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-purple-50/70 dark:hover:bg-purple-950/40 border border-slate-200/80 dark:border-slate-700/60 hover:border-purple-300 dark:hover:border-purple-700 shadow-2xs transition-all duration-150 group">
                    <i class="fa-solid fa-building text-purple-500 group-hover:scale-110 transition-transform text-xs"></i>
                    <span>Panden</span>
                    <span class="inline-flex items-center justify-center px-1.5 h-4 min-w-[18px] rounded-full text-[10px] font-mono font-black bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300">
                        {{ $myGang->properties->count() }}
                    </span>
                </a>

                <!-- 5. Garage -->
                <a href="{{ route('gangs.garage') }}" 
                   class="h-8 px-3 rounded-lg text-xs font-bold shrink-0 inline-flex items-center gap-1.5 bg-white dark:bg-slate-800/90 text-slate-700 dark:text-slate-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-purple-50/70 dark:hover:bg-purple-950/40 border border-slate-200/80 dark:border-slate-700/60 hover:border-purple-300 dark:hover:border-purple-700 shadow-2xs transition-all duration-150 group"
                   title="{{ $myGang->storedVehicles->count() }}/{{ $myGang->total_vehicle_slots }} voertuigen gestald">
                    <i class="fa-solid fa-warehouse text-purple-500 group-hover:scale-110 transition-transform text-xs"></i>
                    <span>Garage</span>
                    <span class="inline-flex items-center justify-center px-1.5 h-4 min-w-[18px] rounded-full text-[10px] font-mono font-black bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300">
                        {{ $myGang->storedVehicles->count() }}
                    </span>
                </a>

                <!-- 6. Bende Heists -->
                <a href="{{ route('heists.index', ['tab' => 'gang']) }}" 
                   class="h-8 px-3 rounded-lg text-xs font-bold shrink-0 inline-flex items-center gap-1.5 bg-white dark:bg-slate-800/90 text-slate-700 dark:text-slate-300 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50/70 dark:hover:bg-rose-950/40 border border-slate-200/80 dark:border-slate-700/60 hover:border-rose-300 dark:hover:border-rose-700 shadow-2xs transition-all duration-150 group"
                   title="Bende Heists (Co-Op)">
                    <i class="fa-solid fa-masks-theater text-rose-500 group-hover:scale-110 transition-transform text-xs"></i>
                    <span>Heists</span>
                </a>

                <!-- 7. Depot -->
                <button type="button" @click="setGangTab('depot')" 
                        :class="activeGangTab === 'depot' ? 'bg-purple-600 text-white shadow-xs border-purple-600 font-black' : 'bg-white dark:bg-slate-800/90 text-slate-700 dark:text-slate-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-purple-50/70 dark:hover:bg-purple-950/40 border-slate-200/80 dark:border-slate-700/60 hover:border-purple-300 dark:hover:border-purple-700 font-bold shadow-2xs'"
                        class="h-8 px-3 rounded-lg text-xs shrink-0 inline-flex items-center gap-1.5 border transition-all duration-150 cursor-pointer group"
                        title="{{ $myGang->getStorageUsed() }}/{{ $myGang->total_storage_capacity }} opslag capaciteit gebruikt">
                    <i class="fa-solid fa-boxes-stacked text-xs" :class="activeGangTab === 'depot' ? 'text-white' : 'text-amber-500 group-hover:scale-110 transition-transform'"></i>
                    <span>Depot</span>
                    <span class="inline-flex items-center justify-center px-1.5 h-4 min-w-[18px] rounded-full text-[10px] font-mono font-black" :class="activeGangTab === 'depot' ? 'bg-white/20 text-white' : 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300'">
                        {{ $myGang->getStorageUsed() }}
                    </span>
                </button>
            </div>

            <!-- Rij 2: Acties, Financiën, Beheer & Overzichten -->
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pt-1.5 border-t border-slate-200/60 dark:border-slate-800/60" style="-webkit-overflow-scrolling: touch;">
                <!-- 8. Doneren -->
                <button type="button" @click="setGangTab('doneren')" 
                        :class="activeGangTab === 'doneren' ? 'bg-emerald-600 text-white shadow-xs border-emerald-600 font-black' : 'bg-white dark:bg-slate-800/90 text-slate-700 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-emerald-50/70 dark:hover:bg-emerald-950/40 border-slate-200/80 dark:border-slate-700/60 hover:border-emerald-300 dark:hover:border-emerald-700 font-bold shadow-2xs'"
                        class="h-8 px-3 rounded-lg text-xs shrink-0 inline-flex items-center gap-1.5 border transition-all duration-150 cursor-pointer group">
                    <i class="fa-solid fa-circle-plus text-xs" :class="activeGangTab === 'doneren' ? 'text-white' : 'text-emerald-500 group-hover:scale-110 transition-transform'"></i>
                    <span>Doneren</span>
                </button>

                <!-- 9. Winkel -->
                <button type="button" @click="setGangTab('winkel')" 
                        :class="activeGangTab === 'winkel' ? 'bg-purple-600 text-white shadow-xs border-purple-600 font-black' : 'bg-white dark:bg-slate-800/90 text-slate-700 dark:text-slate-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-purple-50/70 dark:hover:bg-purple-950/40 border-slate-200/80 dark:border-slate-700/60 hover:border-purple-300 dark:hover:border-purple-700 font-bold shadow-2xs'"
                        class="h-8 px-3 rounded-lg text-xs shrink-0 inline-flex items-center gap-1.5 border transition-all duration-150 cursor-pointer group">
                    <i class="fa-solid fa-store text-xs" :class="activeGangTab === 'winkel' ? 'text-white' : 'text-purple-500 group-hover:scale-110 transition-transform'"></i>
                    <span>Winkel</span>
                </button>

                <!-- 10. Beheer (Officers only) -->
                @if($myGang->isOfficer($character))
                <button type="button" @click="setGangTab('beheer')" 
                        :class="activeGangTab === 'beheer' ? 'bg-purple-600 text-white shadow-xs border-purple-600 font-black' : 'bg-white dark:bg-slate-800/90 text-slate-700 dark:text-slate-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-purple-50/70 dark:hover:bg-purple-950/40 border-slate-200/80 dark:border-slate-700/60 hover:border-purple-300 dark:hover:border-purple-700 font-bold shadow-2xs'"
                        class="h-8 px-3 rounded-lg text-xs shrink-0 inline-flex items-center gap-1.5 border transition-all duration-150 cursor-pointer group">
                    <i class="fa-solid fa-users-gear text-xs" :class="activeGangTab === 'beheer' ? 'text-white' : 'text-purple-500 group-hover:scale-110 transition-transform'"></i>
                    <span>Beheer</span>
                    @if($myGang->requests->where('status', 'pending')->count() > 0)
                        <span class="inline-flex items-center justify-center px-1.5 h-4 min-w-[18px] rounded-full bg-rose-500 text-white text-[9px] font-mono font-black animate-pulse">
                            {{ $myGang->requests->where('status', 'pending')->count() }}
                        </span>
                    @endif
                </button>

                <!-- 11. Instellingen -->
                <button type="button" @click="setGangTab('instellingen')" 
                        :class="activeGangTab === 'instellingen' ? 'bg-purple-600 text-white shadow-xs border-purple-600 font-black' : 'bg-white dark:bg-slate-800/90 text-slate-700 dark:text-slate-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-purple-50/70 dark:hover:bg-purple-950/40 border-slate-200/80 dark:border-slate-700/60 hover:border-purple-300 dark:hover:border-purple-700 font-bold shadow-2xs'"
                        class="h-8 px-3 rounded-lg text-xs shrink-0 inline-flex items-center gap-1.5 border transition-all duration-150 cursor-pointer group">
                    <i class="fa-solid fa-sliders text-xs" :class="activeGangTab === 'instellingen' ? 'text-white' : 'text-slate-400 group-hover:scale-110 transition-transform'"></i>
                    <span>Instellingen</span>
                </button>
                @endif

                <!-- 12. Alle Bendes -->
                <button type="button" @click="setGangTab('alle_bendes')" 
                        :class="activeGangTab === 'alle_bendes' ? 'bg-purple-600 text-white shadow-xs border-purple-600 font-black' : 'bg-white dark:bg-slate-800/90 text-slate-700 dark:text-slate-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-purple-50/70 dark:hover:bg-purple-950/40 border-slate-200/80 dark:border-slate-700/60 hover:border-purple-300 dark:hover:border-purple-700 font-bold shadow-2xs'"
                        class="h-8 px-3 rounded-lg text-xs shrink-0 inline-flex items-center gap-1.5 border transition-all duration-150 cursor-pointer group">
                    <i class="fa-solid fa-globe text-xs" :class="activeGangTab === 'alle_bendes' ? 'text-white' : 'text-purple-500 group-hover:scale-110 transition-transform'"></i>
                    <span>Alle Bendes</span>
                </button>

                <!-- 13. Ranglijst -->
                <button type="button" @click="setGangTab('ranglijst')" 
                        :class="activeGangTab === 'ranglijst' ? 'bg-purple-600 text-white shadow-xs border-purple-600 font-black' : 'bg-white dark:bg-slate-800/90 text-slate-700 dark:text-slate-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-purple-50/70 dark:hover:bg-purple-950/40 border-slate-200/80 dark:border-slate-700/60 hover:border-purple-300 dark:hover:border-purple-700 font-bold shadow-2xs'"
                        class="h-8 px-3 rounded-lg text-xs shrink-0 inline-flex items-center gap-1.5 border transition-all duration-150 cursor-pointer group">
                    <i class="fa-solid fa-trophy text-xs" :class="activeGangTab === 'ranglijst' ? 'text-white' : 'text-amber-500 group-hover:scale-110 transition-transform'"></i>
                    <span>Ranglijst</span>
                </button>

                <!-- 14. Logboek -->
                <button type="button" @click="setGangTab('logboek')" 
                        :class="activeGangTab === 'logboek' ? 'bg-purple-600 text-white shadow-xs border-purple-600 font-black' : 'bg-white dark:bg-slate-800/90 text-slate-700 dark:text-slate-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-purple-50/70 dark:hover:bg-purple-950/40 border-slate-200/80 dark:border-slate-700/60 hover:border-purple-300 dark:hover:border-purple-700 font-bold shadow-2xs'"
                        class="h-8 px-3 rounded-lg text-xs shrink-0 inline-flex items-center gap-1.5 border transition-all duration-150 cursor-pointer group">
                    <i class="fa-solid fa-book-bookmark text-xs" :class="activeGangTab === 'logboek' ? 'text-white' : 'text-blue-500 group-hover:scale-110 transition-transform'"></i>
                    <span>Logboek</span>
                </button>
            </div>
            @else
            <!-- Spelers zonder bende: Alle Bendes & Ranglijst -->
            <div class="flex items-center gap-1.5">
                <button type="button" @click="setGangTab('alle_bendes')" 
                        :class="activeGangTab === 'alle_bendes' ? 'bg-purple-600 text-white shadow-xs border-purple-600 font-black' : 'bg-white dark:bg-slate-800/90 text-slate-700 dark:text-slate-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-purple-50/70 dark:hover:bg-purple-950/40 border-slate-200/80 dark:border-slate-700/60 hover:border-purple-300 dark:hover:border-purple-700 font-bold shadow-2xs'"
                        class="h-8 px-3 rounded-lg text-xs shrink-0 inline-flex items-center gap-1.5 border transition-all duration-150 cursor-pointer group">
                    <i class="fa-solid fa-globe text-xs" :class="activeGangTab === 'alle_bendes' ? 'text-white' : 'text-purple-500 group-hover:scale-110 transition-transform'"></i>
                    <span>Alle Bendes</span>
                </button>

                <button type="button" @click="setGangTab('ranglijst')" 
                        :class="activeGangTab === 'ranglijst' ? 'bg-purple-600 text-white shadow-xs border-purple-600 font-black' : 'bg-white dark:bg-slate-800/90 text-slate-700 dark:text-slate-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-purple-50/70 dark:hover:bg-purple-950/40 border-slate-200/80 dark:border-slate-700/60 hover:border-purple-300 dark:hover:border-purple-700 font-bold shadow-2xs'"
                        class="h-8 px-3 rounded-lg text-xs shrink-0 inline-flex items-center gap-1.5 border transition-all duration-150 cursor-pointer group">
                    <i class="fa-solid fa-trophy text-xs" :class="activeGangTab === 'ranglijst' ? 'text-white' : 'text-amber-500 group-hover:scale-110 transition-transform'"></i>
                    <span>Ranglijst</span>
                </button>
            </div>
            @endif
        </div>
    </div>


    <!-- Banners voor Spelers Zonder Bende -->
    @if(!$character->gang_id)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-xs flex items-center justify-between gap-4">
            <div class="space-y-1">
                <span class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                    <i class="fa-solid fa-crown text-amber-500"></i> Bende Oprichten
                </span>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium leading-relaxed">
                    Start je eigen syndicaat en domineer de TBS kliniek met je bendeleden.
                </p>
            </div>
            <button type="button" @click="showCreateModal = true" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shrink-0 transition shadow-xs cursor-pointer inline-flex items-center gap-1.5">
                <i class="fa-solid fa-star text-amber-300 text-xs"></i>
                <span>25 Sterren</span>
            </button>
        </div>

        <div class="bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 rounded-2xl p-4 sm:p-5 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-300 flex items-center justify-center shrink-0 text-base">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
            <div class="space-y-0.5">
                <span class="text-xs font-black text-emerald-900 dark:text-emerald-300 uppercase tracking-wider block">
                    TBS Patiënten Uitkering
                </span>
                <p class="text-xs text-emerald-800 dark:text-emerald-200 font-medium leading-relaxed">
                    Zonder bende ontvang je automatisch elk kwartier <strong class="font-bold">€1.000</strong> direct op je bank!
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Content Area: Tabs over Volle Breedte -->
    <div class="w-full space-y-6">

            <!-- TAB 1: MIJN GANG (RICH PUBLIC PROFILE STYLE) -->
            @if($myGang)
            <div x-show="activeGangTab === 'mijn_gang'" class="space-y-6" x-data="{
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

                <!-- Top Hero Banner Card (High-Contrast, Readable in Both Light and Dark Mode) -->
                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs">
                    <div class="min-h-[220px] sm:min-h-[240px] md:h-64 w-full overflow-hidden relative bg-purple-950 flex flex-col justify-between p-4 sm:p-6">
                        <img src="{{ $myGang->banner_url }}" alt="{{ $myGang->name }} Banner" class="absolute inset-0 w-full h-full object-cover">
                        <!-- Dark gradient overlay ensuring text on banner is ALWAYS 100% crystal clear in both light and dark modes -->
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-slate-950/50 to-black/30 pointer-events-none"></div>

                        <!-- Top Right Action Controls -->
                        <div class="relative z-10 flex items-center justify-end gap-2">
                            <button type="button" 
                                    @click="window.tbsOpenReportModal({ type: 'gang', id: {{ $myGang->id }}, name: '{{ addslashes($myGang->name) }}' })"
                                    class="px-3 py-1.5 rounded-xl bg-black/60 hover:bg-rose-900/80 text-white text-xs font-bold border border-white/20 shadow-xs flex items-center gap-1.5 backdrop-blur-xs transition cursor-pointer"
                                    title="Rapporteer deze bende bij moderatie">
                                <i class="fa-solid fa-flag text-rose-400"></i>
                                <span class="hidden sm:inline">Rapporteer</span>
                            </button>
                            <form action="{{ route('gangs.leave') }}" method="POST"
                                  onsubmit="return confirm('{{ $character->gang_role === 'leader' ? ($myGang->members->count() > 1 ? 'Weet je zeker dat je de bende wilt verlaten? Het leiderschap wordt overgedragen aan een ander bendelid.' : 'Weet je zeker dat je de bende wilt verlaten? Omdat je het enige lid bent, wordt de bende definitief opgeheven!') : ($myLeaveFee > 0 ? 'Weet je zeker dat je ' . addslashes($myGang->name) . ' wilt verlaten? De vertrekboete bedraagt €' . number_format($myLeaveFee, 0, ',', '.') . ' (' . $myLeaveMultiplier . 'x uurloon) en wordt overgemaakt naar de bende kluis.' : 'Weet je zeker dat je ' . addslashes($myGang->name) . ' wilt verlaten?') }}');">
                                @csrf
                                <button type="submit" 
                                        class="px-3 py-1.5 rounded-xl bg-black/60 hover:bg-rose-900/80 text-white text-xs font-bold border border-white/20 shadow-xs flex items-center gap-1.5 backdrop-blur-xs transition cursor-pointer"
                                        title="Bende verlaten">
                                    <i class="fa-solid fa-arrow-right-from-bracket text-rose-400"></i>
                                    <span class="hidden sm:inline">Verlaten</span>
                                </button>
                            </form>
                        </div>

                        <!-- Gang Identity Hero Header on Banner (Always White & Sharp on dark banners) -->
                        <div class="relative z-10 flex flex-col sm:flex-row sm:items-end gap-4 pt-4">
                            <!-- Gang Emblem / Avatar -->
                            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-gradient-to-br from-purple-600 to-indigo-700 border-2 border-white/90 shadow-2xl flex items-center justify-center text-3xl sm:text-4xl text-white shrink-0 overflow-hidden ring-4 ring-black/40 backdrop-blur-xs">
                                @if($myGang->avatar)
                                    <img src="{{ $myGang->avatar_url }}" alt="{{ $myGang->name }}" class="w-full h-full object-cover">
                                @else
                                    <i class="fa-solid {{ $myGang->emblem ?? 'fa-crown' }}"></i>
                                @endif
                            </div>

                            <div class="space-y-1.5 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
                                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-2 drop-shadow-[0_2px_4px_rgba(0,0,0,0.85)]">
                                        <span>{{ $myGang->name }}</span>
                                        <span class="text-purple-200 font-mono text-xs sm:text-sm font-extrabold bg-purple-900/80 border border-purple-400/50 px-2.5 py-0.5 rounded-lg backdrop-blur-xs shadow-xs">
                                            [{{ $myGang->tag }}]
                                        </span>
                                        <i class="fa-solid fa-circle-check text-purple-400 text-sm sm:text-base drop-shadow-sm" title="Geverifieerde Bende"></i>
                                    </h1>
                                </div>

                                <!-- Meta details: Leader, Members, Status with crisp white text & drop shadow -->
                                <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs font-bold text-white/90 drop-shadow-[0_1px_3px_rgba(0,0,0,0.85)]">
                                    <span class="flex items-center gap-1.5">
                                        <i class="fa-solid fa-crown text-amber-400"></i>
                                        <span class="text-slate-300 font-medium">Leider:</span>
                                        @if($myGang->leader)
                                            <a href="{{ route('profile.show', $myGang->leader->name) }}" class="text-purple-300 hover:text-white hover:underline transition">
                                                {{ $myGang->leader->name }}
                                            </a>
                                        @else
                                            <span class="text-slate-400 italic">Onbekend</span>
                                        @endif
                                    </span>

                                    <span class="text-white/40">&bull;</span>

                                    <span class="flex items-center gap-1.5">
                                        <i class="fa-solid fa-users text-purple-300"></i>
                                        <span>{{ $myGang->members->count() }} / {{ $myGang->max_members ?? 10 }} leden</span>
                                    </span>

                                    <span class="text-white/40">&bull;</span>

                                    <span class="inline-flex items-center gap-1.5 text-emerald-400">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                        <span>{{ $onlineMembersCount ?? 0 }} online</span>
                                    </span>

                                    @if((bool)($myGang->is_open ?? true))
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-emerald-500/25 text-emerald-300 border border-emerald-400/40 backdrop-blur-xs shadow-xs">
                                            <i class="fa-solid fa-lock-open text-[9px]"></i> Open Bende
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-black/50 text-slate-300 border border-white/10 backdrop-blur-xs">
                                            <i class="fa-solid fa-lock text-[9px]"></i> Gesloten
                                        </span>
                                    @endif

                                    @if(($myGang->min_respect ?? 0) > 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-amber-500/25 text-amber-300 border border-amber-400/40 backdrop-blur-xs shadow-xs">
                                            <i class="fa-solid fa-fire text-amber-400 text-[9px]"></i> Min. {{ number_format($myGang->min_respect, 0, ',', '.') }} Respect
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Toolbar Below Banner -->
                    <div class="px-6 py-3.5 bg-slate-50/80 dark:bg-slate-900/60 border-t border-slate-200/80 dark:border-slate-800/80 flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
                            <i class="fa-solid fa-shield-halved text-purple-600 dark:text-purple-400"></i>
                            <span>Bende Hoofdkwartier</span>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            <button type="button" @click="setGangTab('doneren')" 
                                    class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs uppercase tracking-wider transition shadow-xs flex items-center gap-2 cursor-pointer">
                                <i class="fa-solid fa-vault"></i>
                                <span>Doneren</span>
                            </button>
                            <button type="button" @click="setGangTab('oorlog')" 
                                    class="px-3.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs uppercase tracking-wider transition shadow-xs flex items-center gap-2 cursor-pointer">
                                <i class="fa-solid fa-crosshairs"></i>
                                <span>Oorlog</span>
                            </button>
                            @if($myGang->isOfficer($character))
                            <button type="button" @click="setGangTab('beheer')" 
                                    class="px-3.5 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs uppercase tracking-wider transition shadow-xs flex items-center gap-2 cursor-pointer">
                                <i class="fa-solid fa-users-gear"></i>
                                <span>Beheer</span>
                            </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- 6 KEY STATS CARDS (Exact match with public gang page) -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    <!-- Totaal Respect -->
                    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-xl p-4 text-center space-y-1 shadow-xs">
                        <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block">BENDE RESPECT</span>
                        <div class="flex items-center justify-center gap-1.5 text-purple-700 dark:text-purple-400 text-lg font-black font-mono">
                            <i class="fa-solid fa-fire text-xs text-purple-500"></i>
                            <span>{{ number_format($totalRespect, 0, ',', '.') }}</span>
                        </div>
                        <span class="text-[10px] text-slate-400 block font-medium">Collectieve reputatie</span>
                    </div>

                    <!-- Bende Kas (Treasury) -->
                    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-xl p-4 text-center space-y-1 shadow-xs">
                        <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block">BENDE KAS</span>
                        <div class="flex items-center justify-center gap-1.5 text-emerald-700 dark:text-emerald-400 text-lg font-black font-mono">
                            <i class="fa-solid fa-vault text-xs text-emerald-600"></i>
                            <span>€{{ number_format($myGang->treasury, 0, ',', '.') }}</span>
                        </div>
                        <span class="text-[10px] text-slate-400 block font-medium">Gezamenlijke kluis</span>
                    </div>

                    <!-- Uurloon Bonus -->
                    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-xl p-4 text-center space-y-1 shadow-xs">
                        <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block">UURLOON</span>
                        <div class="flex items-center justify-center gap-1 text-amber-700 dark:text-amber-400 text-lg font-black font-mono">
                            <i class="fa-solid fa-coins text-xs text-amber-500"></i>
                            <span>€{{ number_format($hourlyWage, 0, ',', '.') }}</span>
                        </div>
                        @if(isset($wageDetails) && $wageDetails['pending_hours'] > 0)
                            <form action="{{ route('gangs.claim-wage') }}" method="POST" class="pt-0.5">
                                @csrf
                                <button type="submit" class="text-[9px] font-extrabold px-1.5 py-0.5 rounded bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer transition shadow-2xs">
                                    Claim €{{ number_format($wageDetails['pending_amount'], 0, ',', '.') }}
                                </button>
                            </form>
                        @else
                            <span class="text-[10px] text-slate-400 block font-medium">Dag: €{{ number_format($dailyWage, 0, ',', '.') }}</span>
                        @endif
                    </div>

                    <!-- Leden & Online -->
                    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-xl p-4 text-center space-y-1 shadow-xs">
                        <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block">LEDEN</span>
                        <div class="flex items-center justify-center gap-1.5 text-blue-700 dark:text-blue-400 text-lg font-black font-mono">
                            <i class="fa-solid fa-users text-xs text-blue-500"></i>
                            <span>{{ $myGang->members->count() }}/{{ $myGang->max_members ?? 10 }}</span>
                        </div>
                        <span class="text-[10px] text-emerald-600 dark:text-emerald-400 block font-bold">{{ $onlineMembersCount }} nu actief</span>
                    </div>

                    <!-- Smokkelkiosken -->
                    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-xl p-4 text-center space-y-1 shadow-xs">
                        <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block">SMOKKELKIOSKEN</span>
                        <div class="flex items-center justify-center gap-1.5 text-purple-700 dark:text-purple-400 text-lg font-black font-mono">
                            <i class="fa-solid fa-store text-xs text-purple-500"></i>
                            <span>{{ $myGang->bling_shops ?? 0 }}</span>
                        </div>
                        <span class="text-[10px] text-slate-400 block font-medium">+€100/u per kiosk</span>
                    </div>

                    <!-- Panden / Vastgoed -->
                    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-xl p-4 text-center space-y-1 shadow-xs">
                        <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block">BENDE PANDEN</span>
                        <div class="flex items-center justify-center gap-1.5 text-indigo-700 dark:text-indigo-400 text-lg font-black font-mono">
                            <i class="fa-solid fa-building text-xs text-indigo-500"></i>
                            <span>{{ $myGang->properties->count() }}</span>
                        </div>
                        <span class="text-[10px] text-slate-400 block font-medium">Kliniek vastgoed</span>
                    </div>
                </div>

                <!-- MAIN TWO COLUMN DETAILS LAYOUT (Exact match with gang_show.blade.php) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                    <!-- Left / Main Column (lg:col-span-8): Manifest, Shortcuts, Members Roster & Wars -->
                    <div class="lg:col-span-8 space-y-6">

                        <!-- Bende Manifest / Beschrijving -->
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-xs space-y-3">
                            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                                <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                                    <i class="fa-solid fa-scroll text-purple-600 dark:text-purple-400"></i>
                                    <span>Bende Manifest & Omschrijving</span>
                                </h3>
                                <span class="text-[10px] font-mono font-bold text-slate-400">
                                    Specialiteit: {{ $myGang->specialty ?: 'Kantine Controle' }}
                                </span>
                            </div>
                            <div class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed italic bg-slate-50 dark:bg-slate-800/40 p-4 rounded-xl border border-slate-100 dark:border-slate-800">
                                {{ $myGang->description ?: 'Deze bende heeft nog geen publiek manifest of omschrijving achtergelaten, medepatiënt.' }}
                            </div>
                        </div>

                        <!-- Snelle Bende Acties Hub -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <button type="button" @click="setGangTab('doneren')" 
                                    class="p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 hover:border-emerald-400 dark:hover:border-emerald-600 transition text-left group shadow-xs">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm font-bold mb-2 group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-circle-plus"></i>
                                </div>
                                <div class="text-xs font-black text-slate-900 dark:text-white">Kluis Doneren</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">Stort geld of sterren</div>
                            </button>

                            <button type="button" @click="setGangTab('oorlog')" 
                                    class="p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 hover:border-rose-400 dark:hover:border-rose-600 transition text-left group shadow-xs">
                                <div class="w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-sm font-bold mb-2 group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-crosshairs"></i>
                                </div>
                                <div class="text-xs font-black text-slate-900 dark:text-white">Bende Oorlog</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">Val rivaliserende bendes aan</div>
                            </button>

                            <button type="button" @click="setGangTab('depot')" 
                                    class="p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 hover:border-amber-400 dark:hover:border-amber-600 transition text-left group shadow-xs">
                                <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm font-bold mb-2 group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-boxes-stacked"></i>
                                </div>
                                <div class="text-xs font-black text-slate-900 dark:text-white">Bende Depot</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">Opslag van smokkelwaar</div>
                            </button>

                            <a href="{{ route('gangs.panden') }}" 
                               class="p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 hover:border-purple-400 dark:hover:border-purple-600 transition text-left group shadow-xs">
                                <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm font-bold mb-2 group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-building"></i>
                                </div>
                                <div class="text-xs font-black text-slate-900 dark:text-white">Bende Panden</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $myGang->properties->count() }} panden in bezit</div>
                            </a>

                            <a href="{{ route('gangs.garage') }}" 
                               class="p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 hover:border-indigo-400 dark:hover:border-indigo-600 transition text-left group shadow-xs">
                                <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm font-bold mb-2 group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-warehouse"></i>
                                </div>
                                <div class="text-xs font-black text-slate-900 dark:text-white">Bende Garage</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">Voertuigen & ontsnapping</div>
                            </a>

                            <a href="{{ route('heists.index', ['tab' => 'gang']) }}" 
                               class="p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 hover:border-rose-400 dark:hover:border-rose-600 transition text-left group shadow-xs">
                                <div class="w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-sm font-bold mb-2 group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-masks-theater"></i>
                                </div>
                                <div class="text-xs font-black text-slate-900 dark:text-white">Bende Heists</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">Co-Op overvallen plannen</div>
                            </a>
                        </div>

                        <!-- ALLE LEDEN Table with Live Search & Online Toggle -->
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-xs space-y-4">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-3">
                                <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white">
                                    <i class="fa-solid fa-users text-purple-600 dark:text-purple-400"></i>
                                    <span>ALLE LEDEN</span>
                                    <span class="text-slate-400 dark:text-slate-500 font-normal">({{ $myGang->members->count() }})</span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button type="button" 
                                            @click="onlyOnline = !onlyOnline" 
                                            :class="onlyOnline ? 'bg-emerald-600 text-white font-extrabold shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                                            class="px-2.5 py-1 rounded-lg text-xs font-bold transition cursor-pointer flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full" :class="onlyOnline ? 'bg-white' : 'bg-emerald-500 animate-pulse'"></span>
                                        <span>Online ({{ $onlineMembersCount }})</span>
                                    </button>
                                    <div class="relative w-44">
                                        <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                                        <input type="text" 
                                               x-model="memberSearch" 
                                               placeholder="Zoek bendelid..." 
                                               class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-lg pl-7 pr-6 py-1 text-xs focus:outline-none focus:border-purple-600 transition">
                                        <button type="button" x-show="memberSearch" @click="memberSearch = ''" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-[10px]">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead>
                                        <tr class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800 pb-2">
                                            <th class="py-2.5 px-3">PATIËNT</th>
                                            <th class="py-2.5 px-3">ROL</th>
                                            <th class="py-2.5 px-3 text-right">LEVEL</th>
                                            <th class="py-2.5 px-3 text-right">RESPECT</th>
                                            <th class="py-2.5 px-3 text-right">ACTIE</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 font-medium">
                                        <template x-for="member in list" :key="member.id">
                                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
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
                                                            <span class="text-[10px] text-slate-400 block" x-text="member.is_online ? 'Nu online' : ('Actief ' + member.last_active_at)"></span>
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
                                                        <a :href="member.message_url" class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-[10px] font-bold transition inline-flex items-center gap-1" title="Stuur privébericht">
                                                            <i class="fa-solid fa-envelope text-[9px] text-purple-600"></i>
                                                            <span class="hidden sm:inline">Bericht</span>
                                                        </a>
                                                        <a :href="member.profile_url" class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-[10px] font-bold transition inline-flex items-center gap-1" title="Bekijk dossier">
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
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-xs space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                                <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                                    <i class="fa-solid fa-crosshairs text-rose-600 dark:text-rose-400"></i>
                                    <span>Recente Veldslagen & Oorlogsgeschiedenis</span>
                                </h3>
                                <span class="text-[10px] font-bold text-slate-400">
                                    Totaal: {{ $warsWon }} Overwinningen &bull; {{ $warsLost }} Nederlagen
                                </span>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead>
                                        <tr class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800 pb-2">
                                            <th class="py-2.5 px-3">AANVALLER</th>
                                            <th class="py-2.5 px-3">VERDEDIGER</th>
                                            <th class="py-2.5 px-3">UITSLAG</th>
                                            <th class="py-2.5 px-3 text-right">BUIT</th>
                                            <th class="py-2.5 px-3 text-right">TIJD</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 font-medium">
                                        @foreach($recentAttacks->take(8) as $att)
                                            @php
                                                $isThisGangAttacker = $att->attacker_gang_id === $myGang->id;
                                                $thisGangWon = ($isThisGangAttacker && $att->won) || (! $isThisGangAttacker && ! $att->won);
                                            @endphp
                                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                                <td class="py-2.5 px-3">
                                                    @if($att->attackerGang)
                                                        <a href="{{ route('gangs.show', $att->attackerGang->id) }}" class="font-bold {{ $att->attacker_gang_id === $myGang->id ? 'text-purple-600 dark:text-purple-400' : 'text-slate-800 dark:text-slate-200' }} hover:underline">
                                                            [{{ $att->attackerGang->tag }}] {{ $att->attackerGang->name }}
                                                        </a>
                                                    @else
                                                        <span class="text-slate-400 italic">Onbekend</span>
                                                    @endif
                                                </td>
                                                <td class="py-2.5 px-3">
                                                    @if($att->targetGang)
                                                        <a href="{{ route('gangs.show', $att->targetGang->id) }}" class="font-bold {{ $att->target_gang_id === $myGang->id ? 'text-purple-600 dark:text-purple-400' : 'text-slate-800 dark:text-slate-200' }} hover:underline">
                                                            [{{ $att->targetGang->tag }}] {{ $att->targetGang->name }}
                                                        </a>
                                                    @else
                                                        <span class="text-slate-400 italic">Onbekend</span>
                                                    @endif
                                                </td>
                                                <td class="py-2.5 px-3">
                                                    @if($thisGangWon)
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-black bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                            <i class="fa-solid fa-trophy text-[9px]"></i> Overwinning
                                                        </span>
                                                    @else
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-black bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                                            <i class="fa-solid fa-skull text-[9px]"></i> Nederlaag
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                                    @if($att->stolen_cash > 0)
                                                        €{{ number_format($att->stolen_cash, 0, ',', '.') }}
                                                    @else
                                                        <span class="text-slate-400 font-normal">Geen</span>
                                                    @endif
                                                </td>
                                                <td class="py-2.5 px-3 text-right text-[10px] text-slate-400">
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

                    <!-- Right Column (lg:col-span-4): Factsheet, Alliances, Panden, & Verlaten -->
                    <div class="lg:col-span-4 space-y-6">

                        <!-- Bende Factsheet & Gegevens -->
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                                <h4 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                                    <i class="fa-solid fa-id-card-clip text-purple-600 dark:text-purple-400"></i>
                                    <span>Bende Gegevens</span>
                                </h4>
                                <span class="text-[10px] font-mono text-purple-600 dark:text-purple-400 font-extrabold">
                                    [{{ $myGang->tag }}]
                                </span>
                            </div>

                            <div class="divide-y divide-slate-100 dark:divide-slate-800/80 text-xs">
                                <!-- Bende Leider -->
                                <div class="py-2.5 flex items-center justify-between">
                                    <span class="text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                        <i class="fa-solid fa-crown text-amber-500"></i> Bende Leider
                                    </span>
                                    @if($myGang->leader)
                                        <a href="{{ route('profile.show', $myGang->leader->name) }}" class="font-bold text-purple-700 dark:text-purple-400 hover:underline flex items-center gap-1">
                                            {{ $myGang->leader->name }}
                                            <i class="fa-solid fa-circle-check text-purple-600 text-[10px]"></i>
                                        </a>
                                    @else
                                        <span class="text-slate-400 italic">Onbekend</span>
                                    @endif
                                </div>

                                <!-- Kantine Smokkelaar -->
                                <div class="py-2.5 flex items-center justify-between">
                                    <span class="text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                        <i class="fa-solid fa-box-open text-purple-600 dark:text-purple-400"></i> Kantine Smokkelaar
                                    </span>
                                    @if($myGang->personalShopper)
                                        <a href="{{ route('profile.show', $myGang->personalShopper->name) }}" class="font-bold text-slate-800 dark:text-slate-200 hover:text-purple-600">
                                            {{ $myGang->personalShopper->name }}
                                        </a>
                                    @else
                                        <span class="text-slate-400 italic">Nog niet aangewezen</span>
                                    @endif
                                </div>

                                <!-- Kasbeheerder -->
                                <div class="py-2.5 flex items-center justify-between">
                                    <span class="text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                        <i class="fa-solid fa-vault text-emerald-600"></i> Kasbeheerder
                                    </span>
                                    @if($myGang->moneyMagnet)
                                        <a href="{{ route('profile.show', $myGang->moneyMagnet->name) }}" class="font-bold text-slate-800 dark:text-slate-200 hover:text-purple-600">
                                            {{ $myGang->moneyMagnet->name }}
                                        </a>
                                    @else
                                        <span class="text-slate-400 italic">Nog niet aangewezen</span>
                                    @endif
                                </div>

                                <!-- Uurloon / Dagloon -->
                                <div class="py-2.5 flex items-center justify-between">
                                    <div>
                                        <span class="text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                            <i class="fa-solid fa-money-bill-wave text-amber-500"></i> Uurloon / Dagloon
                                        </span>
                                        <span class="text-[9px] text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1 mt-0.5">
                                            <i class="fa-solid fa-circle text-[5px] text-emerald-500 animate-pulse"></i> Actief per uur
                                        </span>
                                    </div>
                                    <div class="text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <span class="font-extrabold text-amber-700 dark:text-amber-400">UURLOON €{{ number_format($hourlyWage, 0, ',', '.') }}</span>
                                        </div>
                                        <span class="text-[10px] text-slate-400 font-mono block">Dag: €{{ number_format($dailyWage, 0, ',', '.') }}</span>
                                    </div>
                                </div>

                                <!-- Aanvalspunten -->
                                <div class="py-2.5 flex items-center justify-between">
                                    <span class="text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                        <i class="fa-solid fa-bolt text-amber-500"></i> Aanvalspunten
                                    </span>
                                    <span class="font-bold text-slate-900 dark:text-white font-mono">
                                        {{ number_format(($myGang->attack_power ?? 500) / 10, 2, ',', '.') }}
                                    </span>
                                </div>

                                <!-- Verdediging -->
                                <div class="py-2.5 flex items-center justify-between">
                                    <span class="text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                        <i class="fa-solid fa-shield text-indigo-500"></i> Verdediging
                                    </span>
                                    <span class="font-bold text-slate-900 dark:text-white font-mono">
                                        {{ number_format($myGang->protection_points ?? 1000, 2, ',', '.') }}
                                    </span>
                                </div>

                                <!-- Power -->
                                <div class="py-2.5 flex items-center justify-between">
                                    <span class="text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                        <i class="fa-solid fa-bolt-lightning text-purple-600"></i> Power
                                    </span>
                                    <span class="font-bold text-purple-700 dark:text-purple-400 font-mono">
                                        {{ number_format($myGang->attack_power ?? 500, 2, ',', '.') }}
                                    </span>
                                </div>

                                <!-- Bescherming -->
                                <div class="py-2.5 flex items-center justify-between">
                                    <span class="text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                        <i class="fa-solid fa-shield-halved text-emerald-600"></i> Totale Bescherming
                                    </span>
                                    <span class="font-bold text-emerald-700 dark:text-emerald-400 font-mono">
                                        {{ number_format(($myGang->protection_points ?? 1000) * 1.5, 2, ',', '.') }}
                                    </span>
                                </div>

                                <!-- Oorlogsbalans -->
                                <div class="py-2.5 flex items-center justify-between">
                                    <span class="text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                        <i class="fa-solid fa-skull text-rose-500"></i> Oorlogsbalans
                                    </span>
                                    <span class="font-bold text-slate-900 dark:text-white">
                                        <span class="text-emerald-600">{{ $warsWon }} W</span> &bull; <span class="text-rose-600">{{ $warsLost }} V</span>
                                    </span>
                                </div>

                                <!-- Minimum Respect -->
                                <div class="py-2.5 flex items-center justify-between">
                                    <span class="text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                        <i class="fa-solid fa-star text-amber-500"></i> Min. Respect
                                    </span>
                                    <span class="font-extrabold text-amber-700 dark:text-amber-400 font-mono">
                                        {{ number_format($myGang->min_respect ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>

                                <!-- Oprichtingsdatum -->
                                <div class="py-2.5 flex items-center justify-between">
                                    <span class="text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                        <i class="fa-solid fa-calendar-days text-slate-400"></i> Opgericht op
                                    </span>
                                    <span class="font-bold text-slate-700 dark:text-slate-300">
                                        {{ $myGang->created_at ? $myGang->created_at->format('d-m-Y') : 'Onbekend' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Bondgenoten / Allianties Card -->
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                                <h4 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                                    <i class="fa-solid fa-handshake text-blue-500"></i>
                                    <span>Bondgenoten</span>
                                </h4>
                                <span class="text-[10px] font-bold text-slate-400">
                                    {{ $myGang->alliances->count() }} actieve pacten
                                </span>
                            </div>

                            @if($myGang->alliances->isNotEmpty())
                                <div class="space-y-2">
                                    @foreach($myGang->alliances as $alliance)
                                        @if($alliance->allyGang)
                                            <a href="{{ route('gangs.show', $alliance->allyGang->id) }}" class="flex items-center justify-between p-2.5 rounded-lg border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                                <div class="flex items-center gap-2.5">
                                                    <div class="w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 flex items-center justify-center text-xs font-bold shrink-0">
                                                        <i class="fa-solid {{ $alliance->allyGang->emblem ?? 'fa-crown' }}"></i>
                                                    </div>
                                                    <div>
                                                        <div class="text-xs font-extrabold text-slate-900 dark:text-white leading-tight flex items-center gap-1">
                                                            <span>{{ $alliance->allyGang->name }}</span>
                                                            <span class="text-purple-600 dark:text-purple-400 font-mono text-[10px]">[{{ $alliance->allyGang->tag }}]</span>
                                                        </div>
                                                        <span class="text-[10px] text-slate-400">{{ $alliance->allyGang->members->count() }} leden</span>
                                                    </div>
                                                </div>
                                                <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
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
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs space-y-3">
                            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                                <h4 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                                    <i class="fa-solid fa-building text-indigo-600 dark:text-indigo-400"></i>
                                    <span>Vastgoed & Panden</span>
                                </h4>
                                <a href="{{ route('gangs.panden') }}" class="text-[10px] font-mono font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                    {{ $myGang->properties->count() }} Panden &rarr;
                                </a>
                            </div>

                            @if($myGang->properties->isNotEmpty())
                                <div class="space-y-2">
                                    @foreach($myGang->properties->take(5) as $prop)
                                        <div class="p-2.5 rounded-lg border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 flex items-center justify-between text-xs">
                                            <span class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                                <i class="fa-solid fa-building-wheat text-indigo-500 text-xs"></i>
                                                {{ $prop->name }}
                                            </span>
                                            <span class="text-[10px] font-mono text-purple-600 dark:text-purple-400 font-extrabold">
                                                Lvl {{ $prop->level }}
                                            </span>
                                        </div>
                                    @endforeach
                                    @if($myGang->properties->count() > 5)
                                        <a href="{{ route('gangs.panden') }}" class="text-[10px] text-slate-400 text-center block pt-1 hover:text-purple-600">
                                            +{{ $myGang->properties->count() - 5 }} meer panden in eigendom
                                        </a>
                                    @endif
                                </div>
                            @else
                                <div class="py-4 text-center text-xs text-slate-400 dark:text-slate-500">
                                    Geen vastgoed in bezit.
                                </div>
                            @endif
                        </div>

                        <!-- Bende Verlaten Box (Danger Zone) -->
                        <div class="bg-rose-50/60 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/60 rounded-xl p-5 space-y-3">
                            <div class="space-y-1">
                                <h4 class="text-xs font-black uppercase tracking-wider text-rose-800 dark:text-rose-300 flex items-center gap-2">
                                    <i class="fa-solid fa-arrow-right-from-bracket text-rose-600"></i>
                                    <span>Bende Verlaten</span>
                                </h4>
                                @if($character->gang_role === 'leader')
                                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                                        Als bendeleider kun je vertrekken. Het leiderschap wordt overgedragen aan een ander bendelid, of de factie wordt opgeheven als je het enige lid bent.
                                    </p>
                                @else
                                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                                        @if($myLeaveFee > 0)
                                            De vertrekboete bedraagt <strong class="text-rose-700 dark:text-rose-400 font-mono font-bold">€{{ number_format($myLeaveFee, 0, ',', '.') }}</strong> ({{ $myLeaveMultiplier }}x uurloon) en wordt overgemaakt naar de bende kluis.
                                        @else
                                            Er is geen vertrekboete ingesteld. Je kunt kosteloos vertrekken.
                                        @endif
                                    </p>
                                    @if($myLeaveFee > 0)
                                        <div class="text-[11px] pt-1">
                                            <span class="text-slate-500 dark:text-slate-400">Cash op zak: <strong class="font-mono {{ $hasEnoughCashToLeave ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">€{{ number_format($character->cash, 0, ',', '.') }}</strong></span>
                                            @if(! $hasEnoughCashToLeave)
                                                <div class="text-rose-600 dark:text-rose-400 font-bold mt-1 text-[10px] flex items-center gap-1">
                                                    <i class="fa-solid fa-circle-exclamation"></i> Minimaal €{{ number_format($myLeaveFee - $character->cash, 0, ',', '.') }} tekort.
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                @endif
                            </div>

                            <form action="{{ route('gangs.leave') }}" method="POST"
                                  onsubmit="return confirm('{{ $character->gang_role === 'leader' ? ($myGang->members->count() > 1 ? 'Weet je zeker dat je de bende wilt verlaten? Het leiderschap wordt overgedragen aan een ander bendelid.' : 'Weet je zeker dat je de bende wilt verlaten? Omdat je het enige lid bent, wordt de bende definitief opgeheven!') : ($myLeaveFee > 0 ? 'Weet je zeker dat je ' . addslashes($myGang->name) . ' wilt verlaten? Er wordt een vertrekboete van €' . number_format($myLeaveFee, 0, ',', '.') . ' overgemaakt naar de bende kluis.' : 'Weet je zeker dat je ' . addslashes($myGang->name) . ' wilt verlaten?') }}');">
                                @csrf
                                <button type="submit" 
                                        class="w-full py-2 px-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs shadow-xs transition cursor-pointer flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                    <span>Bende Verlaten</span>
                                </button>
                            </form>
                        </div>

                    </div>

                </div>

            </div>
            @endif

            <!-- TAB: ALLE BENDES (MAKKELLIJKE OVERZICHTSLIJST MET FILTERS & KAARTEN) -->
            <div x-show="activeGangTab === 'alle_bendes'" class="space-y-6" x-data="{
                search: '',
                filter: 'all', // 'all', 'open', 'closed'
                viewMode: 'grid', // 'grid', 'table'
                gangs: {{ Js::from($gangRankings) }},
                get filteredGangs() {
                    let q = this.search.toLowerCase().trim();
                    return this.gangs.filter(g => {
                        if (q && !g.name.toLowerCase().includes(q) && !g.tag.toLowerCase().includes(q) && !g.leader_name.toLowerCase().includes(q)) return false;
                        if (this.filter === 'open' && !g.is_open) return false;
                        if (this.filter === 'closed' && g.is_open) return false;
                        return true;
                    });
                }
            }">
                <!-- Top Header & Search Banner -->
                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs relative overflow-hidden">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-purple-50 dark:bg-purple-950/70 border border-purple-200 dark:border-purple-800 text-[10px] font-extrabold uppercase tracking-wider text-purple-700 dark:text-purple-300 mb-2">
                                <i class="fa-solid fa-address-book text-purple-600 dark:text-purple-400"></i> Overzicht
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                                <span>Alle Kliniek Bendes</span>
                                <span class="text-xs font-mono text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-950/60 px-2 py-0.5 rounded-lg border border-purple-200 dark:border-purple-800 font-bold" x-text="filteredGangs.length + ' Facties'"></span>
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-xl">
                                Bekijk alle bendes van de TBS kliniek. Inspecteer publieke dossiers, check toelatingseisen of dien direct een aanvraag in.
                            </p>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <!-- View mode toggle -->
                            <div class="inline-flex p-1 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                <button type="button" 
                                        @click="viewMode = 'grid'" 
                                        :class="viewMode === 'grid' ? 'bg-white dark:bg-slate-700 text-purple-600 dark:text-purple-400 shadow-2xs font-bold' : 'text-slate-500 dark:text-slate-400'"
                                        class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer flex items-center gap-1.5">
                                    <i class="fa-solid fa-grip text-[11px]"></i>
                                    <span class="hidden sm:inline">Kaarten</span>
                                </button>
                                <button type="button" 
                                        @click="viewMode = 'table'" 
                                        :class="viewMode === 'table' ? 'bg-white dark:bg-slate-700 text-purple-600 dark:text-purple-400 shadow-2xs font-bold' : 'text-slate-500 dark:text-slate-400'"
                                        class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer flex items-center gap-1.5">
                                    <i class="fa-solid fa-list text-[11px]"></i>
                                    <span class="hidden sm:inline">Lijst</span>
                                </button>
                            </div>

                            @if(!$character->gang_id)
                            <button type="button" @click="showCreateModal = true" 
                                    class="px-3.5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs uppercase tracking-wider transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                <i class="fa-solid fa-plus"></i>
                                <span>Bende Oprichten</span>
                            </button>
                            @endif
                        </div>
                    </div>

                    <!-- Search and Filters Bar -->
                    <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="relative flex-1 max-w-md">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input type="text" 
                                   x-model="search" 
                                   placeholder="Zoek op bendenaam, tag of leider..." 
                                   class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl pl-9 pr-8 py-2 text-xs focus:outline-none focus:border-purple-600 transition">
                            <button type="button" x-show="search" @click="search = ''" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
                            <button type="button" 
                                    @click="filter = 'all'" 
                                    :class="filter === 'all' ? 'bg-purple-600 text-white font-extrabold shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer whitespace-nowrap">
                                Alle Bendes ({{ $gangRankings->count() }})
                            </button>
                            <button type="button" 
                                    @click="filter = 'open'" 
                                    :class="filter === 'open' ? 'bg-emerald-600 text-white font-extrabold shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer whitespace-nowrap flex items-center gap-1.5">
                                <i class="fa-solid fa-lock-open text-[10px]"></i>
                                <span>Open voor toelating</span>
                            </button>
                            <button type="button" 
                                    @click="filter = 'closed'" 
                                    :class="filter === 'closed' ? 'bg-slate-700 text-white font-extrabold shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer whitespace-nowrap flex items-center gap-1.5">
                                <i class="fa-solid fa-lock text-[10px]"></i>
                                <span>Gesloten</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- CARDS GRID VIEW -->
                <div x-show="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    <template x-for="g in filteredGangs" :key="g.id">
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs hover:border-purple-300 dark:hover:border-purple-700 transition flex flex-col justify-between group">
                            <div>
                                <!-- Mini Banner Header -->
                                <div class="h-24 w-full overflow-hidden relative bg-purple-950">
                                    <img :src="g.banner_url" :alt="g.name" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

                                    <!-- Top Right Status Pill -->
                                    <div class="absolute top-2.5 right-2.5 flex items-center gap-1.5">
                                        <template x-if="g.is_open">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-emerald-500/90 text-white backdrop-blur-xs shadow-2xs">
                                                <i class="fa-solid fa-lock-open text-[8px]"></i> Open
                                            </span>
                                        </template>
                                        <template x-if="!g.is_open">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[9px] font-bold bg-black/60 text-slate-300 backdrop-blur-xs border border-white/10">
                                                <i class="fa-solid fa-lock text-[8px]"></i> Gesloten
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                <!-- Body with overlapping avatar -->
                                <div class="p-4 pt-0 relative z-10 space-y-3">
                                    <div class="flex items-end justify-between -mt-7">
                                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-purple-600 to-indigo-700 border-2 border-white dark:border-slate-900 shadow-md flex items-center justify-center text-xl text-white shrink-0 overflow-hidden">
                                            <template x-if="g.avatar_url">
                                                <img :src="g.avatar_url" :alt="g.name" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!g.avatar_url">
                                                <i class="fa-solid" :class="g.icon"></i>
                                            </template>
                                        </div>

                                        <div class="flex items-center gap-1.5">
                                            <template x-if="g.min_respect > 0">
                                                <span class="text-[9px] font-extrabold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/60 px-1.5 py-0.5 rounded border border-amber-200 dark:border-amber-800 flex items-center gap-1">
                                                    <i class="fa-solid fa-fire text-[8px] text-amber-500"></i> Min. <span x-text="g.min_respect"></span>
                                                </span>
                                            </template>
                                        </div>
                                    </div>

                                    <div>
                                        <a :href="g.profile_url" class="text-base font-black text-slate-900 dark:text-white hover:text-purple-600 dark:hover:text-purple-400 transition flex items-center gap-1.5 leading-tight">
                                            <span x-text="g.name"></span>
                                            <span class="text-purple-600 dark:text-purple-400 font-mono text-xs font-extrabold" x-text="'[' + g.tag + ']'"></span>
                                            <i class="fa-solid fa-circle-check text-purple-600 text-xs" title="Geverifieerd"></i>
                                        </a>

                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5 mt-0.5 font-medium">
                                            <i class="fa-solid fa-crown text-amber-500 text-[10px]"></i>
                                            <span>Leider:</span>
                                            <span class="font-bold text-slate-700 dark:text-slate-300" x-text="g.leader_name"></span>
                                        </div>
                                    </div>

                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2 italic" x-text="g.description || 'Geen bende manifest ingevuld.'"></p>

                                    <!-- Quick Stats Grid in Card -->
                                    <div class="grid grid-cols-3 gap-1.5 pt-1 text-center">
                                        <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                                            <span class="text-[8px] font-extrabold uppercase text-slate-400 block">Respect</span>
                                            <span class="text-xs font-mono font-black text-purple-600 dark:text-purple-400" x-text="g.respect_formatted || g.slay"></span>
                                        </div>
                                        <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                                            <span class="text-[8px] font-extrabold uppercase text-slate-400 block">Kas</span>
                                            <span class="text-xs font-mono font-black text-emerald-600 dark:text-emerald-400" x-text="g.cash"></span>
                                        </div>
                                        <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                                            <span class="text-[8px] font-extrabold uppercase text-slate-400 block">Leden</span>
                                            <span class="text-xs font-mono font-black text-slate-700 dark:text-slate-200" x-text="g.members_count + '/' + g.max_members"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Actions -->
                            <div class="px-4 pb-4 pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                                <a :href="g.profile_url" 
                                   class="flex-1 py-1.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition text-center inline-flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-eye text-[10px]"></i>
                                    <span>Bekijk Bende</span>
                                </a>

                                @if($myGang)
                                    <template x-if="g.id === {{ $myGang->id }}">
                                        <span class="px-3 py-1.5 rounded-xl bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-extrabold text-xs inline-flex items-center gap-1 border border-purple-200 dark:border-purple-800">
                                            <i class="fa-solid fa-check text-[10px]"></i> Jouw Bende
                                        </span>
                                    </template>
                                @else
                                    <template x-if="g.has_pending_request">
                                        <span class="px-3 py-1.5 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 font-bold text-xs inline-flex items-center gap-1 border border-amber-200 dark:border-amber-800">
                                            <i class="fa-solid fa-clock-rotate-left text-[10px]"></i> In Behandeling
                                        </span>
                                    </template>
                                    <template x-if="!g.has_pending_request && g.is_open && {{ (int) ($character->respect ?? 0) }} >= (g.min_respect || 0)">
                                        <form :action="'{{ url('/gangs') }}/' + g.id + '/aanvragen'" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="py-1.5 px-3 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs transition cursor-pointer shadow-xs inline-flex items-center gap-1.5">
                                                <i class="fa-solid fa-user-plus text-[10px]"></i>
                                                <span>Aanmelden</span>
                                            </button>
                                        </form>
                                    </template>
                                    <template x-if="!g.has_pending_request && g.is_open && {{ (int) ($character->respect ?? 0) }} < (g.min_respect || 0)">
                                        <span class="py-1.5 px-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 font-bold text-xs inline-flex items-center gap-1 border border-slate-200 dark:border-slate-700 cursor-not-allowed" :title="'Je hebt minimaal ' + g.min_respect + ' respect nodig om lid te worden'">
                                            <i class="fa-solid fa-lock text-[10px]"></i>
                                            <span>Min. <span x-text="g.min_respect"></span> Respect</span>
                                        </span>
                                    </template>
                                    <template x-if="!g.has_pending_request && !g.is_open">
                                        <span class="px-2.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 font-bold text-xs inline-flex items-center gap-1 border border-slate-200 dark:border-slate-700">
                                            <i class="fa-solid fa-lock text-[10px]"></i> Gesloten
                                        </span>
                                    </template>
                                @endif
                            </div>
                        </div>
                    </template>
                </div>

                <!-- TABLE LIST VIEW -->
                <div x-show="viewMode === 'table'" class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-xs space-y-4">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800 pb-2">
                                    <th class="py-3 px-3">BENDE & LEIDER</th>
                                    <th class="py-3 px-3">STATUS</th>
                                    <th class="py-3 px-3 text-center">LEDEN</th>
                                    <th class="py-3 px-3 text-right">RESPECT</th>
                                    <th class="py-3 px-3 text-right">KAS</th>
                                    <th class="py-3 px-3 text-right">ACTIE</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 font-medium">
                                <template x-for="g in filteredGangs" :key="g.id">
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                        <td class="py-3.5 px-3">
                                            <div class="flex items-center gap-3">
                                                <a :href="g.profile_url" class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 flex items-center justify-center text-base shrink-0 font-bold shadow-xs overflow-hidden">
                                                    <template x-if="g.avatar_url">
                                                        <img :src="g.avatar_url" :alt="g.name" class="w-full h-full object-cover">
                                                    </template>
                                                    <template x-if="!g.avatar_url">
                                                        <i class="fa-solid" :class="g.icon"></i>
                                                    </template>
                                                </a>
                                                <div>
                                                    <div class="font-extrabold text-slate-900 dark:text-white text-xs leading-tight flex items-center gap-1.5">
                                                        <a :href="g.profile_url" class="hover:text-purple-600 dark:hover:text-purple-400 transition" x-text="g.name"></a>
                                                        <span class="text-purple-600 dark:text-purple-400 font-mono text-[10px] font-bold" x-text="'[' + g.tag + ']'"></span>
                                                        <i class="fa-solid fa-circle-check text-purple-600 text-[10px]"></i>
                                                    </div>
                                                    <div class="text-[11px] text-slate-400 font-medium">
                                                        Leider: <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="g.leader_name"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-3">
                                            <template x-if="g.is_open">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                    <i class="fa-solid fa-lock-open text-[8px]"></i> Open
                                                </span>
                                            </template>
                                            <template x-if="!g.is_open">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                                    <i class="fa-solid fa-lock text-[8px]"></i> Gesloten
                                                </span>
                                            </template>
                                        </td>
                                        <td class="py-3.5 px-3 text-center font-mono font-bold text-slate-700 dark:text-slate-300" x-text="g.members_count + '/' + g.max_members"></td>
                                        <td class="py-3.5 px-3 text-right font-extrabold text-purple-700 dark:text-purple-400 font-mono" x-text="g.respect_formatted || g.slay"></td>
                                        <td class="py-3.5 px-3 text-right font-bold text-emerald-700 dark:text-emerald-400 font-mono" x-text="g.cash"></td>
                                        <td class="py-3.5 px-3 text-right">
                                            <div class="inline-flex items-center gap-1.5 justify-end">
                                                <a :href="g.profile_url" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition inline-flex items-center gap-1">
                                                    <i class="fa-solid fa-eye text-[10px]"></i>
                                                    <span>Bekijk</span>
                                                </a>
                                                @if(!$character->gang_id)
                                                    <template x-if="g.has_pending_request">
                                                        <span class="px-2.5 py-1.5 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 font-bold text-xs border border-amber-200 dark:border-amber-800">
                                                            In Behandeling
                                                        </span>
                                                    </template>
                                                    <template x-if="!g.has_pending_request && g.is_open && {{ (int) ($character->respect ?? 0) }} >= (g.min_respect || 0)">
                                                        <form :action="'{{ url('/gangs') }}/' + g.id + '/aanvragen'" method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs transition cursor-pointer shadow-xs">
                                                                Aanmelden
                                                            </button>
                                                        </form>
                                                    </template>
                                                    <template x-if="!g.has_pending_request && g.is_open && {{ (int) ($character->respect ?? 0) }} < (g.min_respect || 0)">
                                                        <span class="px-2 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-400 font-bold text-xs inline-flex items-center gap-1 border border-slate-200 dark:border-slate-700 cursor-not-allowed" :title="'Minimaal ' + g.min_respect + ' respect vereist'">
                                                            <i class="fa-solid fa-lock text-[9px]"></i>
                                                            <span>Min. <span x-text="g.min_respect"></span></span>
                                                        </span>
                                                    </template>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div x-show="filteredGangs.length === 0" class="py-12 text-center text-xs text-slate-400 dark:text-slate-500 bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800">
                    <i class="fa-solid fa-users-slash text-2xl mb-2 opacity-50 block"></i>
                    Geen bendes gevonden die voldoen aan je zoekcriteria.
                </div>
            </div>

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
                        <h2 class="text-lg font-extrabold text-slate-900 uppercase tracking-wide">Bende Winkel</h2>
                        <p class="text-xs text-slate-500 mt-1">
                            Verhoog het uurloon met Smokkelkiosken, koop bescherming tegen aanvallen, en haal power om andere bendes te verslaan!
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
                                        <span>Bende Portofoon (Besloten Bende Chat)</span>
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
                                            <span>Koop Bende Chat (500 ⭐)</span>
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
                                <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight">Doneren aan Bende Kas</h2>
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
                                <button type="button" @click="donateAmount = Math.min({{ $character->cash }}, 100000)" 
                                        class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-purple-400 transition cursor-pointer">
                                    €100k
                                </button>
                                <button type="button" @click="donateAmount = Math.min({{ $character->cash }}, 1000000)" 
                                        class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-purple-400 transition cursor-pointer">
                                    €1 Miljoen
                                </button>
                                <button type="button" @click="donateAmount = Math.min({{ $character->cash }}, 10000000)" 
                                        class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-purple-400 transition cursor-pointer">
                                    €10M
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

                    <!-- Maskers Doneren Box -->
                    <div class="bg-gradient-to-br from-slate-50 to-purple-50/30 dark:from-slate-800/50 dark:to-purple-950/20 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 space-y-4" x-data="{ maskDonationQty: {{ min(5, (int)($character->masks ?? 0)) }} }">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                <i class="fa-solid fa-masks-theater text-purple-600 dark:text-purple-400"></i>
                                <span>Maskers op zak:</span>
                                <strong class="text-purple-600 dark:text-purple-400 font-black font-mono">{{ (int)($character->masks ?? 0) }} stuks</strong>
                            </span>

                            <div class="flex items-center gap-1.5 flex-wrap">
                                <button type="button" @click="maskDonationQty = Math.min({{ (int)($character->masks ?? 0) }}, 5)" class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-purple-400 transition cursor-pointer">5x</button>
                                <button type="button" @click="maskDonationQty = Math.min({{ (int)($character->masks ?? 0) }}, 20)" class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-purple-400 transition cursor-pointer">20x</button>
                                <button type="button" @click="maskDonationQty = {{ (int)($character->masks ?? 0) }}" class="px-2.5 py-1 text-[11px] font-black rounded-lg bg-purple-100 dark:bg-purple-950/80 border border-purple-300 dark:border-purple-700 text-purple-800 dark:text-purple-300 hover:bg-purple-200 transition cursor-pointer">Alles / Max</button>
                            </div>
                        </div>

                        <form action="{{ route('gangs.donate-masks') }}" method="POST" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            @csrf
                            <div class="relative flex-1">
                                <i class="fa-solid fa-mask absolute left-3.5 top-3 text-purple-500 text-xs"></i>
                                <input type="number" 
                                       name="quantity" 
                                       x-model.number="maskDonationQty"
                                       min="1" 
                                       max="{{ (int)($character->masks ?? 0) }}" 
                                       required
                                       class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl pl-8 pr-4 py-2 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:border-purple-500 font-mono shadow-2xs">
                            </div>
                            <button type="submit" 
                                    :disabled="maskDonationQty < 1 || maskDonationQty > {{ (int)($character->masks ?? 0) }}"
                                    class="py-2.5 px-6 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed text-white font-black text-xs uppercase tracking-wider cursor-pointer transition shadow-xs shrink-0 flex items-center justify-center gap-2">
                                <i class="fa-solid fa-masks-theater"></i>
                                <span>Doneer Maskers aan Bende Depot</span>
                            </button>
                        </form>
                    </div>

                    <!-- Spullen & Heist Gear Doneren Box -->
                    <div class="bg-gradient-to-br from-slate-50 to-indigo-50/30 dark:from-slate-800/50 dark:to-indigo-950/20 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                <i class="fa-solid fa-box-open text-indigo-500"></i>
                                <span>Spullen &amp; Benodigdheden Doneren (Sigaretten, Gereedschap, Heist Gear)</span>
                            </span>
                            <span class="text-[10px] text-slate-400 font-medium">Opslagplek in bende depot vereist</span>
                        </div>

                        @if(isset($myInventoryItems) && $myInventoryItems->isNotEmpty())
                        <form action="{{ route('gangs.donate-item') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                            @csrf
                            <div class="sm:col-span-7">
                                <select name="character_item_id" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                                    @foreach($myInventoryItems as $ci)
                                    <option value="{{ $ci->id }}">{{ $ci->item?->name ?? 'Voorwerp' }} (Je bezit: {{ $ci->quantity }}x)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <input type="number" name="quantity" value="1" min="1" max="100" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 dark:text-white text-center focus:outline-none focus:border-indigo-500" placeholder="Aantal" required>
                            </div>
                            <div class="sm:col-span-3">
                                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-black text-xs uppercase tracking-wider cursor-pointer transition shadow-xs flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-share-from-square"></i>
                                    <span>Doneer Item</span>
                                </button>
                            </div>
                        </form>
                        @else
                        <p class="text-xs text-slate-400 py-1">Je hebt momenteel geen overige spullen of smokkelwaar in je opbergplaats om te doneren.</p>
                        @endif
                    </div>

                    @else
                    <p class="text-xs text-slate-400 py-4">Je bent nog geen lid van een bende. Sluit je eerst aan bij een bende via de ranglijst!</p>
                    @endif
                </div>
            </div>

            <!-- TAB: BENDE DEPOT (OPSLAG, VOORRAAD, GARAGE & PANDEN) -->
            <div x-show="activeGangTab === 'depot'" class="space-y-6">
                @if($myGang)
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-7 shadow-xs space-y-6">
                    
                    <!-- Depot Header & Capaciteit Progress Bar -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl shrink-0 shadow-2xs">
                                <i class="fa-solid fa-boxes-stacked"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-0.5">
                                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/70 border border-amber-200 dark:border-amber-800/60 px-2 py-0.5 rounded-md">Collectieve Opslag</span>
                                    <span class="text-[10px] text-slate-400 font-mono">Bende: {{ $myGang->name }}</span>
                                </div>
                                <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight">Centraal Bende Depot</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    Beheer de collectieve maskers, smokkelwaar, het bende wagenpark en vastgoedpanden voor Bende Heists.
                                </p>
                            </div>
                        </div>

                        <!-- Highlights Stats -->
                        <div class="flex items-center gap-2 flex-wrap shrink-0">
                            <div class="px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300">
                                <span class="text-[10px] text-slate-400 uppercase tracking-wider block font-bold">Opslag Capaciteit</span>
                                <span class="font-black text-amber-600 dark:text-amber-400 font-mono">{{ $myGang->getStorageUsed() }} / {{ $myGang->total_storage_capacity }} plekken</span>
                            </div>
                            <div class="px-3.5 py-2 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200 dark:border-purple-800/60 text-xs text-purple-700 dark:text-purple-300">
                                <span class="text-[10px] text-purple-400 uppercase tracking-wider block font-bold">Wagenpark Fleet</span>
                                <span class="font-black text-purple-900 dark:text-purple-200 font-mono">{{ $myGang->storedVehicles->count() }} / {{ $myGang->total_vehicle_slots }} wagens</span>
                            </div>
                        </div>
                    </div>

                    <!-- Capaciteit Alert als het vol zit -->
                    @if($myGang->getStorageUsed() >= $myGang->total_storage_capacity && $myGang->total_storage_capacity > 0)
                    <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/50 border border-amber-300 dark:border-amber-800 text-amber-900 dark:text-amber-200 text-xs flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-triangle-exclamation text-amber-600 text-lg shrink-0"></i>
                            <div>
                                <strong>Bende Depot Zit Vol ({{ $myGang->getStorageUsed() }}/{{ $myGang->total_storage_capacity }})!</strong>
                                Bendeleden kunnen pas weer nieuwe maskers of spullen doneren zodra beheerders overtollige voorraad verkopen voor de Bende Kas of nieuwe opslagpanden aankopen.
                            </div>
                        </div>
                        <a href="{{ route('gangs.panden') }}" class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs shrink-0 transition">
                            Panden Kopen
                        </a>
                    </div>
                    @elseif($myGang->total_storage_capacity == 0)
                    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-300 dark:border-rose-800 text-rose-900 dark:text-rose-200 text-xs flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-warehouse text-rose-600 text-lg shrink-0"></i>
                            <div>
                                <strong>Jullie bende bezit nog geen opslagpanden!</strong>
                                Schaf een Bende Pand (zoals een Loods of Safehouse) aan via Bende Panden om opslagruimte en garageplekken vrij te spelen.
                            </div>
                        </div>
                        <a href="{{ route('gangs.panden') }}" class="px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-black text-xs shrink-0 transition">
                            Naar Bende Panden
                        </a>
                    </div>
                    @endif

                    <!-- SECTIE 1: BENDE MASKERS (HEIST GEAR) -->
                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-950/80 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl shrink-0">
                                <i class="fa-solid fa-masks-theater"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-black text-slate-900 dark:text-white">Bende Overval Maskers</h3>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-purple-100 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300">
                                        Vereist voor Bende Heists
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    Bij het starten van een officiële Bende Heist worden maskers direct uit deze voorraad afgetrokken.
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-3 shrink-0 justify-between md:justify-end">
                            <span class="text-xl font-black text-purple-600 dark:text-purple-400 font-mono">
                                {{ (int)($myGang->masks ?? 0) }} <span class="text-xs font-bold text-slate-400">stuks</span>
                            </span>

                            @if($myGang->isOfficer($character) && (int)($myGang->masks ?? 0) > 0)
                            <form action="{{ route('gangs.sell-masks') }}" method="POST" onsubmit="return confirm('Weet je zeker dat je bende maskers wilt verkopen aan de heler? De opbrengst van €750 per stuk wordt direct gestort in de Bende Kas.');" class="flex items-center gap-1.5">
                                @csrf
                                <input type="number" name="quantity" value="{{ min(10, (int)($myGang->masks ?? 0)) }}" min="1" max="{{ (int)($myGang->masks ?? 0) }}" class="w-16 px-2 py-1.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-bold text-center" title="Aantal te verkopen">
                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs transition flex items-center gap-1 shadow-xs cursor-pointer" title="Verkoop voor bende kas">
                                    <i class="fa-solid fa-coins"></i>
                                    <span>Verkopen (€750 p/s)</span>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>

                    <!-- SECTIE 2: BENDE ITEMS VOORRAAD -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                                <i class="fa-solid fa-boxes-stacked text-amber-500"></i>
                                <span>Bende Smokkel- &amp; Voorraadartikelen</span>
                            </h3>
                            <button type="button" @click="setGangTab('doneren')" class="text-xs font-bold text-purple-600 dark:text-purple-400 hover:underline">
                                + Spullen Doneren
                            </button>
                        </div>

                        @if($myGang->items->isEmpty())
                        <div class="p-8 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 text-center">
                            <i class="fa-solid fa-box-open text-slate-300 dark:text-slate-700 text-3xl mb-2 block"></i>
                            <p class="text-xs font-bold text-slate-500 dark:text-slate-400">Er zijn momenteel geen overige goederen in het bende depot opgeslagen.</p>
                            <p class="text-[11px] text-slate-400 mt-1">Leden kunnen sigaretten, gereedschap en heist items doneren via het Doneren tabblad.</p>
                        </div>
                        @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($myGang->items as $gItem)
                            <div class="p-4 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 shadow-2xs flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 flex items-center justify-center text-base shrink-0">
                                        <i class="fa-solid {{ $gItem->item?->icon ?? 'fa-box' }}"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-black text-slate-900 dark:text-white">{{ $gItem->item?->name ?? 'Voorwerp' }}</h4>
                                        <span class="text-[10px] text-slate-500 dark:text-slate-400">Voorraad: <strong class="text-amber-600 dark:text-amber-400">{{ $gItem->quantity }}x</strong></span>
                                    </div>
                                </div>

                                @if($myGang->isOfficer($character))
                                <form action="{{ route('gangs.sell-item', $gItem->id) }}" method="POST" onsubmit="return confirm('Item verkopen en opbrengst storten in de bende kas?');" class="flex items-center gap-1">
                                    @csrf
                                    <input type="number" name="quantity" value="1" min="1" max="{{ $gItem->quantity }}" class="w-12 px-1 py-1 rounded-lg border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-xs text-center">
                                    <button type="submit" class="px-2 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-black text-[11px] transition" title="Verkopen voor bende kas">
                                        <i class="fa-solid fa-coins"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <!-- SECTIE 3: BENDE GARAGE (WAGENPARK IN DEPOT) -->
                    <div class="space-y-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                                <i class="fa-solid fa-car-side text-indigo-500"></i>
                                <span>Bende Garage Vloot (Gestalde Vluchtvoertuigen)</span>
                            </h3>
                            <a href="{{ route('garage.index') }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                Naar Garage &rarr;
                            </a>
                        </div>

                        @if($myGang->storedVehicles->isEmpty())
                        <div class="p-6 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-200 dark:border-indigo-900/40 text-center">
                            <i class="fa-solid fa-warehouse text-indigo-400 text-2xl mb-1.5 block"></i>
                            <p class="text-xs font-bold text-indigo-900 dark:text-indigo-200">Er staan nog geen auto's geparkeerd in de Bende Garage.</p>
                            <p class="text-[11px] text-indigo-600 dark:text-indigo-400 mt-0.5">
                                Parkeer een wagen vanuit je persoonlijke garage in de Bende Garage om Bende Heists te kunnen organiseren!
                            </p>
                        </div>
                        @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($myGang->storedVehicles as $gv)
                            <div class="p-4 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xs space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm">
                                            <i class="fa-solid fa-car"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-black text-slate-900 dark:text-white">{{ $gv->vehicle?->name ?? 'Bendevoertuig' }}</h4>
                                            <span class="text-[10px] text-slate-400 uppercase font-bold">{{ $gv->vehicle?->type ?? 'Voertuig' }}</span>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300">
                                        Bende Fleet
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-100 dark:border-slate-700/60 text-[11px]">
                                    <div class="text-slate-500">Snelheid: <strong class="text-slate-900 dark:text-white font-mono">{{ $gv->effective_speed }} km/u</strong></div>
                                    <div class="text-slate-500 text-right">Conditie: <strong class="{{ $gv->current_condition < 50 ? 'text-rose-600' : 'text-emerald-600' }} font-mono">{{ $gv->current_condition }}%</strong></div>
                                </div>
                                @if(($gv->character_id === $character->id || ($myGang && $myGang->isOfficer($character))) && $gv->current_condition < 100)
                                    <form action="{{ route('garage.repair', $gv->id) }}" method="POST" class="pt-1">
                                        @csrf
                                        <button type="submit" 
                                                class="w-full py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-black text-[10px] uppercase tracking-wider transition shadow-2xs flex items-center justify-center gap-1.5 cursor-pointer active:scale-95">
                                            <i class="fa-solid fa-wrench text-[9px]"></i>
                                            <span>Reviseren (€{{ number_format($gv->repair_cost, 0, ',', '.') }})</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <!-- SECTIE 4: OVERZICHT AANGEKOCHTE BENDE PANDEN -->
                    <div class="space-y-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                                <i class="fa-solid fa-building text-purple-500"></i>
                                <span>Aangekochte Bende Panden (Vastgoedimperium)</span>
                            </h3>
                            <a href="{{ route('gangs.panden') }}" class="text-xs font-bold text-purple-600 dark:text-purple-400 hover:underline">
                                Panden Beheer &rarr;
                            </a>
                        </div>

                        @if($myGang->properties->isEmpty())
                        <div class="p-6 rounded-2xl bg-purple-50/50 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-900/40 text-center">
                            <i class="fa-solid fa-city text-purple-400 text-2xl mb-1.5 block"></i>
                            <p class="text-xs font-bold text-purple-900 dark:text-purple-200">Jullie bende bezit nog geen panden.</p>
                            <p class="text-[11px] text-purple-600 dark:text-purple-400 mt-0.5">
                                Koop safehouses, loodsen en bunkers om opslagcapaciteit en garageplekken vrij te spelen.
                            </p>
                        </div>
                        @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($myGang->properties as $prop)
                            <div class="p-4 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xs space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-950/80 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm">
                                            <i class="fa-solid fa-warehouse"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-black text-slate-900 dark:text-white">{{ $prop->name }}</h4>
                                            <span class="text-[10px] text-purple-600 dark:text-purple-400 font-bold uppercase">{{ $prop->type }} (Lvl {{ $prop->level }})</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-100 dark:border-slate-700/60 text-[11px] text-slate-500">
                                    <div>Opslag: <strong class="text-amber-600 font-mono">+{{ $prop->storage_capacity }}</strong></div>
                                    <div class="text-right">Garage: <strong class="text-indigo-600 font-mono">+{{ $prop->vehicle_slots }}</strong></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                </div>
                @endif
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
                            Bendes Ranglijst
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
                                            <a href="{{ route('gangs.show', $g['id']) }}" class="w-10 h-10 rounded-xl bg-purple-100 hover:bg-purple-200 text-purple-700 flex items-center justify-center text-base shrink-0 font-bold shadow-xs transition" title="Bekijk openbare bendepagina">
                                                <i class="fa-solid {{ $g['icon'] }}"></i>
                                            </a>
                                            <div>
                                                <div class="font-extrabold text-slate-900 text-xs leading-tight flex items-center gap-1.5">
                                                    <a href="{{ route('gangs.show', $g['id']) }}" class="hover:text-purple-600 transition flex items-center gap-1.5" title="Bekijk openbare bendepagina">
                                                        <span>{{ $g['name'] }}</span>
                                                        <span class="text-purple-600 font-mono text-[10px] font-bold">[{{ $g['tag'] }}]</span>
                                                    </a>
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
                                        <div class="inline-flex items-center gap-1.5 justify-end">
                                            <a href="{{ route('gangs.show', $g['id']) }}" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition inline-flex items-center gap-1" title="Bekijk openbare bendepagina & statistieken">
                                                <i class="fa-solid fa-eye text-[10px]"></i>
                                                <span class="hidden sm:inline">Bekijk</span>
                                            </a>
                                            @if($myGang && $g['id'] === $myGang->id)
                                                <span class="px-2.5 py-1 rounded-lg bg-purple-100 text-purple-800 font-extrabold text-[11px] inline-flex items-center gap-1 border border-purple-200">
                                                    <i class="fa-solid fa-check text-[10px]"></i> Jouw Bende
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
                                            @endif
                                        </div>
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

                                        @if((int) $myGang->personal_shopper === (int) $mem->id)
                                            <span class="px-2 py-0.5 rounded-md bg-indigo-50 border border-indigo-200 text-indigo-700 text-[10px] font-bold inline-flex items-center gap-1">
                                                <i class="fa-solid fa-box text-indigo-500 text-[9px]"></i> Kantine Smokkelaar
                                            </span>
                                        @endif
                                        @if((int) $myGang->money_magnet === (int) $mem->id)
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
                                    <input type="hidden" name="decision" value="accept">
                                    <button type="submit" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-xs transition cursor-pointer flex items-center gap-1.5">
                                        <i class="fa-solid fa-check"></i> Accepteren
                                    </button>
                                </form>
                                <form action="{{ route('gangs.handle-request', $req) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="action" value="reject">
                                    <input type="hidden" name="decision" value="reject">
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

            <!-- DEDICATED TAB: BENDE INSTELLINGEN (EIGEN PAGINA MET SUB-TABS) -->
            @if($myGang && $myGang->isOfficer($character))
            <div x-show="activeGangTab === 'instellingen'" 
                 x-data="{ 
                     settingsTab: '{{ session('settings_tab', 'algemeen') }}',
                     previewName: '{{ addslashes($myGang->name) }}',
                     previewTag: '{{ addslashes($myGang->tag) }}'
                 }" 
                 class="space-y-5">

                <!-- Header Banner -->
                <div class="bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 text-[10px] font-black uppercase tracking-wider mb-1.5">
                            <i class="fa-solid fa-sliders"></i> Factie Beheer
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-gear text-purple-600 dark:text-purple-400"></i>
                            <span>Bende Instellingen</span>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-xl">
                            Beheer de naam, toelatingseisen, rollen, visuele identiteit en het manifest van <strong>{{ $myGang->name }}</strong>.
                        </p>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" @click="setGangTab('beheer')" 
                                class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-extrabold transition cursor-pointer flex items-center gap-1.5 border border-slate-200 dark:border-slate-700">
                            <i class="fa-solid fa-users-gear text-slate-500"></i>
                            <span>Naar Ledenbeheer</span>
                        </button>
                    </div>
                </div>

                <!-- SUB-TABS NAVIGATIE BALK -->
                <div class="bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-2 shadow-xs">
                    <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar scroll-smooth">
                        <!-- Tab 1: Naam & Algemeen -->
                        <button type="button" @click="settingsTab = 'algemeen'" 
                                :class="settingsTab === 'algemeen' ? 'bg-purple-600 text-white shadow-xs font-black' : 'bg-slate-50 dark:bg-slate-800/70 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 font-bold border-transparent'"
                                class="px-3.5 py-2 rounded-xl text-xs flex items-center gap-2 transition cursor-pointer shrink-0 border border-slate-200/60 dark:border-slate-700/60">
                            <i class="fa-solid fa-tag text-xs" :class="settingsTab === 'algemeen' ? 'text-white' : 'text-purple-600 dark:text-purple-400'"></i>
                            <span>Naam & Algemeen</span>
                        </button>

                        <!-- Tab 2: Toelating & Regels -->
                        <button type="button" @click="settingsTab = 'toelating'" 
                                :class="settingsTab === 'toelating' ? 'bg-purple-600 text-white shadow-xs font-black' : 'bg-slate-50 dark:bg-slate-800/70 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 font-bold border-transparent'"
                                class="px-3.5 py-2 rounded-xl text-xs flex items-center gap-2 transition cursor-pointer shrink-0 border border-slate-200/60 dark:border-slate-700/60">
                            <i class="fa-solid fa-door-open text-xs" :class="settingsTab === 'toelating' ? 'text-white' : 'text-emerald-500'"></i>
                            <span>Toelating & Regels</span>
                            @if(($myGang->min_respect ?? 0) > 0)
                                <span class="px-1.5 py-0.2 rounded-full text-[9px] font-mono font-black" :class="settingsTab === 'toelating' ? 'bg-white/20 text-white' : 'bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300'">
                                    {{ $myGang->min_respect }}
                                </span>
                            @endif
                        </button>

                        <!-- Tab 3: Rollen & Management -->
                        <button type="button" @click="settingsTab = 'rollen'" 
                                :class="settingsTab === 'rollen' ? 'bg-purple-600 text-white shadow-xs font-black' : 'bg-slate-50 dark:bg-slate-800/70 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 font-bold border-transparent'"
                                class="px-3.5 py-2 rounded-xl text-xs flex items-center gap-2 transition cursor-pointer shrink-0 border border-slate-200/60 dark:border-slate-700/60">
                            <i class="fa-solid fa-id-badge text-xs" :class="settingsTab === 'rollen' ? 'text-white' : 'text-indigo-500'"></i>
                            <span>Rollen & Management</span>
                        </button>

                        <!-- Tab 4: Banner & Embleem -->
                        <button type="button" @click="settingsTab = 'visueel'" 
                                :class="settingsTab === 'visueel' ? 'bg-purple-600 text-white shadow-xs font-black' : 'bg-slate-50 dark:bg-slate-800/70 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 font-bold border-transparent'"
                                class="px-3.5 py-2 rounded-xl text-xs flex items-center gap-2 transition cursor-pointer shrink-0 border border-slate-200/60 dark:border-slate-700/60">
                            <i class="fa-solid fa-palette text-xs" :class="settingsTab === 'visueel' ? 'text-white' : 'text-rose-500'"></i>
                            <span>Banner & Embleem</span>
                        </button>

                        <!-- Tab 5: Publiek Manifest -->
                        <button type="button" @click="settingsTab = 'manifest'" 
                                :class="settingsTab === 'manifest' ? 'bg-purple-600 text-white shadow-xs font-black' : 'bg-slate-50 dark:bg-slate-800/70 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 font-bold border-transparent'"
                                class="px-3.5 py-2 rounded-xl text-xs flex items-center gap-2 transition cursor-pointer shrink-0 border border-slate-200/60 dark:border-slate-700/60">
                            <i class="fa-solid fa-pen-nib text-xs" :class="settingsTab === 'manifest' ? 'text-white' : 'text-amber-500'"></i>
                            <span>Publiek Manifest</span>
                        </button>
                    </div>
                </div>

                <!-- ======================================================== -->
                <!-- SUBTAB 1: NAAM & ALGEMEEN                                 -->
                <!-- ======================================================== -->
                <div x-show="settingsTab === 'algemeen'" class="space-y-5">
                    <!-- Bendenaam en Tag Wijzigen Card -->
                    <div class="bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                        <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                                    <i class="fa-solid fa-pen-to-square text-purple-600 dark:text-purple-400"></i>
                                    <span>Bendenaam & Tag Aanpassen</span>
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    Pas de officiële naam en de factie tag van je bende aan. Dit wordt overal in de kliniek en ranglijsten direct zichtbaar.
                                </p>
                            </div>
                            @if($character->gang_role === 'leader')
                                <span class="px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[10px] font-black uppercase tracking-wider self-start sm:self-auto">
                                    <i class="fa-solid fa-crown text-amber-500"></i> Bendeleider Bevoegdheid
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 text-[10px] font-bold self-start sm:self-auto">
                                    <i class="fa-solid fa-lock text-slate-400"></i> Alleen Bendeleider
                                </span>
                            @endif
                        </div>

                        <!-- Live Badge Preview -->
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60 flex items-center justify-between flex-wrap gap-3">
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-bold">Voorbeeld weergave:</span>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md bg-purple-600 text-white font-mono text-xs font-black uppercase shadow-2xs" x-text="'[' + (previewTag || 'TAG').toUpperCase() + ']'"></span>
                                <span class="text-sm font-black text-slate-900 dark:text-white" x-text="previewName || 'Bendenaam'"></span>
                            </div>
                        </div>

                        @if($character->gang_role === 'leader')
                        <form action="{{ route('gangs.settings') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="action_type" value="name">

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Bendenaam (3 - 35 tekens)
                                    </label>
                                    <input type="text" name="name" x-model="previewName" value="{{ $myGang->name }}" minlength="3" maxlength="35" required 
                                           class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-3.5 py-2.5 text-xs font-bold focus:outline-none focus:border-purple-600 focus:bg-white dark:focus:bg-slate-800 transition">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Bende Tag (2 - 5 letters/cijfers)
                                    </label>
                                    <input type="text" name="tag" x-model="previewTag" value="{{ $myGang->tag }}" minlength="2" maxlength="5" required 
                                           class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white uppercase font-mono rounded-xl px-3.5 py-2.5 text-xs font-black focus:outline-none focus:border-purple-600 focus:bg-white dark:focus:bg-slate-800 transition">
                                </div>
                            </div>

                            <button type="submit" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs shadow-xs transition cursor-pointer flex items-center gap-1.5">
                                <i class="fa-solid fa-floppy-disk"></i>
                                <span>Bendenaam & Tag Opslaan</span>
                            </button>
                        </form>
                        @else
                        <p class="text-xs text-slate-400 dark:text-slate-500 italic py-2">
                            Alleen de oprichter/bendeleider kan de naam of de tag van de bende wijzigen.
                        </p>
                        @endif
                    </div>

                    <!-- MOTD / Slogan & Embleem Card -->
                    <div class="bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                        <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                                <i class="fa-solid fa-bullhorn text-purple-600 dark:text-purple-400"></i>
                                <span>Bende Slogan & Specialiteit</span>
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                Korte factieleus en specialiteit die getoond worden op het bendedashboard en in de lijsten.
                            </p>
                        </div>

                        <form action="{{ route('gangs.settings') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="action_type" value="general_info">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Bende Slogan / Bericht van de Dag (MOTD)
                                    </label>
                                    <input type="text" name="motd" value="{{ $myGang->motd ?? '' }}" maxlength="150" placeholder="Bijv. Wie niet sterk is moet slim zijn..."
                                           class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-3.5 py-2.5 text-xs font-bold focus:outline-none focus:border-purple-600 focus:bg-white dark:focus:bg-slate-800 transition">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Bende Specialiteit / Focus
                                    </label>
                                    <input type="text" name="specialty" value="{{ $myGang->specialty ?? 'Smokkel & Handel' }}" maxlength="50"
                                           class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-3.5 py-2.5 text-xs font-bold focus:outline-none focus:border-purple-600 focus:bg-white dark:focus:bg-slate-800 transition">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Embleem Icoon (FontAwesome class)
                                    </label>
                                    <input type="text" name="emblem" value="{{ $myGang->emblem ?? 'fa-crown' }}" maxlength="50"
                                           class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-3.5 py-2.5 text-xs font-mono font-bold focus:outline-none focus:border-purple-600 focus:bg-white dark:focus:bg-slate-800 transition">
                                </div>
                            </div>

                            <button type="submit" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs shadow-xs transition cursor-pointer flex items-center gap-1.5">
                                <i class="fa-solid fa-check"></i>
                                <span>Algemene Gegevens Opslaan</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- ======================================================== -->
                <!-- SUBTAB 2: TOELATING & REGELS                              -->
                <!-- ======================================================== -->
                <div x-show="settingsTab === 'toelating'" class="space-y-5">
                    <!-- Lidmaatschapsverzoeken Toggle Card -->
                    <div class="bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                        <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                                    <i class="fa-solid fa-user-shield text-purple-600 dark:text-purple-400"></i>
                                    <span>Lidmaatschapsverzoeken (Toelating)</span>
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    Bepaal of nieuwe patiënten een verzoek mogen indienen om lid te worden van <strong>{{ $myGang->name }}</strong>.
                                </p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-[11px] font-black self-start sm:self-auto {{ $myGang->is_open ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800' }}">
                                <i class="fa-solid {{ $myGang->is_open ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                                {{ $myGang->is_open ? 'Verzoeken Toegestaan (Open)' : 'Verzoeken Uitgeschakeld (Gesloten)' }}
                            </span>
                        </div>

                        <form action="{{ route('gangs.settings') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="action_type" value="requests_toggle">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="flex items-start gap-3 p-4 rounded-xl border cursor-pointer transition {{ $myGang->is_open ? 'border-purple-400 dark:border-purple-600 bg-purple-50/40 dark:bg-purple-950/30 shadow-2xs' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 hover:border-slate-300' }}">
                                    <input type="radio" name="is_open" value="1" {{ $myGang->is_open ? 'checked' : '' }} class="mt-0.5 text-purple-600 focus:ring-purple-500">
                                    <div class="text-xs">
                                        <span class="font-extrabold text-slate-900 dark:text-white flex items-center gap-1.5">
                                            <i class="fa-solid fa-door-open text-emerald-600 dark:text-emerald-400"></i>
                                            <span>Verzoeken Inschakelen (Open)</span>
                                        </span>
                                        <span class="text-slate-500 dark:text-slate-400 block text-[11px] mt-1 leading-relaxed">
                                            Spelers die aan de toelatingseis voldoen kunnen via de benderanglijst een verzoek indienen. De leiding kan verzoeken goedkeuren of afwijzen.
                                        </span>
                                    </div>
                                </label>

                                <label class="flex items-start gap-3 p-4 rounded-xl border cursor-pointer transition {{ ! $myGang->is_open ? 'border-purple-400 dark:border-purple-600 bg-purple-50/40 dark:bg-purple-950/30 shadow-2xs' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 hover:border-slate-300' }}">
                                    <input type="radio" name="is_open" value="0" {{ ! $myGang->is_open ? 'checked' : '' }} class="mt-0.5 text-purple-600 focus:ring-purple-500">
                                    <div class="text-xs">
                                        <span class="font-extrabold text-slate-900 dark:text-white flex items-center gap-1.5">
                                            <i class="fa-solid fa-lock text-rose-500"></i>
                                            <span>Verzoeken Uitschakelen (Gesloten)</span>
                                        </span>
                                        <span class="text-slate-500 dark:text-slate-400 block text-[11px] mt-1 leading-relaxed">
                                            Niemand kan een aanvraag indienen. Je bende toont de badge 'Gesloten' in alle ranglijsten.
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
                        <!-- Toelatingseis (Minimaal Respect) -->
                        <div class="bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                            <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center justify-between">
                                <div>
                                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                                        <i class="fa-solid fa-fire text-amber-500"></i>
                                        <span>Toelatingseis (Minimaal Respect)</span>
                                    </h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                        Minimale respectscore die een patiënt moet hebben om lid te kunnen worden.
                                    </p>
                                </div>
                                <span class="px-2.5 py-1 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 font-mono text-xs font-black">
                                    Min. {{ number_format($myGang->min_respect ?? 0, 0, ',', '.') }}
                                </span>
                            </div>

                            <div class="p-3 rounded-xl bg-amber-50/70 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800 text-xs text-amber-900 dark:text-amber-200 flex items-start gap-2">
                                <i class="fa-solid fa-circle-info text-amber-600 dark:text-amber-400 mt-0.5 shrink-0"></i>
                                <span>Patiënten met minder respect zien direct een vergrendelde knop met <em>"Min. Respect Vereist"</em> en kunnen geen aanvraag verzenden. Vul <strong>0</strong> in voor vrije toelating.</span>
                            </div>

                            <form action="{{ route('gangs.settings') }}" method="POST" class="space-y-4">
                                @csrf
                                <input type="hidden" name="action_type" value="min_respect">

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Minimaal vereist respect (0 voor geen eis)
                                    </label>
                                    <div class="relative">
                                        <input type="number" name="min_respect" value="{{ $myGang->min_respect ?? 0 }}" min="0" step="50" required
                                               class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-3.5 py-2.5 text-xs font-bold focus:outline-none focus:border-purple-600 focus:bg-white dark:focus:bg-slate-800 transition pr-20">
                                        <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-black text-amber-600 dark:text-amber-400 font-mono">
                                            RESPECT
                                        </span>
                                    </div>
                                </div>

                                <button type="submit" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs shadow-xs transition cursor-pointer flex items-center gap-1.5">
                                    <i class="fa-solid fa-check"></i>
                                    <span>Minimaal Respect Opslaan</span>
                                </button>
                            </form>
                        </div>

                        <!-- Verlatingskosten -->
                        <div class="bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4" 
                             x-data="{ multiplier: {{ min(50, (int) ($myGang->leave_fee_multiplier ?? 48)) }}, hourly: {{ $hourlyWage ?? 0 }} }">
                            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                                    <i class="fa-solid fa-door-open text-purple-600 dark:text-purple-400"></i>
                                    <span>Verlatingskosten (Vertrekboete)</span>
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    Boete die een bendelid betaalt aan de kluis wanneer hij/zij besluit de factie te verlaten (maximaal 50x het uurloon).
                                </p>
                            </div>

                            <form action="{{ route('gangs.settings') }}" method="POST" class="space-y-4">
                                @csrf
                                <input type="hidden" name="action_type" value="leave_fee">

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Factor van het Uurloon (0 - 50x)</label>
                                    <div class="relative">
                                        <input type="number" name="leave_fee_multiplier" x-model.number="multiplier" min="0" max="50"
                                               class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-3.5 py-2.5 text-xs font-bold focus:outline-none focus:border-purple-600 focus:bg-white dark:focus:bg-slate-800 transition pr-28">
                                        <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-black text-purple-700 dark:text-purple-400 font-mono" 
                                              x-text="'€' + ((multiplier || 0) * hourly).toLocaleString('nl-NL')">
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-1">
                                        Huidig uurloon: €{{ number_format($hourlyWage ?? 0, 0, ',', '.') }}. Maximaal 50x = €{{ number_format(($hourlyWage ?? 0) * 50, 0, ',', '.') }}.
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

                <!-- ======================================================== -->
                <!-- SUBTAB 3: ROLLEN & MANAGEMENT                             -->
                <!-- ======================================================== -->
                <div x-show="settingsTab === 'rollen'" class="space-y-5">
                    <div class="bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-5">
                        <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                                    <i class="fa-solid fa-id-badge text-purple-600 dark:text-purple-400"></i>
                                    <span>Bende Rollen & Sleutelfuncties</span>
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    Wijs sleutelposities toe aan betrouwbare bendeleden. Aangewezen leden krijgen direct <strong>Co-Leider bevoegdheden</strong> binnen de kliniek.
                                </p>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-purple-50 dark:bg-purple-950/60 border border-purple-200 dark:border-purple-800 text-[10px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-300 shrink-0 self-start sm:self-auto">
                                <i class="fa-solid fa-shield-halved text-purple-600 dark:text-purple-400"></i>
                                <span>Co-Op Management</span>
                            </span>
                        </div>

                        <!-- Information callout banner with rich styling -->
                        <div class="p-4 rounded-xl bg-purple-50/80 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800/80 text-xs text-purple-950 dark:text-purple-200 space-y-2">
                            <div class="font-black flex items-center gap-2 text-purple-900 dark:text-purple-200">
                                <i class="fa-solid fa-circle-info text-purple-600 dark:text-purple-400"></i>
                                <span>Sleutelfuncties Toelichting & Rechten:</span>
                            </div>
                            <ul class="grid grid-cols-1 md:grid-cols-2 gap-2 text-slate-700 dark:text-slate-300 text-[11px] leading-relaxed">
                                <li class="flex items-start gap-1.5">
                                    <span class="text-amber-500 shrink-0">👑</span>
                                    <div><strong class="text-slate-900 dark:text-white">Bendeleider:</strong> Volledige controle over upgrades, oorlogen, bondgenoten, instellingen en leden.</div>
                                </li>
                                <li class="flex items-start gap-1.5">
                                    <span class="text-purple-500 shrink-0">🎖️</span>
                                    <div><strong class="text-slate-900 dark:text-white">Co-Leider / Onderbaas:</strong> Directe operationele leiding, toelatingen, oorlogscommando en dagelijks beheer.</div>
                                </li>
                                <li class="flex items-start gap-1.5">
                                    <span class="text-indigo-500 shrink-0">📦</span>
                                    <div><strong class="text-slate-900 dark:text-white">Kantine Smokkelaar:</strong> Beheert smokkelkiosken & depot. <span class="text-purple-600 dark:text-purple-400 font-bold">(Automatisch Co-Leider)</span></div>
                                </li>
                                <li class="flex items-start gap-1.5">
                                    <span class="text-emerald-500 shrink-0">💷</span>
                                    <div><strong class="text-slate-900 dark:text-white">Kasbeheerder:</strong> Beheert de kluisdonaties & geldreserves. <span class="text-purple-600 dark:text-purple-400 font-bold">(Automatisch Co-Leider)</span></div>
                                </li>
                            </ul>
                        </div>

                        <form action="{{ route('gangs.settings') }}" method="POST" class="space-y-5">
                            @csrf
                            <input type="hidden" name="action_type" value="management">

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                <!-- 1. Bendeleider -->
                                <div class="space-y-1.5 p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-800">
                                    <label class="text-xs font-black text-slate-900 dark:text-slate-100 flex items-center justify-between">
                                        <span class="flex items-center gap-1.5">
                                            <i class="fa-solid fa-crown text-amber-500"></i> Bendeleider
                                        </span>
                                        <span class="text-[9px] font-bold text-amber-600 dark:text-amber-400 uppercase">Opperbaas</span>
                                    </label>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-tight">Oprichter & hoogste baas van de bende.</p>
                                    <select name="boss_babe_id" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl px-3 py-2 text-xs font-bold focus:outline-none focus:border-purple-600 transition">
                                        @foreach($myGang->members as $m)
                                            <option value="{{ $m->id }}" {{ (int) $myGang->leader_character_id === (int) $m->id ? 'selected' : '' }}>
                                                {{ $m->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- 2. Co-Leider / Onderbaas -->
                                <div class="space-y-1.5 p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-800">
                                    <label class="text-xs font-black text-slate-900 dark:text-slate-100 flex items-center justify-between">
                                        <span class="flex items-center gap-1.5">
                                            <i class="fa-solid fa-shield-halved text-purple-600 dark:text-purple-400"></i> Co-Leider
                                        </span>
                                        <span class="text-[9px] font-bold text-purple-600 dark:text-purple-400 uppercase">Onderbaas</span>
                                    </label>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-tight">Tweede bevelvoerder met co-op rechten.</p>
                                    <select name="co_leader_id" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl px-3 py-2 text-xs font-bold focus:outline-none focus:border-purple-600 transition">
                                        <option value="">Geen aangewezen</option>
                                        @foreach($myGang->members as $m)
                                            @if((int) $myGang->leader_character_id !== (int) $m->id)
                                                <option value="{{ $m->id }}" {{ $m->gang_role === 'co_leader' && (int) $myGang->personal_shopper !== (int) $m->id && (int) $myGang->money_magnet !== (int) $m->id ? 'selected' : '' }}>
                                                    {{ $m->name }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <!-- 3. Kantine Smokkelaar -->
                                <div class="space-y-1.5 p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-800">
                                    <label class="text-xs font-black text-slate-900 dark:text-slate-100 flex items-center justify-between">
                                        <span class="flex items-center gap-1.5">
                                            <i class="fa-solid fa-box text-indigo-500"></i> Kantine Smokkelaar
                                        </span>
                                        <span class="text-[9px] font-bold text-indigo-600 dark:text-indigo-400 uppercase">Kiosken</span>
                                    </label>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-tight">Beheert kiosks & bevoorrading. (Co-op)</p>
                                    <select name="personal_shopper_id" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl px-3 py-2 text-xs font-bold focus:outline-none focus:border-purple-600 transition">
                                        <option value="">Geen aangewezen</option>
                                        @foreach($myGang->members as $m)
                                            <option value="{{ $m->id }}" {{ (int) $myGang->personal_shopper === (int) $m->id ? 'selected' : '' }}>
                                                {{ $m->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- 4. Kasbeheerder -->
                                <div class="space-y-1.5 p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-800">
                                    <label class="text-xs font-black text-slate-900 dark:text-slate-100 flex items-center justify-between">
                                        <span class="flex items-center gap-1.5">
                                            <i class="fa-solid fa-vault text-emerald-600 dark:text-emerald-400"></i> Kasbeheerder
                                        </span>
                                        <span class="text-[9px] font-bold text-emerald-600 dark:text-emerald-400 uppercase">Kluis</span>
                                    </label>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-tight">Heeft toezicht & opnamerechten. (Co-op)</p>
                                    <select name="money_magnet_id" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl px-3 py-2 text-xs font-bold focus:outline-none focus:border-purple-600 transition">
                                        <option value="">Geen aangewezen</option>
                                        @foreach($myGang->members as $m)
                                            <option value="{{ $m->id }}" {{ (int) $myGang->money_magnet === (int) $m->id ? 'selected' : '' }}>
                                                {{ $m->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="pt-2 flex items-center justify-between flex-wrap gap-3">
                                <button type="submit" class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs shadow-md transition cursor-pointer flex items-center gap-2">
                                    <i class="fa-solid fa-check"></i>
                                    <span>Sleutelfuncties Opslaan</span>
                                </button>
                                <span class="text-[11px] text-slate-400 flex items-center gap-1">
                                    <i class="fa-solid fa-bolt text-amber-500"></i> Wijzigingen worden direct van kracht
                                </span>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- ======================================================== -->
                <!-- SUBTAB 4: HUISSTIJL & BANNER                              -->
                <!-- ======================================================== -->
                <div x-show="settingsTab === 'visueel'" class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Avatar Card -->
                        <div class="bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                            <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center justify-between">
                                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                                    <i class="fa-solid fa-image text-purple-600 dark:text-purple-400"></i>
                                    <span>Bende Embleem / Avatar</span>
                                </h3>
                                <span class="text-[11px] text-slate-400 font-medium">JPG, PNG, WEBP, GIF (max. 10MB)</span>
                            </div>

                            <div class="flex items-center gap-4">
                                <img src="{{ $myGang->avatar_url }}" alt="Gang Avatar" 
                                     class="w-20 h-20 rounded-2xl object-cover border-2 border-slate-200 dark:border-slate-700 shadow-sm shrink-0">
                                
                                <form action="{{ route('gangs.settings') }}" method="POST" enctype="multipart/form-data" class="flex-1 space-y-3">
                                    @csrf
                                    <input type="hidden" name="action_type" value="avatar">
                                    <input type="file" name="avatar" accept="image/png,image/jpeg,image/webp,image/gif,image/*" required 
                                           @change="if ($event.target.files[0] && $event.target.files[0].size > 10 * 1024 * 1024) { alert('De gekozen avatar is groter dan 10MB. Selecteer een kleiner bestand.'); $event.target.value = ''; }"
                                           class="block w-full text-xs text-slate-600 dark:text-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-50 dark:file:bg-purple-950 file:text-purple-700 dark:file:text-purple-300 hover:file:bg-purple-100 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-1.5 cursor-pointer">
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-xs transition cursor-pointer flex items-center gap-1.5">
                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                        <span>Avatar Uploaden</span>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Banner Card -->
                        <div class="bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                            <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center justify-between">
                                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                                    <i class="fa-solid fa-panorama text-purple-600 dark:text-purple-400"></i>
                                    <span>Achtergrond Banner</span>
                                </h3>
                                <span class="text-[11px] text-slate-400 font-medium">JPG, PNG, WEBP, GIF (max. 12MB)</span>
                            </div>

                            @if($myGang->banner)
                            <div class="h-24 w-full rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 shadow-2xs">
                                <img src="{{ $myGang->banner_url }}" alt="Huidige Banner" class="w-full h-full object-cover">
                            </div>
                            @endif

                            <form action="{{ route('gangs.settings') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                                @csrf
                                <input type="hidden" name="action_type" value="banner">
                                <input type="file" name="banner" accept="image/png,image/jpeg,image/webp,image/gif,image/*" required 
                                       @change="if ($event.target.files[0] && $event.target.files[0].size > 12 * 1024 * 1024) { alert('De gekozen banner is groter dan 12MB. Selecteer een kleiner bestand.'); $event.target.value = ''; }"
                                       class="block w-full text-xs text-slate-600 dark:text-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-50 dark:file:bg-purple-950 file:text-purple-700 dark:file:text-purple-300 hover:file:bg-purple-100 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-1.5 cursor-pointer">
                                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-xs transition cursor-pointer flex items-center gap-1.5">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                    <span>Banner Uploaden</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ======================================================== -->
                <!-- SUBTAB 5: PUBLIEK MANIFEST                                -->
                <!-- ======================================================== -->
                <div x-show="settingsTab === 'manifest'" class="space-y-5">
                    <div class="bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                        <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                                <i class="fa-solid fa-pen-nib text-purple-600 dark:text-purple-400"></i>
                                <span>Publiek Bende Manifest & Regels</span>
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                Schrijf het officiële manifest van de bende. Dit wordt getoond op de publieke bende pagina voor alle patiënten in de kliniek.
                            </p>
                        </div>

                        <form action="{{ route('gangs.settings') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="action_type" value="profile">

                            <div class="border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden bg-white dark:bg-slate-900 shadow-2xs">
                                <!-- Clean BBCode Toolbar -->
                                <div class="flex flex-wrap items-center gap-1 p-2 bg-slate-50 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs">
                                    <button type="button" onclick="insertGangBbcode('[img]', '[/img]')" title="Afbeelding" class="w-7 h-7 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center transition cursor-pointer">
                                        <i class="fa-regular fa-image"></i>
                                    </button>
                                    <button type="button" onclick="insertGangBbcode('[video]', '[/video]')" title="Video" class="w-7 h-7 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center transition cursor-pointer">
                                        <i class="fa-regular fa-circle-play"></i>
                                    </button>
                                    <button type="button" onclick="insertGangBbcode('[url]', '[/url]')" title="Link" class="w-7 h-7 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center transition cursor-pointer">
                                        <i class="fa-solid fa-link"></i>
                                    </button>
                                    <button type="button" onclick="insertGangBbcode('😊', '')" title="Emoji" class="w-7 h-7 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center transition cursor-pointer">
                                        <i class="fa-regular fa-face-smile"></i>
                                    </button>
                                    <div class="w-px h-4 bg-slate-200 dark:bg-slate-700 mx-1"></div>
                                    <button type="button" onclick="insertGangBbcode('[b]', '[/b]')" title="Vetgedrukt" class="w-7 h-7 font-black rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center transition cursor-pointer">
                                        B
                                    </button>
                                    <button type="button" onclick="insertGangBbcode('[i]', '[/i]')" title="Cursief" class="w-7 h-7 italic font-serif rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center transition cursor-pointer">
                                        I
                                    </button>
                                    <button type="button" onclick="insertGangBbcode('[u]', '[/u]')" title="Onderstreept" class="w-7 h-7 underline rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center transition cursor-pointer">
                                        U
                                    </button>
                                    <button type="button" onclick="insertGangBbcode('[s]', '[/s]')" title="Doorgehaald" class="w-7 h-7 line-through rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center transition cursor-pointer">
                                        S
                                    </button>
                                    <div class="w-px h-4 bg-slate-200 dark:bg-slate-700 mx-1"></div>
                                    <button type="button" onclick="insertGangBbcode('[left]', '[/left]')" title="Links uitlijnen" class="w-7 h-7 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center transition cursor-pointer">
                                        <i class="fa-solid fa-align-left"></i>
                                    </button>
                                    <button type="button" onclick="insertGangBbcode('[center]', '[/center]')" title="Centreren" class="w-7 h-7 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center transition cursor-pointer">
                                        <i class="fa-solid fa-align-center"></i>
                                    </button>
                                    <button type="button" onclick="insertGangBbcode('[right]', '[/right]')" title="Rechts uitlijnen" class="w-7 h-7 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center transition cursor-pointer">
                                        <i class="fa-solid fa-align-right"></i>
                                    </button>
                                </div>

                                <!-- Textarea -->
                                <textarea id="gangProfileEditor" name="description" rows="8" placeholder="Schrijf hier het manifest van je factie..." 
                                          class="w-full bg-white dark:bg-slate-900 p-4 text-xs font-medium text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none resize-y leading-relaxed">{{ $myGang->description }}</textarea>
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
                            <div class="w-10 h-10 rounded-lg border flex items-center justify-center text-lg font-black shrink-0"
                                 :class="attackCooldown > 0 ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-emerald-100 text-emerald-700 border-emerald-200'">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-slate-500 uppercase block">Aanvalsstatus</span>
                                <div x-show="attackCooldown > 0" style="{{ ($myGang && $myGang->attackCooldownRemaining() > 0) ? '' : 'display: none;' }}">
                                    <span class="text-xs font-black text-amber-800 font-mono flex items-center gap-1.5">
                                        <i class="fa-solid fa-stopwatch text-amber-600"></i> Wachten: <span x-text="formatTime(attackCooldown)">{{ $myGang ? gmdate('i:s', $myGang->attackCooldownRemaining()) : '' }}</span>
                                    </span>
                                </div>
                                <div x-show="attackCooldown <= 0" style="{{ ($myGang && $myGang->attackCooldownRemaining() <= 0) ? '' : 'display: none;' }}">
                                    <span class="text-xs font-black text-emerald-700 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Gereed voor aanval!
                                    </span>
                                </div>
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
                                            @php
                                                $otherAttackBlocked = ! $myGang->isOfficer($character) || $isAlly || $hasShield;
                                            @endphp
                                            <button type="submit" 
                                                    :disabled="attackCooldown > 0 || {{ $otherAttackBlocked ? 'true' : 'false' }}"
                                                    :class="(attackCooldown > 0 || {{ $otherAttackBlocked ? 'true' : 'false' }}) ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer'"
                                                    @if(! $canAttack) disabled @endif
                                                    class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-black uppercase tracking-wider transition shadow-xs flex items-center gap-2">
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
                        Je bent momenteel aangesloten bij <strong>{{ $myGang->name }}</strong> [{{ $myGang->tag }}]. Je kunt maximaal in 1 bende tegelijk zitten. Om een nieuwe bende op te richten, moet je eerst je huidige bende verlaten via het tabblad <em>Mijn Bende</em>.
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
