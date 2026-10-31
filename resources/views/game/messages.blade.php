@extends('layouts.app')

@section('content')
@php
    $otherPlayers = $otherPlayers ?? $friends ?? collect();
@endphp
<div class="space-y-6" x-data="{ composeOpen: {{ !empty($initialRecipient) ? 'true' : 'false' }} }">

    <!-- Header Banner -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 p-6 flex flex-col md:flex-row gap-6 items-start md:items-center justify-between">
        <div class="flex items-center gap-4">
            <img src="{{ $character->avatar_url }}" alt="{{ $character->name }}" class="w-16 h-16 rounded-2xl object-cover border-2 border-slate-200 dark:border-slate-700 shadow-2xs shrink-0">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        {{ $character->name }}'s Berichtenbox
                    </h1>
                    @if($unreadCount > 0)
                    <span class="bg-rose-500 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full">
                        {{ $unreadCount }} Nieuw
                    </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Officiële kliniek incidenten, arrestatierapporten, diefstalmeldingen en brieven van medespelers.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
            <!-- Alles Gelezen Form -->
            <form action="{{ route('messages.mark-all-read') }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="filter" value="{{ $filter }}">
                <button type="submit" 
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs font-black transition-all duration-150 border shadow-2xs active:scale-95 cursor-pointer {{ $unreadCount > 0 ? 'bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white border-emerald-600 dark:border-emerald-500' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border-slate-200/80 dark:border-slate-700' }}"
                        title="Markeer alle ongelezen berichten als gelezen">
                    <i class="fa-solid fa-check-double"></i>
                    <span>Alles Gelezen</span>
                    @if($unreadCount > 0)
                        <span class="bg-emerald-800 dark:bg-emerald-900 text-white text-[10px] font-extrabold px-1.5 py-0.5 rounded-full">{{ $unreadCount }}</span>
                    @endif
                </button>
            </form>

            <!-- Verwijderen Acties Dropdown -->
            <div x-data="{ openDelete: false }" class="relative inline-block w-full sm:w-auto">
                <button @click="openDelete = !openDelete" 
                        type="button" 
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs font-black bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 dark:hover:bg-rose-900/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70 shadow-2xs transition-all duration-150 active:scale-95 cursor-pointer">
                    <i class="fa-solid fa-trash-can text-rose-500"></i>
                    <span>Alles Verwijderen</span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform ml-0.5" :class="openDelete ? 'rotate-180' : ''"></i>
                </button>

                <div x-show="openDelete" 
                     @click.away="openDelete = false" 
                     x-cloak 
                     class="absolute left-0 sm:left-auto sm:right-0 mt-2 w-64 max-w-[calc(100vw-2rem)] bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200/90 dark:border-slate-800 py-1.5 z-30 animate-in fade-in zoom-in-95 duration-100 text-left">
                    <div class="px-3 py-1.5 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">Berichten Verwijderen</span>
                    </div>

                    <!-- 1. Huidige categorie / alles -->
                    <form action="{{ route('messages.destroy-all') }}" method="POST"
                          data-confirm="{{ $filter !== 'all' ? 'Weet je zeker dat je alle berichten in deze categorie wilt verwijderen?' : 'Weet je zeker dat je ALLE berichten definitief wilt verwijderen uit je berichtenbox?' }}"
                          data-confirm-title="{{ $filter !== 'all' ? 'Categorie Berichten Verwijderen' : 'Alle Berichten Verwijderen' }}"
                          data-confirm-btn="Verwijderen"
                          data-confirm-type="danger"
                          data-confirm-icon="fa-trash-can">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="scope" value="filter">
                        <input type="hidden" name="filter" value="{{ $filter }}">
                        <button type="submit" class="w-full px-3 py-2 text-left text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-rose-50 dark:hover:bg-rose-950/40 hover:text-rose-700 dark:hover:text-rose-300 transition flex items-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-trash text-slate-400 w-4 text-center"></i>
                            <span>{{ $filter !== 'all' ? 'Verwijder huidige selectie' : 'Verwijder alle berichten' }}</span>
                        </button>
                    </form>

                    <!-- 2. Alleen gelezen berichten -->
                    <form action="{{ route('messages.destroy-all') }}" method="POST"
                          data-confirm="Weet je zeker dat je alle reeds gelezen berichten wilt verwijderen? Ongelezen berichten blijven bewaard."
                          data-confirm-title="Gelezen Berichten Verwijderen"
                          data-confirm-btn="Verwijder Gelezen"
                          data-confirm-type="danger"
                          data-confirm-icon="fa-envelope-open">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="scope" value="read_only">
                        <button type="submit" class="w-full px-3 py-2 text-left text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-rose-50 dark:hover:bg-rose-950/40 hover:text-rose-700 dark:hover:text-rose-300 transition flex items-center gap-2 cursor-pointer">
                            <i class="fa-regular fa-envelope-open text-slate-400 w-4 text-center"></i>
                            <span>Alleen gelezen berichten</span>
                        </button>
                    </form>

                    <!-- 3. Volledig postvak legen -->
                    @if($filter !== 'all')
                    <form action="{{ route('messages.destroy-all') }}" method="POST"
                          data-confirm="Weet je zeker dat je je HELE berichtenbox (alle categorieën) definitief wilt legen?"
                          data-confirm-title="Volledige Postvak Legen"
                          data-confirm-btn="Alles Legen"
                          data-confirm-type="danger"
                          data-confirm-icon="fa-bomb">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="scope" value="everything">
                        <button type="submit" class="w-full px-3 py-2 text-left text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition flex items-center gap-2 cursor-pointer border-t border-slate-100 dark:border-slate-800">
                            <i class="fa-solid fa-bomb text-rose-500 w-4 text-center"></i>
                            <span>Volledige postvak legen</span>
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            <!-- Brief Schrijven -->
            <button type="button" @click="composeOpen = true" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black px-4 py-2.5 rounded-xl text-xs shadow-xs transition-all duration-150 active:scale-95 cursor-pointer">
                <i class="fa-solid fa-pen-to-square text-amber-300"></i>
                <span>Brief Schrijven</span>
            </button>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="bg-slate-100/90 dark:bg-slate-800/80 p-1 rounded-xl border border-slate-200/80 dark:border-slate-700/70 flex items-center gap-1.5 overflow-x-auto">
        <a href="{{ route('messages.index', ['filter' => 'all']) }}" 
           class="shrink-0 whitespace-nowrap px-3.5 py-1.5 rounded-lg text-xs transition {{ $filter === 'all' ? 'bg-purple-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 font-bold hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-700/60' }}">
            <i class="fa-solid fa-inbox mr-1"></i> Alle Berichten
        </a>
        <a href="{{ route('messages.index', ['filter' => 'unread']) }}" 
           class="shrink-0 whitespace-nowrap px-3.5 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5 {{ $filter === 'unread' ? 'bg-rose-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 font-bold hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-700/60' }}">
            <i class="fa-solid fa-bell"></i>
            <span>Ongelezen</span>
            @if($unreadCount > 0)
                <span class="{{ $filter === 'unread' ? 'bg-white text-rose-700' : 'bg-rose-500 text-white' }} text-[10px] font-extrabold px-1.5 py-0.5 rounded-full">{{ $unreadCount }}</span>
            @endif
        </a>
        <a href="{{ route('messages.index', ['filter' => 'theft']) }}" 
           class="shrink-0 whitespace-nowrap px-3.5 py-1.5 rounded-lg text-xs transition {{ $filter === 'theft' ? 'bg-rose-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 font-bold hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-700/60' }}">
            <i class="fa-solid fa-mask mr-1"></i> Diefstal & Berovingen
        </a>
        <a href="{{ route('messages.index', ['filter' => 'system']) }}" 
           class="shrink-0 whitespace-nowrap px-3.5 py-1.5 rounded-lg text-xs transition {{ $filter === 'system' ? 'bg-amber-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 font-bold hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-700/60' }}">
            <i class="fa-solid fa-building-shield mr-1"></i> Arrestaties & Directie
        </a>
        <a href="{{ route('messages.index', ['filter' => 'player']) }}" 
           class="shrink-0 whitespace-nowrap px-3.5 py-1.5 rounded-lg text-xs transition {{ $filter === 'player' ? 'bg-purple-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 font-bold hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-700/60' }}">
            <i class="fa-solid fa-user-group mr-1"></i> Spelersbrieven
        </a>
    </div>

    <!-- Berichtenlijst -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 overflow-hidden">
        @if($messages->isEmpty())
        <div class="p-12 text-center space-y-3">
            <div class="w-14 h-14 mx-auto rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 flex items-center justify-center text-2xl">
                <i class="fa-regular fa-envelope-open"></i>
            </div>
            <h3 class="text-sm font-extrabold text-slate-700 dark:text-slate-200">Geen berichten gevonden</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">Je postvak is helemaal bijgewerkt. Bij acties zoals arrestaties of berovingen verschijnen hier direct rapporten.</p>
        </div>
        @else
        <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
            @foreach($messages as $msg)
            <div class="p-4 sm:p-5 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors flex items-start gap-3.5 sm:gap-4 {{ ! $msg->is_read ? 'bg-purple-50/30 dark:bg-purple-950/20 border-l-4 border-purple-500 pl-3 sm:pl-4' : '' }}">
                <!-- Icon badge per type -->
                @if($msg->type === 'theft')
                <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/70 flex items-center justify-center text-sm shrink-0 font-bold shadow-2xs">
                    <i class="fa-solid fa-mask"></i>
                </div>
                @elseif($msg->type === 'jail')
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200/80 dark:border-amber-800/70 flex items-center justify-center text-sm shrink-0 font-bold shadow-2xs">
                    <i class="fa-solid fa-dungeon"></i>
                </div>
                @elseif($msg->type === 'level')
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-sm shrink-0 font-bold shadow-2xs">
                    <i class="fa-solid fa-trophy"></i>
                </div>
                @elseif($msg->type === 'friend_request')
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-sm shrink-0 font-bold shadow-2xs">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                @else
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-800/70 flex items-center justify-center text-sm shrink-0 font-bold shadow-2xs">
                    <i class="fa-solid fa-envelope"></i>
                </div>
                @endif

                <div class="flex-1 min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black text-slate-900 dark:text-white">{{ $msg->sender_name }}</span>
                            @if(! $msg->is_read)
                            <span class="bg-purple-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded uppercase tracking-wider">
                                NIEUW
                            </span>
                            @endif
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wider
                                {{ $msg->type === 'theft' ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70' : ($msg->type === 'jail' ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70' : ($msg->type === 'friend_request' ? 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700')) }}">
                                {{ $msg->type === 'friend_request' ? 'VRIENDSCHAPSVERZOEK' : $msg->type }}
                            </span>
                        </div>
                        <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">{{ $msg->created_at->diffForHumans() }}</span>
                    </div>

                    <h4 class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-white mt-1">
                        {{ $msg->subject }}
                    </h4>

                    <div class="text-xs text-slate-700 dark:text-slate-300 mt-1 leading-relaxed whitespace-pre-line bg-slate-50/90 dark:bg-slate-800/60 p-3 rounded-xl border border-slate-200/80 dark:border-slate-700/70 font-mono text-[11px] sm:text-xs">
                        {{ $msg->content }}
                    </div>

                    @if($msg->type === 'friend_request')
                        @php
                            $pendingFriendReq = \App\Models\Friend::where('character_id', $msg->sender_id)
                                ->where('friend_id', $character->id)
                                ->where('status', 'pending')
                                ->first();
                        @endphp

                        @if($pendingFriendReq)
                        <div class="mt-3 flex items-center gap-2">
                            <form action="{{ route('friends.accept', $pendingFriendReq) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white font-black text-xs transition-all duration-150 active:scale-95 shadow-xs flex items-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-check"></i> Accepteren
                                </button>
                            </form>
                            <form action="{{ route('friends.reject', $pendingFriendReq) }}" method="POST"
                                  data-confirm="Weet je zeker dat je dit vriendschapsverzoek wilt weigeren?"
                                  data-confirm-title="Vriendschapsverzoek Weigeren"
                                  data-confirm-btn="Weigeren"
                                  data-confirm-type="warning"
                                  data-confirm-icon="fa-user-slash">
                                @csrf
                                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-rose-50 dark:bg-slate-800 dark:hover:bg-rose-950/40 text-slate-700 hover:text-rose-600 dark:text-slate-200 dark:hover:text-rose-400 border border-slate-200/80 dark:border-slate-700 font-bold text-xs transition-all duration-150 active:scale-95 flex items-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-xmark"></i> Weigeren
                                </button>
                            </form>
                        </div>
                        @else
                        <div class="mt-2.5 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 text-[11px] text-slate-600 dark:text-slate-400 font-bold">
                            <i class="fa-solid fa-check-double text-emerald-600 dark:text-emerald-400"></i>
                            <span>Dit verzoek is reeds verwerkt</span>
                        </div>
                        @endif
                    @endif
                </div>

                <!-- Actions: Mark as Read & Delete -->
                <div class="flex items-center gap-1 shrink-0">
                    @if(! $msg->is_read)
                    <form action="{{ route('messages.mark-read', $msg) }}" method="POST">
                        @csrf
                        <button type="submit" class="text-slate-400 dark:text-slate-500 hover:text-emerald-600 dark:hover:text-emerald-400 p-1.5 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-950/40 transition cursor-pointer" title="Als gelezen markeren">
                            <i class="fa-solid fa-check text-xs"></i>
                        </button>
                    </form>
                    @endif

                    <form action="{{ route('messages.destroy', $msg) }}" method="POST" 
                          data-confirm="Weet je zeker dat je dit bericht definitief wilt verwijderen uit je dossier?"
                          data-confirm-title="Bericht Verwijderen"
                          data-confirm-btn="Verwijderen"
                          data-confirm-type="danger"
                          data-confirm-icon="fa-trash-can">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-slate-400 dark:text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer" title="Verwijderen">
                            <i class="fa-regular fa-trash-can text-xs"></i>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $messages->links() }}
        </div>
        @endif
    </div>

    <!-- COMPOSE MODAL -->
    <div x-show="composeOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 pb-24 lg:pb-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="composeOpen = false" class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200/90 dark:border-slate-800 max-w-lg w-full max-h-[85dvh] overflow-y-auto p-6 space-y-4 animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-feather text-purple-600 dark:text-purple-400"></i>
                    <span>Brief Versturen naar Vriend</span>
                </h3>
                <button type="button" @click="composeOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 cursor-pointer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <div class="p-3 bg-purple-50/90 dark:bg-purple-950/60 rounded-xl border border-purple-200/80 dark:border-purple-800/70 text-xs text-purple-900 dark:text-purple-200 flex items-start gap-2">
                <i class="fa-solid fa-circle-info text-purple-600 dark:text-purple-400 mt-0.5 shrink-0"></i>
                <span>Je kunt in de kliniek alleen brieven sturen naar spelers op je vriendenlijst.</span>
            </div>

            @if($otherPlayers->isEmpty())
            <div class="p-6 text-center space-y-3 bg-slate-50/90 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700/70">
                <div class="w-12 h-12 rounded-full bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-xl mx-auto">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <h4 class="text-xs font-black text-slate-900 dark:text-white">Je hebt nog geen vrienden op je lijst</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 max-w-xs mx-auto">
                    Voeg eerst medepatiënten toe als vriend via de vriendenpagina om geheime brieven met hen uit te wisselen.
                </p>
                <a href="{{ route('friends.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs rounded-xl shadow-xs transition-all duration-150 active:scale-95 cursor-pointer">
                    <i class="fa-solid fa-users"></i>
                    <span>Naar Vriendenlijst</span>
                </a>
            </div>
            @else
            <form action="{{ route('messages.send') }}" method="POST" class="space-y-3">
                @csrf
                <div x-data="{
                    search: @js($initialRecipient ?? ''),
                    selectedPlayer: @js($initialRecipient ?? ''),
                    openDropdown: false,
                    players: @js($otherPlayers->map(fn($p) => ['name' => $p->name, 'level' => $p->level, 'avatar' => $p->avatar_url])->values()),
                    get filteredPlayers() {
                        if (!this.search.trim()) return this.players.slice(0, 25);
                        return this.players.filter(p => p.name.toLowerCase().includes(this.search.toLowerCase())).slice(0, 25);
                    },
                    select(name) {
                        this.selectedPlayer = name;
                        this.search = name;
                        this.openDropdown = false;
                    }
                }" class="relative">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Vriend (Ontvanger)</label>
                    <input type="hidden" name="recipient_name" :value="selectedPlayer || search" required>
                    <div class="relative flex items-center">
                        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 flex items-center pointer-events-none text-purple-500 dark:text-purple-400 text-xs z-10">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>
                        <input type="text"
                               x-model="search"
                               @focus="openDropdown = true"
                               @input="openDropdown = true; selectedPlayer = ''"
                               @click.outside="openDropdown = false"
                               autocomplete="off"
                               placeholder="Kies een vriend uit je lijst..."
                               class="w-full text-base sm:text-xs font-semibold bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl py-2.5 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition tbs-search-input"
                               style="padding-left: 2.75rem !important; padding-right: 2.25rem !important;">
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 flex items-center pointer-events-none text-slate-400 dark:text-slate-500 text-xs z-10">
                            <i class="fa-solid fa-chevron-down transition-transform duration-150" :class="openDropdown ? 'rotate-180' : ''"></i>
                        </div>
                    </div>

                    <!-- Filtered Dropdown list -->
                    <div x-show="openDropdown" 
                         x-transition
                         class="absolute z-50 left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white dark:bg-slate-900 rounded-xl shadow-xl border border-slate-200/90 dark:border-slate-800 divide-y divide-slate-100 dark:divide-slate-800">
                        <template x-if="filteredPlayers.length === 0">
                            <div class="p-3 text-xs text-slate-400 dark:text-slate-500 text-center">
                                Geen vriend gevonden met '<span x-text="search"></span>'
                            </div>
                        </template>
                        <template x-for="p in filteredPlayers" :key="p.name">
                            <button type="button" 
                                    @click="select(p.name)"
                                    class="w-full flex items-center justify-between px-3.5 py-2.5 hover:bg-purple-50/70 dark:hover:bg-purple-950/50 text-left transition group cursor-pointer">
                                <div class="flex items-center gap-2.5">
                                    <img :src="p.avatar" class="w-6 h-6 rounded-full object-cover border border-purple-200 dark:border-purple-800">
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 group-hover:text-purple-700 dark:group-hover:text-purple-300" x-text="p.name"></span>
                                </div>
                                <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full" x-text="'Level ' + p.level"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Onderwerp</label>
                    <input type="text" name="subject" required placeholder="Bijv. Deal in de kantine / Bende bondgenootschap"
                           class="w-full text-base sm:text-xs font-semibold bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl px-3 py-2.5 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Boodschap</label>
                    <textarea name="content" required rows="4" placeholder="Schrijf je geheime brief..."
                              class="w-full text-base sm:text-xs font-semibold bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl p-3 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition resize-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="composeOpen = false" class="px-4 py-2 text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700 rounded-xl transition cursor-pointer">
                        Annuleren
                    </button>
                    <button type="submit" 
                            {{ $character->isImprisoned() || $character->isComatose() ? 'disabled' : '' }}
                            class="px-5 py-2 text-xs font-black {{ $character->isImprisoned() || $character->isComatose() ? 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed' : 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white cursor-pointer active:scale-95' }} rounded-xl transition-all duration-150 shadow-xs">
                        Brief Versturen
                    </button>
                </div>
            </form>
            @endif
        </div>
    </div>

</div>
@endsection
