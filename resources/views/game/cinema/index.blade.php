@extends('layouts.app')

@section('title', 'Kliniek Cinema & Watchparties - TBS Kliniek')

@section('content')
<div class="space-y-6">

    <!-- Cinema Hero Header -->
    <div class="rounded-3xl overflow-hidden border border-purple-400/30 dark:border-purple-900/60 shadow-xl bg-gradient-to-r from-purple-800 via-indigo-900 to-purple-950 dark:from-slate-950 dark:via-purple-950 dark:to-slate-900 p-6 sm:p-8 text-white relative">
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="space-y-2 max-w-xl">
                <div class="inline-flex items-center gap-2 bg-white/10 dark:bg-purple-500/20 border border-white/20 dark:border-purple-400/40 text-purple-100 dark:text-purple-300 text-xs font-black px-3.5 py-1 rounded-full uppercase tracking-wider backdrop-blur-xs">
                    <i class="fa-solid fa-film text-purple-200"></i>
                    <span>Community Bioscoop</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-black tracking-tight text-white">
                    🍿 Kliniek <span class="bg-gradient-to-r from-purple-200 via-amber-200 to-amber-300 bg-clip-text text-transparent">Cinema &amp; Watchparties</span>
                </h1>
                <p class="text-xs sm:text-sm text-purple-100/90 dark:text-slate-300 leading-relaxed font-medium">
                    Kijk gezellig samen met patiënten naar YouTube video's, trailers, documentaires en muziek direct in het spel! Deel live reacties, stuur stickers en memes in de sessie-chat of organiseer jullie eigen bende watchparty.
                </p>
            </div>

            <div class="flex items-center gap-3 shrink-0 flex-wrap">
                <button type="button" @click="$dispatch('open-create-cinema')"
                        class="px-5 py-3 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-slate-950 font-black text-xs uppercase tracking-wider transition shadow-lg shadow-amber-900/30 flex items-center gap-2 cursor-pointer active:scale-95">
                    <i class="fa-solid fa-plus-circle text-base"></i>
                    <span>Nieuwe Zaal Openen</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Official Community Watchparties Section (Only displayed if staff created an official room) -->
    @if($officialRooms->isNotEmpty())
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-certificate text-amber-500"></i>
                <span>Officiële TBS Kliniek Zalen</span>
            </h2>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-100 dark:bg-amber-950/70 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                Staff Moderated &amp; Discord Event
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($officialRooms as $room)
            <div class="bg-white dark:bg-slate-900 border-2 border-purple-500/40 dark:border-purple-500/50 rounded-3xl p-5 shadow-sm dark:shadow-lg flex flex-col justify-between gap-4 relative overflow-hidden group hover:border-purple-500 transition">
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <span class="px-2.5 py-1 rounded-xl text-[10px] font-black bg-purple-100 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 flex items-center gap-1.5 border border-purple-200 dark:border-purple-800">
                            <i class="fa-solid fa-crown text-amber-500"></i>
                            <span>Hoofdzaal</span>
                        </span>
                        <span class="text-xs font-mono text-slate-500 dark:text-slate-400 font-bold flex items-center gap-1">
                            <i class="fa-solid fa-eye text-slate-400"></i>
                            {{ $room->views_count }} Weergaven
                        </span>
                    </div>

                    <h3 class="text-base font-black text-slate-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition">
                        {{ $room->title }}
                    </h3>

                    <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2 leading-relaxed">
                        {{ $room->description }}
                    </p>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset($room->host?->avatar ?? 'characters/avatar-default.png') }}" class="w-6 h-6 rounded-full object-cover">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Host: {{ $room->host?->name ?? 'TBS Staff' }}</span>
                    </div>
                    <a href="{{ route('cinema.show', $room->slug) }}" 
                       class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-black text-xs uppercase tracking-wider transition shadow-xs flex items-center gap-1.5">
                        <i class="fa-solid fa-play text-[10px]"></i>
                        <span>Zaal Binnengaan</span>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Patient & Bende Watchparties -->
    <div class="space-y-4 pt-2">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-users text-indigo-500"></i>
                <span>Actieve Patiënten Zalen</span>
            </h2>
            <span class="text-xs text-slate-500 dark:text-slate-400 font-bold">{{ $patientRooms->count() }} Zalen Geopend</span>
        </div>

        @if($patientRooms->isEmpty())
        <div class="bg-white dark:bg-slate-900 border-2 border-dashed border-slate-300 dark:border-slate-800 rounded-3xl p-10 sm:p-12 text-center shadow-xs">
            <div class="w-16 h-16 rounded-2xl bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-3xl mx-auto mb-3 shadow-xs">
                <i class="fa-solid fa-clapperboard"></i>
            </div>
            <h3 class="text-base font-black text-slate-900 dark:text-white">Nog Geen Patiënten Zalen</h3>
            <p class="text-xs text-slate-600 dark:text-slate-400 max-w-md mx-auto mt-1.5 leading-relaxed font-medium">
                Open als eerste een gezellige zaal voor jouw bende of vrienden! Plak een YouTube link van je favoriete video of muziek en kijk direct samen.
            </p>
            <button type="button" @click="$dispatch('open-create-cinema')" class="mt-4 px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-black text-xs uppercase tracking-wider transition shadow-xs inline-flex items-center gap-2 cursor-pointer active:scale-95">
                <i class="fa-solid fa-plus-circle"></i>
                <span>Eerste Zaal Openen</span>
            </button>
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($patientRooms as $pRoom)
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex flex-col justify-between gap-3 hover:border-purple-400 dark:hover:border-purple-500 transition">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-0.5 rounded-md text-[10px] font-black {{ $pRoom->is_private ? 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300 border border-rose-200 dark:border-rose-800' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">
                            {{ $pRoom->is_private ? '🔒 Privé Zaal' : '🌐 Openbaar' }}
                        </span>
                        <span class="text-[11px] font-mono text-slate-500 dark:text-slate-400 font-bold">
                            {{ $pRoom->views_count }} views
                        </span>
                    </div>

                    <h4 class="text-sm font-black text-slate-900 dark:text-white truncate">
                        {{ $pRoom->title }}
                    </h4>

                    <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2 leading-relaxed">
                        {{ $pRoom->description }}
                    </p>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <span class="text-[11px] text-slate-600 dark:text-slate-400 truncate">Host: <strong class="text-slate-900 dark:text-slate-200">{{ $pRoom->host?->name ?? 'Patiënt' }}</strong></span>
                    <a href="{{ route('cinema.show', $pRoom->slug) }}" class="px-3.5 py-1.5 rounded-xl bg-purple-50 hover:bg-purple-600 text-purple-700 hover:text-white dark:bg-slate-800 dark:hover:bg-purple-600 dark:text-slate-200 font-black text-xs transition border border-purple-200 dark:border-slate-700 hover:border-purple-600">
                        Kijk Mee &rarr;
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- CREATE CINEMA ROOM MODAL -->
    <div x-data="{ open: false, isPrivate: false, selectedPreset: '' }" 
         @open-create-cinema.window="open = true" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
        <div @click.away="open = false" 
             class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-7 max-w-lg w-full shadow-2xl space-y-5">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg shadow-xs">
                        <i class="fa-solid fa-film"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-sm text-slate-900 dark:text-white">Nieuwe Bioscoopzaal Openen</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Start een video watchparty met live sessie-chat</p>
                    </div>
                </div>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-white cursor-pointer">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form action="{{ route('cinema.create') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-black text-slate-900 dark:text-slate-200 mb-1">Titel van de Zaal:</label>
                    <input type="text" name="title" placeholder="bijv. Bende Movie Night of Muziek Lounge" required maxlength="80"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 font-bold text-xs text-slate-900 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-900 dark:text-slate-200 mb-1">YouTube Video Link / URL:</label>
                    <input type="text" name="youtube_url" x-model="selectedPreset" placeholder="https://www.youtube.com/watch?v=..." required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 font-bold text-xs text-slate-900 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-purple-500 font-mono">
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">Plak een YouTube link (videoclips, livestreams, documentaires of muziek).</p>
                </div>

                <!-- Snelle Voorselectie Presets -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1.5">Of kies een suggestie:</label>
                    <div class="flex items-center gap-1.5 flex-wrap">
                        @foreach($presets as $pr)
                        <button type="button" @click="selectedPreset = 'https://www.youtube.com/watch?v={{ $pr['id'] }}'"
                                class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-purple-100 hover:text-purple-700 dark:bg-slate-800 dark:hover:bg-slate-700 text-[11px] font-bold text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 cursor-pointer transition">
                            {{ $pr['title'] }}
                        </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-900 dark:text-slate-200 mb-1">Beschrijving (optioneel):</label>
                    <textarea name="description" rows="2" placeholder="Wat gaan jullie bekijken of bespreken?" maxlength="250"
                              class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-900 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-purple-500"></textarea>
                </div>

                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_private" value="1" x-model="isPrivate" class="rounded text-purple-600 focus:ring-purple-500">
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Maak dit een privé zaal (met PIN code)</span>
                    </label>

                    <div x-show="isPrivate" class="pt-2">
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Toegangs PIN:</label>
                        <input type="text" name="passcode" placeholder="bijv. 1234" maxlength="20"
                               class="w-full px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-mono text-slate-900 dark:text-white">
                    </div>
                </div>

                @if(Auth::user()->isStaff())
                <div class="p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/80">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_official" value="1" class="rounded text-amber-600 focus:ring-amber-500">
                        <span class="text-xs font-black text-amber-900 dark:text-amber-200 flex items-center gap-1.5">
                            <i class="fa-solid fa-crown text-amber-600"></i>
                            <span>Officiële Zaal (TBS Kliniek Hoofdzaal)</span>
                        </span>
                    </label>
                </div>
                @endif

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" @click="open = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs cursor-pointer transition">
                        Annuleren
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-black text-xs uppercase tracking-wider transition shadow-sm flex items-center gap-1.5 cursor-pointer active:scale-95">
                        <i class="fa-solid fa-play"></i>
                        <span>Start Watchparty</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
