@extends('layouts.app')

@section('title', 'Kliniek Cinema & Watchparties - TBS Kliniek')

@section('content')
<div class="space-y-6" x-data="{
    tab: 'all',
    search: '',
    matchesFilter(isOfficial, isHost, title, hostName) {
        if (this.tab === 'official' && !isOfficial) return false;
        if (this.tab === 'patient' && isOfficial) return false;
        if (this.tab === 'mine' && !isHost) return false;
        if (this.search.trim() === '') return true;
        const q = this.search.toLowerCase();
        return (title || '').toLowerCase().includes(q) || (hostName || '').toLowerCase().includes(q);
    }
}">

    <!-- HERO HEADER (Standard TBS Kliniek Theme) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs relative overflow-hidden">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 relative z-10">
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-purple-600 text-white flex items-center justify-center text-2xl shadow-xs shrink-0">
                    <i class="fa-solid fa-clapperboard"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight">Kliniek Cinema &amp; Watchparties</h1>
                        <span class="px-2.5 py-0.5 rounded-full bg-purple-50 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70 font-black text-[10px] uppercase tracking-wider">
                            Live Bioscoop
                        </span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-2xl leading-relaxed">
                        Kijk samen met patiënten naar YouTube video's, trailers, documentaires en muziek! Volledig synchroon afgespeeld met realtime zaalchat en een gedeelde video wachtrij.
                    </p>
                    <div class="flex items-center gap-3 pt-2 text-[11px] text-slate-600 dark:text-slate-400 font-bold flex-wrap">
                        <span class="flex items-center gap-1"><i class="fa-solid fa-check text-emerald-500"></i> Synchrone Playback</span>
                        <span class="flex items-center gap-1"><i class="fa-solid fa-check text-emerald-500"></i> Video Wachtrij</span>
                        <span class="flex items-center gap-1"><i class="fa-solid fa-check text-emerald-500"></i> Realtime Chat</span>
                        <span class="flex items-center gap-1"><i class="fa-solid fa-check text-emerald-500"></i> Directie Hoofdzalen</span>
                    </div>
                </div>
            </div>

            <!-- ACTION BUTTON -->
            <div class="flex items-center gap-2 shrink-0">
                @if($character?->isComatose())
                    <button type="button" disabled 
                            class="px-4 py-2.5 rounded-xl bg-slate-200 dark:bg-slate-800 text-rose-500 font-black text-xs uppercase tracking-wider shadow-xs flex items-center gap-2 opacity-70 cursor-not-allowed" title="Niet mogelijk tijdens Code Blauw">
                        <i class="fa-solid fa-heart-pulse text-xs text-rose-500"></i>
                        <span>Code Blauw</span>
                    </button>
                @elseif($character?->isImprisoned())
                    <button type="button" disabled 
                            class="px-4 py-2.5 rounded-xl bg-slate-200 dark:bg-slate-800 text-amber-500 font-black text-xs uppercase tracking-wider shadow-xs flex items-center gap-2 opacity-70 cursor-not-allowed" title="Niet mogelijk vanuit de isoleercel">
                        <i class="fa-solid fa-lock text-xs text-amber-500"></i>
                        <span>Isoleercel</span>
                    </button>
                @else
                    <button type="button" @click="$dispatch('open-create-cinema')" 
                            class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 shadow-xs flex items-center gap-2 cursor-pointer active:scale-95">
                        <i class="fa-solid fa-plus-circle text-xs"></i>
                        <span>Nieuwe Zaal Openen</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-3 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <!-- Filter Tabs -->
        <div class="bg-slate-100/90 dark:bg-slate-800/80 p-1 rounded-xl border border-slate-200/80 dark:border-slate-700/70 flex items-center gap-1 overflow-x-auto pb-1 sm:pb-0">
            <button type="button" @click="tab = 'all'"
                    :class="tab === 'all' ? 'bg-purple-600 text-white shadow-xs font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-700/60 font-bold'"
                    class="px-3.5 py-1.5 rounded-lg text-xs uppercase tracking-wider transition-all duration-150 shrink-0 cursor-pointer">
                Alle Zalen ({{ $officialRooms->count() + $patientRooms->count() }})
            </button>

            @if($officialRooms->isNotEmpty())
            <button type="button" @click="tab = 'official'"
                    :class="tab === 'official' ? 'bg-amber-500 text-slate-950 font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-700/60 font-bold'"
                    class="px-3.5 py-1.5 rounded-lg text-xs uppercase tracking-wider transition-all duration-150 shrink-0 flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-crown" :class="tab === 'official' ? 'text-slate-950' : 'text-amber-500'"></i>
                <span>Hoofdzalen ({{ $officialRooms->count() }})</span>
            </button>
            @endif

            <button type="button" @click="tab = 'patient'"
                    :class="tab === 'patient' ? 'bg-purple-600 text-white shadow-xs font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-700/60 font-bold'"
                    class="px-3.5 py-1.5 rounded-lg text-xs uppercase tracking-wider transition-all duration-150 shrink-0 cursor-pointer">
                Patiënten ({{ $patientRooms->count() }})
            </button>

            <button type="button" @click="tab = 'mine'"
                    :class="tab === 'mine' ? 'bg-purple-600 text-white shadow-xs font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-700/60 font-bold'"
                    class="px-3.5 py-1.5 rounded-lg text-xs uppercase tracking-wider transition-all duration-150 shrink-0 cursor-pointer">
                Mijn Zalen
            </button>
        </div>

        <!-- Search Bar -->
        <div class="relative w-full sm:w-64 shrink-0">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <input type="text" x-model="search" placeholder="Zoek zaal of host..."
                   class="w-full pl-9 pr-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/90 font-bold text-xs text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
        </div>
    </div>

    <!-- Official Community Hoofdzalen Section -->
    @if($officialRooms->isNotEmpty())
    <div class="space-y-4" x-show="tab === 'all' || tab === 'official'">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-crown text-amber-500"></i>
                <span>Officiële TBS Kliniek Hoofdzalen</span>
            </h2>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                Directie Evenement &amp; Notificatie
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @foreach($officialRooms as $room)
            <div x-show="matchesFilter(true, {{ ($character && $room->host_character_id === $character->id) ? 'true' : 'false' }}, @js($room->title), @js($room->host?->name ?? ''))"
                 class="bg-white dark:bg-slate-900 border-2 border-amber-300 dark:border-amber-500/60 rounded-2xl overflow-hidden shadow-xs hover:border-amber-400 dark:hover:border-amber-400 transition-all duration-200 flex flex-col justify-between group">
                
                <!-- Video Thumbnail Banner with Play Overlay -->
                <div class="relative aspect-video bg-slate-950 overflow-hidden">
                    <img src="{{ $room->getThumbnailUrl() }}" alt="{{ $room->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent"></div>

                    <!-- Badges on top of thumbnail -->
                    <div class="absolute top-3 left-3 flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-600 text-white flex items-center gap-1.5 shadow-xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                            <span>LIVE</span>
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-500 text-slate-950 flex items-center gap-1 shadow-xs">
                            <i class="fa-solid fa-crown text-[9px]"></i>
                            <span>HOOFDZAAL</span>
                        </span>
                    </div>

                    <div class="absolute top-3 right-3 flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-900/80 text-white backdrop-blur-xs flex items-center gap-1">
                            <i class="fa-solid fa-eye text-amber-400"></i>
                            {{ $room->views_count }} views
                        </span>
                    </div>

                    <!-- Centered Play Icon on Hover -->
                    <a href="{{ route('cinema.show', $room->slug) }}" class="absolute inset-0 flex items-center justify-center">
                        <div class="w-12 h-12 rounded-full bg-amber-500/90 text-slate-950 flex items-center justify-center text-lg shadow-md group-hover:scale-110 transition duration-300">
                            <i class="fa-solid fa-play ml-0.5"></i>
                        </div>
                    </a>

                    <!-- Queue indicator on bottom of thumbnail -->
                    @if(count($room->getQueue()) > 0)
                    <div class="absolute bottom-2.5 left-3">
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-black bg-purple-900/90 border border-purple-500/40 text-purple-200 backdrop-blur-xs flex items-center gap-1.5">
                            <i class="fa-solid fa-list-ol text-purple-300"></i>
                            <span>{{ count($room->getQueue()) }} in wachtrij</span>
                        </span>
                    </div>
                    @endif
                </div>

                <!-- Card Body -->
                <div class="p-5 space-y-3 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition line-clamp-1">
                            {{ $room->title }}
                        </h3>
                        <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2 mt-1 leading-relaxed">
                            {{ $room->description }}
                        </p>
                    </div>

                    <!-- Host & Actions Footer -->
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2 min-w-0">
                            <img src="{{ asset($room->host?->avatar ?? 'characters/avatar-default.png') }}" class="w-7 h-7 rounded-full object-cover shrink-0 border border-slate-200 dark:border-slate-700">
                            <div class="min-w-0">
                                <span class="text-xs font-black text-slate-900 dark:text-white truncate block">{{ $room->host?->name ?? 'TBS Directie' }}</span>
                                <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase">Directie Host</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @if(($character && $room->host_character_id === $character->id) || Auth::user()?->isStaff())
                            <form action="{{ route('cinema.close', $room->slug) }}" method="POST" onsubmit="return confirm('Weet je zeker dat je deze zaal wilt sluiten?');">
                                @csrf
                                <button type="submit" class="px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:hover:bg-rose-900/60 dark:text-rose-300 font-bold text-xs transition border border-rose-200/80 dark:border-rose-800/70 cursor-pointer" title="Zaal Sluiten">
                                    <i class="fa-solid fa-power-off"></i>
                                </button>
                            </form>
                            @endif

                            <a href="{{ route('cinema.show', $room->slug) }}" 
                               class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 shadow-xs flex items-center gap-1.5 active:scale-95 cursor-pointer">
                                <i class="fa-solid fa-play text-[10px]"></i>
                                <span>Kijk Mee</span>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Patient & Community Watchparties -->
    <div class="space-y-4 pt-2">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-users text-purple-600 dark:text-purple-400"></i>
                <span>Actieve Patiënten Zalen</span>
            </h2>
            <span class="text-xs text-slate-500 dark:text-slate-400 font-bold">{{ $patientRooms->count() }} Zalen Geopend</span>
        </div>

        @if($patientRooms->isEmpty())
        <div class="bg-white dark:bg-slate-900 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-2xl p-10 sm:p-12 text-center shadow-xs">
            <div class="w-14 h-14 rounded-2xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-2xl mx-auto mb-3 shadow-xs">
                <i class="fa-solid fa-clapperboard"></i>
            </div>
            <h3 class="text-base font-black text-slate-900 dark:text-white">Nog Geen Patiënten Zalen Geopend</h3>
            <p class="text-xs text-slate-600 dark:text-slate-400 max-w-md mx-auto mt-1.5 leading-relaxed font-medium">
                Wees de eerste! Open een zaal voor jouw bende of afdeling. Plak een YouTube link van je favoriete video, track of clip en kijk direct live samen.
            </p>
            <button type="button" @click="$dispatch('open-create-cinema')" class="mt-4 px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 shadow-xs inline-flex items-center gap-2 cursor-pointer active:scale-95">
                <i class="fa-solid fa-plus-circle"></i>
                <span>Eerste Zaal Openen</span>
            </button>
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($patientRooms as $pRoom)
            <div x-show="matchesFilter(false, {{ ($character && $pRoom->host_character_id === $character->id) ? 'true' : 'false' }}, @js($pRoom->title), @js($pRoom->host?->name ?? ''))"
                 class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200 flex flex-col justify-between group">
                
                <!-- Video Thumbnail Banner -->
                <div class="relative aspect-video bg-slate-950 overflow-hidden">
                    <img src="{{ $pRoom->getThumbnailUrl() }}" alt="{{ $pRoom->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent"></div>

                    <!-- Status pills -->
                    <div class="absolute top-3 left-3 flex items-center gap-1.5">
                        <span class="px-2.5 py-0.5 rounded-md text-[10px] font-black {{ $pRoom->is_private ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-900/80 text-white border border-slate-700/60' }}">
                            {{ $pRoom->is_private ? '🔒 Privé' : '🌐 Openbaar' }}
                        </span>
                    </div>

                    <div class="absolute top-3 right-3">
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-mono text-white bg-slate-900/80 font-bold backdrop-blur-xs flex items-center gap-1">
                            <i class="fa-solid fa-eye text-slate-400"></i>
                            {{ $pRoom->views_count }}
                        </span>
                    </div>

                    <!-- Play Hover Overlay -->
                    <a href="{{ route('cinema.show', $pRoom->slug) }}" class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition duration-200 bg-slate-950/40">
                        <div class="w-12 h-12 rounded-full bg-purple-600 text-white flex items-center justify-center text-base shadow-lg">
                            <i class="fa-solid fa-play ml-0.5"></i>
                        </div>
                    </a>

                    <!-- Queue indicator -->
                    @if(count($pRoom->getQueue()) > 0)
                    <div class="absolute bottom-2 left-2.5">
                        <span class="px-2 py-0.5 rounded-md text-[9px] font-black bg-purple-950/80 text-purple-200 border border-purple-800/80 flex items-center gap-1">
                            <i class="fa-solid fa-list-ol"></i>
                            <span>{{ count($pRoom->getQueue()) }} in wachtrij</span>
                        </span>
                    </div>
                    @endif
                </div>

                <!-- Card Body -->
                <div class="p-4 sm:p-5 space-y-3 flex-1 flex flex-col justify-between">
                    <div>
                        <h4 class="text-sm font-black text-slate-900 dark:text-white line-clamp-1 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition">
                            {{ $pRoom->title }}
                        </h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2 mt-1 leading-relaxed">
                            {{ $pRoom->description }}
                        </p>
                    </div>

                    <!-- Footer -->
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <img src="{{ asset($pRoom->host?->avatar ?? 'characters/avatar-default.png') }}" class="w-6 h-6 rounded-full object-cover shrink-0">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 truncate">
                                {{ $pRoom->host?->name ?? 'Patiënt' }}
                            </span>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0">
                            @if(($character && $pRoom->host_character_id === $character->id) || Auth::user()?->isStaff())
                            <form action="{{ route('cinema.close', $pRoom->slug) }}" method="POST" onsubmit="return confirm('Weet je zeker dat je deze zaal wilt sluiten?');">
                                @csrf
                                <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:hover:bg-rose-900/60 dark:text-rose-300 font-bold text-xs transition border border-rose-200/80 dark:border-rose-800/70 cursor-pointer" title="Zaal Sluiten">
                                    <i class="fa-solid fa-power-off text-[11px]"></i>
                                </button>
                            </form>
                            @endif

                            <a href="{{ route('cinema.show', $pRoom->slug) }}" 
                               class="px-3.5 py-1.5 rounded-xl bg-purple-50 hover:bg-purple-600 text-purple-700 hover:text-white dark:bg-slate-800 dark:hover:bg-purple-600 dark:text-purple-300 dark:hover:text-white font-black text-xs transition border border-purple-200/80 dark:border-slate-700 hover:border-purple-600 cursor-pointer">
                                Kijk Mee &rarr;
                            </a>
                        </div>
                    </div>
                </div>

            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- CREATE CINEMA ROOM MODAL -->
    <div x-data="{
            open: false,
            isPrivate: false,
            title: '',
            youtubeUrl: '',
            previewVideoId: '',
            selectedTitle: '',
            searchQuery: '',
            searchResults: [],
            isSearching: false,
            showDropdown: false,

            async onSearchInput() {
                const q = this.searchQuery.trim();
                if (!q) {
                    this.searchResults = [];
                    this.showDropdown = false;
                    return;
                }

                // Check if user pasted a direct YouTube link
                const patterns = [
                    /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^&?\/ ]{11})/i,
                    /^[a-zA-Z0-9_-]{11}$/
                ];
                for (const p of patterns) {
                    const match = q.match(p);
                    if (match) {
                        this.previewVideoId = match[1] || match[0];
                        this.youtubeUrl = 'https://www.youtube.com/watch?v=' + this.previewVideoId;
                        this.searchResults = [];
                        this.showDropdown = false;
                        return;
                    }
                }

                // If text search query, fetch results from YouTube
                if (q.length >= 2) {
                    this.isSearching = true;
                    try {
                        const res = await fetch(`/cinema/search-youtube?q=${encodeURIComponent(q)}`);
                        if (res.ok) {
                            const data = await res.json();
                            this.searchResults = data.results || [];
                            this.showDropdown = this.searchResults.length > 0;
                        }
                    } catch (e) {
                        console.error('YouTube search error:', e);
                    } finally {
                        this.isSearching = false;
                    }
                }
            },

            selectVideo(item) {
                this.youtubeUrl = item.url;
                this.previewVideoId = item.id;
                this.selectedTitle = item.title;
                this.searchQuery = item.title;
                if (!this.title.trim()) {
                    this.title = item.title.substring(0, 80);
                }
                this.searchResults = [];
                this.showDropdown = false;
            },

            clearVideo() {
                this.youtubeUrl = '';
                this.previewVideoId = '';
                this.selectedTitle = '';
                this.searchQuery = '';
                this.searchResults = [];
                this.showDropdown = false;
            }
         }" 
         @open-create-cinema.window="open = true" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 pb-24 lg:pb-4 bg-slate-950/70 backdrop-blur-xs">
        <div @click.away="open = false; showDropdown = false;" 
             class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 sm:p-7 max-w-lg w-full shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/80 text-purple-600 dark:text-purple-400 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-lg shadow-xs">
                        <i class="fa-solid fa-clapperboard"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-sm text-slate-900 dark:text-white">Nieuwe Bioscoopzaal Openen</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Zoek een video of plak direct een YouTube link</p>
                    </div>
                </div>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-white cursor-pointer">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form action="{{ route('cinema.create') }}" method="POST" class="space-y-4">
                @csrf

                <!-- YouTube Zoekbalk & Link Veld met Dropdown -->
                <div class="relative">
                    <label class="block text-xs font-black text-slate-900 dark:text-slate-200 mb-1">
                        YouTube Video Zoeken of Link Plakken:
                    </label>
                    <div class="relative">
                        <i class="fa-brands fa-youtube absolute left-3.5 top-1/2 -translate-y-1/2 text-rose-500 text-sm"></i>
                        <input type="text" x-model="searchQuery" @input.debounce.350ms="onSearchInput" @focus="if(searchResults.length > 0) showDropdown = true;"
                               placeholder="Typ zoekwoorden (bijv. 'Boef documentaire') of plak link..."
                               class="w-full pl-9 pr-9 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/90 font-bold text-xs text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                        
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1.5">
                            <template x-if="isSearching">
                                <i class="fa-solid fa-spinner animate-spin text-purple-600 text-xs"></i>
                            </template>
                            <template x-if="searchQuery">
                                <button type="button" @click="clearVideo" class="text-slate-400 hover:text-slate-600 text-xs cursor-pointer">
                                    <i class="fa-solid fa-circle-xmark"></i>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Hidden input to submit the actual YouTube URL -->
                    <input type="hidden" name="youtube_url" :value="youtubeUrl" required>

                    <!-- Search Dropdown Results -->
                    <div x-show="showDropdown && searchResults.length > 0" 
                         @click.away="showDropdown = false"
                         x-cloak
                         class="absolute z-50 inset-x-0 top-full mt-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl max-h-64 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                        <template x-for="item in searchResults" :key="item.id">
                            <div @click="selectVideo(item)" 
                                 class="p-2.5 flex items-center gap-3 hover:bg-purple-50 dark:hover:bg-purple-950/40 cursor-pointer transition">
                                <img :src="item.thumbnail" class="w-16 h-10 object-cover rounded-lg shrink-0 bg-slate-950">
                                <div class="min-w-0 flex-1">
                                    <h5 class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="item.title"></h5>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 truncate block" x-text="item.channel"></span>
                                </div>
                                <span class="px-2 py-1 rounded-lg bg-purple-100 dark:bg-purple-900/60 text-purple-700 dark:text-purple-300 text-[10px] font-black shrink-0">
                                    Kies
                                </span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Selected Video Preview Card -->
                <div x-show="previewVideoId" class="rounded-2xl overflow-hidden border border-purple-400/50 bg-slate-950 p-2.5 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <img :src="'https://img.youtube.com/vi/' + previewVideoId + '/mqdefault.jpg'" class="w-20 h-12 object-cover rounded-xl shrink-0">
                        <div class="min-w-0">
                            <span class="text-[9px] font-black uppercase text-emerald-400 flex items-center gap-1">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>Geselecteerde Video</span>
                            </span>
                            <p class="text-xs font-bold text-white truncate" x-text="selectedTitle || 'YouTube Video (' + previewVideoId + ')'"></p>
                        </div>
                    </div>
                    <button type="button" @click="clearVideo" class="px-2 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold shrink-0 transition" title="Wissen">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-900 dark:text-slate-200 mb-1">Titel van de Zaal:</label>
                    <input type="text" name="title" x-model="title" placeholder="bijv. Bende Movie Night of HipHop Bangers" required maxlength="80"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/90 font-bold text-xs text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-900 dark:text-slate-200 mb-1">Beschrijving (optioneel):</label>
                    <textarea name="description" rows="2" placeholder="Wat gaan jullie bekijken of bespreken?" maxlength="250"
                              class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/90 text-xs text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition"></textarea>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_private" value="1" x-model="isPrivate" class="rounded text-purple-600 focus:ring-purple-500">
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Maak dit een privé zaal (met PIN code)</span>
                    </label>

                    <div x-show="isPrivate" class="pt-2">
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Toegangs PIN:</label>
                        <input type="text" name="passcode" placeholder="bijv. 1234" maxlength="20"
                               class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-mono text-slate-900 dark:text-slate-100 focus:outline-none focus:border-purple-500">
                    </div>
                </div>

                @if(Auth::user()?->isAdmin())
                <div class="p-4 rounded-xl bg-amber-50/80 dark:bg-slate-800/60 border border-amber-200/80 dark:border-amber-800/60 space-y-2">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" name="is_official" value="1" class="rounded text-amber-500 focus:ring-amber-400 mt-0.5">
                        <div class="space-y-1">
                            <span class="text-xs font-black text-amber-900 dark:text-amber-200 flex items-center gap-1.5">
                                <i class="fa-solid fa-crown text-amber-500"></i>
                                <span>Officiële Zaal (TBS Kliniek Hoofdzaal)</span>
                            </span>
                            <p class="text-[11px] text-amber-800 dark:text-amber-300/90 leading-relaxed font-medium">
                                📢 <strong>Directie Chat Melding:</strong> Dit stuurt automatisch een uitnodigingsbericht in de Directie Chat naar <u>iedereen</u> in de kliniek + een aankondiging in de openbare chat!
                            </p>
                        </div>
                    </label>
                </div>
                @endif

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" @click="open = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-xs cursor-pointer transition-all duration-150 active:scale-95">
                        Annuleren
                    </button>
                    <button type="submit" :disabled="!youtubeUrl" class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 disabled:opacity-50 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 shadow-xs flex items-center gap-1.5 cursor-pointer active:scale-95">
                        <i class="fa-solid fa-play text-xs"></i>
                        <span>Start Watchparty</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
