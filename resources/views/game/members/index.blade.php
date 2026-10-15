@extends('layouts.app')

@section('title', 'Ledenlijst & Online Patiënten - TBS Kliniek')
@section('meta_description', 'Realtime overzicht van alle patiënten en online leden in de TBS Kliniek. Zoek direct op naam, bekijk activiteit, bende-lidmaatschappen en neem contact op.')

@section('content')
<div class="space-y-6"
     x-data="{
         search: '{{ addslashes($initialSearch) }}',
         filter: '{{ in_array($initialFilter, ['all', 'online', 'offline', 'gang', 'solo', 'vip', 'prison']) ? $initialFilter : 'all' }}',
         sortBy: '{{ $initialFilter === 'online' ? 'online_first' : 'respect_desc' }}',
         viewMode: 'grid',
         members: {{ Js::from($membersData) }},
         get filteredList() {
             let q = this.search.toLowerCase().trim();
             return this.members.filter(m => {
                 if (q) {
                     let matchName = m.name.toLowerCase().includes(q);
                     let matchTitle = (m.title || '').toLowerCase().includes(q);
                     let matchGang = (m.gang_name || '').toLowerCase().includes(q) || (m.gang_tag || '').toLowerCase().includes(q);
                     if (!matchName && !matchTitle && !matchGang) return false;
                 }
                 if (this.filter === 'online' && !m.is_online) return false;
                 if (this.filter === 'offline' && m.is_online) return false;
                 if (this.filter === 'gang' && !m.gang_id) return false;
                 if (this.filter === 'solo' && m.gang_id) return false;
                 if (this.filter === 'vip' && !m.is_vip) return false;
                 if (this.filter === 'prison' && !m.is_in_prison) return false;
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
         get currentOnlineCount() {
             return this.members.filter(m => m.is_online).length;
         },
         get currentOfflineCount() {
             return this.members.filter(m => !m.is_online).length;
         },
         get currentGangCount() {
             return this.members.filter(m => m.gang_id).length;
         },
         get currentSoloCount() {
             return this.members.filter(m => !m.gang_id).length;
         },
         get currentPrisonCount() {
             return this.members.filter(m => m.is_in_prison).length;
         },
         get currentVipCount() {
             return this.members.filter(m => m.is_vip).length;
         }
     }">

    <!-- Top Hero Header Banner -->
    <div class="bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xs relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
            <div>
                <div class="inline-flex items-center gap-2 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-xs font-bold px-3 py-1 rounded-full border border-emerald-200 dark:border-emerald-800 mb-2.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Patiëntenregister & Live Activiteit</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                    Ledenlijst & Online Patiënten
                </h1>
                <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-1 max-w-2xl leading-relaxed">
                    Overzicht van alle ingeschreven patiënten in de TBS kliniek. Zoek direct realtime op naam of bende, bekijk wie er nu rondlopen op de afdeling en leg contact via privéberichten.
                </p>
            </div>

            <!-- Header Quick Badge -->
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <a href="{{ route('leaderboard.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-200 dark:border-slate-700 transition flex items-center gap-2 shadow-2xs">
                    <i class="fa-solid fa-trophy text-amber-500"></i>
                    <span>Ranglijsten</span>
                </a>
                <a href="{{ route('gangs.index') }}" class="px-3.5 py-2 rounded-xl bg-purple-50 dark:bg-purple-950/60 hover:bg-purple-100 dark:hover:bg-purple-900/60 text-purple-700 dark:text-purple-300 text-xs font-bold border border-purple-200 dark:border-purple-800 transition flex items-center gap-2 shadow-2xs">
                    <i class="fa-solid fa-users text-purple-600 dark:text-purple-400"></i>
                    <span>Bendes & Gangs</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 5 Summary Stat Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
        <button type="button" @click="filter = 'all'" 
                :class="filter === 'all' ? 'ring-2 ring-purple-600 bg-purple-50/60 dark:bg-purple-950/40 border-purple-300' : 'bg-white dark:bg-[#111827] border-slate-200/80 dark:border-slate-800 hover:border-slate-300'"
                class="border rounded-2xl p-4 text-left transition cursor-pointer shadow-xs space-y-1 group">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 block flex items-center justify-between">
                <span>Totaal Leden</span>
                <i class="fa-solid fa-users text-purple-500 group-hover:scale-110 transition-transform"></i>
            </span>
            <div class="text-xl font-black text-slate-900 dark:text-slate-100 font-mono">{{ $totalCount }}</div>
            <span class="text-[10px] text-slate-500 dark:text-slate-400 block font-medium">Geregistreerd in kliniek</span>
        </button>

        <button type="button" @click="filter = 'online'" 
                :class="filter === 'online' ? 'ring-2 ring-emerald-600 bg-emerald-50/60 dark:bg-emerald-950/40 border-emerald-300' : 'bg-white dark:bg-[#111827] border-slate-200/80 dark:border-slate-800 hover:border-slate-300'"
                class="border rounded-2xl p-4 text-left transition cursor-pointer shadow-xs space-y-1 group">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 block flex items-center justify-between">
                <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Nu Online</span>
                <i class="fa-solid fa-wifi text-emerald-500 group-hover:scale-110 transition-transform"></i>
            </span>
            <div class="text-xl font-black text-emerald-700 dark:text-emerald-300 font-mono" x-text="currentOnlineCount">{{ $onlineCount }}</div>
            <span class="text-[10px] text-emerald-600/80 dark:text-emerald-400/80 block font-medium">Actief afgelopen 5 min</span>
        </button>

        <button type="button" @click="filter = 'gang'" 
                :class="filter === 'gang' ? 'ring-2 ring-indigo-600 bg-indigo-50/60 dark:bg-indigo-950/40 border-indigo-300' : 'bg-white dark:bg-[#111827] border-slate-200/80 dark:border-slate-800 hover:border-slate-300'"
                class="border rounded-2xl p-4 text-left transition cursor-pointer shadow-xs space-y-1 group">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 block flex items-center justify-between">
                <span>In een Bende</span>
                <i class="fa-solid fa-shield-halved text-indigo-500 group-hover:scale-110 transition-transform"></i>
            </span>
            <div class="text-xl font-black text-slate-900 dark:text-slate-100 font-mono" x-text="currentGangCount">{{ $gangCount }}</div>
            <span class="text-[10px] text-slate-500 dark:text-slate-400 block font-medium">Aangesloten bij factie</span>
        </button>

        <button type="button" @click="filter = 'solo'" 
                :class="filter === 'solo' ? 'ring-2 ring-amber-600 bg-amber-50/60 dark:bg-amber-950/40 border-amber-300' : 'bg-white dark:bg-[#111827] border-slate-200/80 dark:border-slate-800 hover:border-slate-300'"
                class="border rounded-2xl p-4 text-left transition cursor-pointer shadow-xs space-y-1 group">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 block flex items-center justify-between">
                <span>Solitair / Vrij</span>
                <i class="fa-solid fa-user text-amber-500 group-hover:scale-110 transition-transform"></i>
            </span>
            <div class="text-xl font-black text-slate-900 dark:text-slate-100 font-mono" x-text="currentSoloCount">{{ $soloCount }}</div>
            <span class="text-[10px] text-slate-500 dark:text-slate-400 block font-medium">Beschikbaar voor bende</span>
        </button>

        <button type="button" @click="filter = 'prison'" 
                :class="filter === 'prison' ? 'ring-2 ring-rose-600 bg-rose-50/60 dark:bg-rose-950/40 border-rose-300' : 'bg-white dark:bg-[#111827] border-slate-200/80 dark:border-slate-800 hover:border-slate-300'"
                class="border rounded-2xl p-4 text-left transition cursor-pointer shadow-xs space-y-1 group">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-rose-500 block flex items-center justify-between">
                <span>Isoleercel</span>
                <i class="fa-solid fa-lock text-rose-500 group-hover:scale-110 transition-transform"></i>
            </span>
            <div class="text-xl font-black text-rose-600 dark:text-rose-400 font-mono" x-text="currentPrisonCount">{{ $prisonCount }}</div>
            <span class="text-[10px] text-rose-500/80 block font-medium">Momenteel opgesloten</span>
        </button>
    </div>

    <!-- Search & Filters Toolbar -->
    <div class="bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs space-y-4">
        
        <!-- Search Input Bar -->
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input type="text"
                   id="members-search-input"
                   x-model.debounce.150ms="search"
                   placeholder="Zoek patiënt op naam, titel of bende (realtime typen)..."
                   class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl pl-10 pr-10 py-3 text-xs sm:text-sm font-semibold focus:outline-none focus:border-purple-600 focus:bg-white dark:focus:bg-slate-900 transition shadow-inner">
            <button type="button"
                    x-show="search.length > 0"
                    @click="search = ''"
                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-sm cursor-pointer"
                    title="Zoekopdracht wissen">
                <i class="fa-solid fa-circle-xmark"></i>
            </button>
        </div>

        <!-- Filter Pills & Sorting Row -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3.5 pt-1">
            <!-- Filter Pills -->
            <div class="flex flex-wrap items-center gap-1.5">
                <button type="button"
                        id="members-status-all"
                        @click="filter = 'all'"
                        :class="filter === 'all' ? 'bg-purple-600 text-white font-extrabold shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                        class="px-3 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-1.5">
                    <span>Alle Patiënten</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="filter === 'all' ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300'" x-text="members.length"></span>
                </button>

                <button type="button"
                        id="members-status-online"
                        @click="filter = 'online'"
                        :class="filter === 'online' ? 'bg-emerald-600 text-white font-extrabold shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                        class="px-3 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full" :class="filter === 'online' ? 'bg-white' : 'bg-emerald-500 animate-pulse'"></span>
                    <span>Nu Online</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="filter === 'online' ? 'bg-white/20 text-white' : 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300'" x-text="currentOnlineCount"></span>
                </button>

                <button type="button"
                        @click="filter = 'offline'"
                        :class="filter === 'offline' ? 'bg-slate-700 text-white font-extrabold shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                        class="px-3 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-1.5">
                    <span>Offline</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="filter === 'offline' ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300'" x-text="currentOfflineCount"></span>
                </button>

                <button type="button"
                        @click="filter = 'gang'"
                        :class="filter === 'gang' ? 'bg-indigo-600 text-white font-extrabold shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                        class="px-3 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-users text-[10px]"></i>
                    <span>Met Bende</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="filter === 'gang' ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300'" x-text="currentGangCount"></span>
                </button>

                <button type="button"
                        @click="filter = 'solo'"
                        :class="filter === 'solo' ? 'bg-amber-600 text-white font-extrabold shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                        class="px-3 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-user-plus text-[10px]"></i>
                    <span>Zonder Bende</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="filter === 'solo' ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300'" x-text="currentSoloCount"></span>
                </button>

                <button type="button"
                        @click="filter = 'vip'"
                        :class="filter === 'vip' ? 'bg-amber-500 text-white font-extrabold shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                        class="px-3 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-crown text-[10px] text-amber-300"></i>
                    <span>VIP</span>
                </button>
            </div>

            <!-- Sorting & View Controls -->
            <div class="flex items-center gap-3 shrink-0">
                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-bold text-slate-400 hidden sm:inline">Sorteren:</span>
                    <select x-model="sortBy"
                            id="members-sort-select"
                            class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-3 py-1.5 text-xs font-bold focus:outline-none focus:border-purple-600 transition cursor-pointer">
                        <option value="online_first">🟢 Online Eerst</option>
                        <option value="respect_desc">Hoogste Respect</option>
                        <option value="level_desc">Hoogste Level</option>
                        <option value="name_asc">Naam (A-Z)</option>
                    </select>
                </div>

                <!-- View Mode Switcher (Grid vs Table) -->
                <div class="flex items-center bg-slate-100 dark:bg-slate-800 p-1 rounded-xl border border-slate-200 dark:border-slate-700">
                    <button type="button"
                            @click="viewMode = 'grid'"
                            :class="viewMode === 'grid' ? 'bg-white dark:bg-slate-700 text-purple-600 dark:text-purple-300 shadow-2xs' : 'text-slate-400 hover:text-slate-600'"
                            class="p-1.5 rounded-lg text-xs transition cursor-pointer"
                            title="Kaartenoverzicht">
                        <i class="fa-solid fa-grip"></i>
                    </button>
                    <button type="button"
                            @click="viewMode = 'table'"
                            :class="viewMode === 'table' ? 'bg-white dark:bg-slate-700 text-purple-600 dark:text-purple-300 shadow-2xs' : 'text-slate-400 hover:text-slate-600'"
                            class="p-1.5 rounded-lg text-xs transition cursor-pointer"
                            title="Tabeloverzicht">
                        <i class="fa-solid fa-list"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Filter Results Counter Line -->
        <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 pt-2 border-t border-slate-100 dark:border-slate-800/80">
            <div>
                Resultaten: <strong class="text-slate-900 dark:text-slate-100 font-mono" x-text="filteredList.length"></strong> patiënten getoond
                <template x-if="search">
                    <span>voor zoekterm "<strong class="text-purple-600" x-text="search"></strong>"</span>
                </template>
            </div>
            <template x-if="search || filter !== 'all'">
                <button type="button" @click="search = ''; filter = 'all';" class="text-xs text-purple-600 hover:underline font-bold cursor-pointer">
                    Herstel filters
                </button>
            </template>
        </div>
    </div>

    <!-- 1. GRID VIEW MODE (Rich Cards) -->
    <div x-show="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <template x-for="m in filteredList" :key="m.id">
            <div class="bg-white dark:bg-[#111827] border rounded-2xl p-5 shadow-xs transition space-y-4 hover:shadow-md"
                 :class="{
                     'border-purple-300 dark:border-purple-800/80 bg-purple-50/20': m.is_self,
                     'border-slate-200/90 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700': !m.is_self
                 }">
                <!-- Top Card: Avatar, Name, Title, Online Status -->
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <a :href="m.profile_url" class="relative shrink-0 group">
                            <img :src="m.avatar_url" :alt="m.name" class="w-13 h-13 rounded-2xl object-cover border border-slate-200 dark:border-slate-700 group-hover:scale-105 transition-transform">
                            <!-- Online Status Glowing Dot -->
                            <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white dark:border-[#111827] flex items-center justify-center"
                                  :class="m.is_online ? 'bg-emerald-500 ring-2 ring-emerald-300/40' : (m.is_in_prison ? 'bg-rose-500' : 'bg-slate-300 dark:bg-slate-600')"
                                  :title="m.online_label">
                            </span>
                        </a>

                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <a :href="m.profile_url" class="font-black text-sm sm:text-base text-slate-900 dark:text-slate-100 hover:text-purple-600 dark:hover:text-purple-400 transition truncate" x-text="m.name"></a>
                                <template x-if="m.is_vip">
                                    <span title="VIP Lid" class="text-amber-500 text-xs"><i class="fa-solid fa-crown"></i></span>
                                </template>
                                <template x-if="m.is_self">
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase tracking-wider bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800">Jij</span>
                                </template>
                            </div>
                            <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 truncate" x-text="m.title"></div>

                            <!-- Live Activity Badge -->
                            <div class="mt-1 flex items-center gap-1.5 text-[11px]">
                                <template x-if="m.is_online">
                                    <span class="text-emerald-600 dark:text-emerald-400 font-extrabold flex items-center gap-1.5 bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span>Nu Online</span>
                                    </span>
                                </template>
                                <template x-if="!m.is_online && !m.is_in_prison">
                                    <span class="text-slate-400 flex items-center gap-1">
                                        <i class="fa-regular fa-clock text-[10px]"></i>
                                        <span x-text="m.last_active_human"></span>
                                    </span>
                                </template>
                                <template x-if="m.is_in_prison">
                                    <span class="text-rose-600 dark:text-rose-400 font-extrabold flex items-center gap-1 bg-rose-50 dark:bg-rose-950/60 px-2 py-0.5 rounded-full border border-rose-200 dark:border-rose-900">
                                        <i class="fa-solid fa-lock text-[10px]"></i>
                                        <span>Isoleercel</span>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Bende Badge on Top Right -->
                    <div class="shrink-0 text-right">
                        <template x-if="m.gang_id">
                            <a :href="'{{ route('gangs.index') }}'" class="inline-flex items-center gap-1 text-[11px] font-bold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 px-2.5 py-1 rounded-xl border border-purple-200 dark:border-purple-800 hover:bg-purple-100 transition" :title="m.gang_name + ' (' + m.role_label + ')'">
                                <i class="fa-solid fa-shield-halved text-[10px]"></i>
                                <span class="font-mono font-extrabold" x-text="'[' + m.gang_tag + ']'"></span>
                            </a>
                        </template>
                        <template x-if="!m.gang_id">
                            <span class="text-[10px] font-bold text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-lg">Geen bende</span>
                        </template>
                    </div>
                </div>

                <!-- Mid Card: Level & Respect Stats Pill Grid -->
                <div class="grid grid-cols-2 gap-2 text-xs pt-1 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl p-2.5 text-center">
                        <span class="text-[10px] font-bold text-slate-400 block uppercase tracking-wider">Level</span>
                        <span class="text-sm font-black text-slate-800 dark:text-slate-200 font-mono" x-text="m.level"></span>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl p-2.5 text-center">
                        <span class="text-[10px] font-bold text-purple-500 block uppercase tracking-wider">Respect</span>
                        <span class="text-sm font-black text-purple-700 dark:text-purple-300 font-mono" x-text="m.respect_formatted"></span>
                    </div>
                </div>

                <!-- Bottom Actions Bar -->
                <div class="flex items-center gap-2 pt-1">
                    <a :href="m.profile_url" 
                       class="flex-1 py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-extrabold text-xs transition flex items-center justify-center gap-1.5 shadow-2xs">
                        <i class="fa-solid fa-user"></i>
                        <span>Profiel</span>
                    </a>

                    <a :href="m.message_url"
                       title="Stuur privébericht"
                       class="py-2 px-3.5 rounded-xl bg-purple-50 hover:bg-purple-100 dark:bg-purple-950/60 dark:hover:bg-purple-900/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 font-extrabold text-xs transition flex items-center justify-center gap-1.5 shadow-2xs">
                        <i class="fa-solid fa-envelope"></i>
                        <span class="hidden sm:inline">Bericht</span>
                    </a>

                    <template x-if="!m.is_self">
                        <a :href="'{{ route('fight.index') }}'"
                           title="Zoek op in de luchtplaats"
                           class="py-2 px-3 rounded-xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900 font-extrabold text-xs transition flex items-center justify-center shadow-2xs">
                            <i class="fa-solid fa-hand-fist"></i>
                        </a>
                    </template>
                </div>
            </div>
        </template>
    </div>

    <!-- 2. TABLE VIEW MODE (Dense Ranking Style) -->
    <div x-show="viewMode === 'table'" class="bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/80 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">PATIËNT</th>
                        <th class="py-3 px-4">STATUS</th>
                        <th class="py-3 px-4">BENDE</th>
                        <th class="py-3 px-4 text-right">LEVEL</th>
                        <th class="py-3 px-4 text-right">RESPECT</th>
                        <th class="py-3 px-4 text-right w-36">ACTIES</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                    <template x-for="(m, idx) in filteredList" :key="m.id">
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors"
                            :class="m.is_self ? 'bg-purple-50/30 dark:bg-purple-950/30' : ''">
                            <td class="py-3.5 px-4 text-center font-bold text-slate-400 dark:text-slate-500 font-mono" x-text="idx + 1"></td>
                            
                            <!-- Patient Avatar & Name -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <a :href="m.profile_url" class="relative shrink-0">
                                        <img :src="m.avatar_url" :alt="m.name" class="w-8 h-8 rounded-full object-cover border border-slate-200 dark:border-slate-700">
                                        <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2 border-white dark:border-[#111827]"
                                              :class="m.is_online ? 'bg-emerald-500 ring-2 ring-emerald-300/40' : (m.is_in_prison ? 'bg-rose-500' : 'bg-slate-300 dark:bg-slate-600')">
                                        </span>
                                    </a>
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <a :href="m.profile_url" class="font-extrabold text-slate-900 dark:text-slate-100 hover:text-purple-600 dark:hover:text-purple-400 transition" x-text="m.name"></a>
                                            <template x-if="m.is_vip">
                                                <i class="fa-solid fa-crown text-[10px] text-amber-500" title="VIP Lid"></i>
                                            </template>
                                            <template x-if="m.is_self">
                                                <span class="text-[9px] font-bold text-purple-700 bg-purple-100 px-1 py-0.2 rounded">Jij</span>
                                            </template>
                                        </div>
                                        <span class="text-[10px] text-slate-400 dark:text-slate-500 block" x-text="m.title"></span>
                                    </div>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4">
                                <template x-if="m.is_online">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span>Nu Online</span>
                                    </span>
                                </template>
                                <template x-if="!m.is_online && !m.is_in_prison">
                                    <span class="text-[11px] text-slate-400" x-text="m.last_active_human"></span>
                                </template>
                                <template x-if="m.is_in_prison">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-600 bg-rose-50 dark:bg-rose-950/60 px-2 py-0.5 rounded-full border border-rose-200 dark:border-rose-900">
                                        <i class="fa-solid fa-lock text-[9px]"></i>
                                        <span>Isoleercel</span>
                                    </span>
                                </template>
                            </td>

                            <!-- Gang -->
                            <td class="py-3.5 px-4">
                                <template x-if="m.gang_id">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-800 dark:text-slate-200">
                                        <span class="px-1.5 py-0.5 rounded bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 font-mono text-[10px]" x-text="'[' + m.gang_tag + ']'"></span>
                                        <span class="truncate max-w-[120px]" x-text="m.gang_name"></span>
                                    </span>
                                </template>
                                <template x-if="!m.gang_id">
                                    <span class="text-slate-400 text-xs italic">Geen</span>
                                </template>
                            </td>

                            <!-- Level -->
                            <td class="py-3.5 px-4 text-right font-bold text-slate-700 dark:text-slate-300 font-mono" x-text="m.level"></td>

                            <!-- Respect -->
                            <td class="py-3.5 px-4 text-right font-extrabold text-purple-700 dark:text-purple-300 font-mono" x-text="m.respect_formatted"></td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a :href="m.message_url" title="Bericht sturen" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-purple-100 hover:text-purple-700 dark:bg-slate-800 dark:hover:bg-purple-950 flex items-center justify-center text-slate-600 dark:text-slate-300 text-xs transition">
                                        <i class="fa-solid fa-envelope"></i>
                                    </a>
                                    <a :href="m.profile_url" title="Bekijk profiel" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 text-xs transition">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Empty State when 0 members match filter/search -->
    <div x-show="filteredList.length === 0" class="bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-12 text-center space-y-4">
        <div class="w-16 h-16 rounded-2xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center mx-auto text-2xl">
            <i class="fa-solid fa-user-slash"></i>
        </div>
        <div class="space-y-1.5">
            <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100">Geen patiënten gevonden</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                Er zijn geen leden die overeenkomen met je zoekopdracht of gekozen filter. Probeer een andere term of reset de filters.
            </p>
        </div>
        <div>
            <button type="button" @click="search = ''; filter = 'all';" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs transition cursor-pointer shadow-xs">
                Alle filters wissen
            </button>
        </div>
    </div>

</div>
@endsection
