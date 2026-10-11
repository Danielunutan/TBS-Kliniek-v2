@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Top Hero Banner -->
    <div class="rounded-2xl shadow-xs p-6 sm:p-8 text-slate-800 relative overflow-hidden bg-white border border-slate-200">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-50 border border-purple-100 flex items-center justify-center text-2xl text-purple-600 shadow-2xs shrink-0">
                    <i class="fa-solid fa-bullhorn"></i>
                </div>
                <div>
                    <div class="inline-flex items-center gap-2 bg-purple-50 text-purple-700 text-[10px] font-black uppercase tracking-widest px-2.5 py-0.5 rounded-md border border-purple-200 mb-1">
                        TBS Kliniek Sociaal
                    </div>
                    <h1 class="text-xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        Kliniek Tijdlijn (Feed)
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5 max-w-xl">
                        De openbare praatpaal van de TBS kliniek. Plaats mededelingen, daag rivaliserende bendes uit en volg alle ontwikkelingen op de voet.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <span class="px-3.5 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-purple-700 shadow-2xs">
                    <i class="fa-solid fa-comment-dots text-purple-600 mr-1.5"></i> Live Kliniek Feed
                </span>
            </div>
        </div>
    </div>

    <!-- Compose Box (Twitter Style) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5" x-data="{ text: '' }">
        <form action="{{ route('timeline.store') }}" method="POST" class="space-y-3">
            @csrf
            <div class="flex items-start gap-3.5">
                <img src="{{ $character->avatar_url }}" alt="{{ $character->name }}" class="w-11 h-11 rounded-2xl object-cover border-2 border-purple-200 shadow-xs shrink-0">
                <div class="flex-1 min-w-0">
                    <textarea name="content" 
                              x-model="text"
                              maxlength="280"
                              required 
                              rows="3" 
                              placeholder="Wat gebeurt er op de afdeling, {{ $character->name }}? Deel een bericht (max 280 tekens)..."
                              class="w-full text-xs sm:text-sm font-semibold bg-slate-50 border border-slate-200 text-slate-800 rounded-xl p-3.5 focus:bg-white focus:border-purple-600 focus:ring-2 focus:ring-purple-200 outline-none resize-none transition"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-between pt-1 pl-14">
                <div class="text-[11px] font-bold" :class="text.length > 250 ? 'text-rose-500 font-extrabold' : 'text-slate-400'">
                    <span x-text="text.length"></span> / 280 tekens
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" 
                            :disabled="text.trim().length === 0" 
                            class="px-5 py-2 text-xs font-black bg-gradient-to-r from-purple-700 to-indigo-700 hover:from-purple-600 hover:to-indigo-600 disabled:opacity-40 text-white rounded-xl transition shadow-xs flex items-center gap-2">
                        <i class="fa-solid fa-paper-plane text-amber-300"></i>
                        <span>Plaatsen</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Timeline Posts List -->
    <div class="space-y-3">
        @if($posts->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-12 text-center space-y-3">
            <div class="w-14 h-14 mx-auto rounded-full bg-purple-50 text-purple-400 flex items-center justify-center text-2xl">
                <i class="fa-solid fa-comment-slash"></i>
            </div>
            <h3 class="text-sm font-black text-slate-800">De tijdlijn is nog stil</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">
                Wees de allereerste patiënt die een spraakmakend bericht deelt op het openbare prikbord!
            </p>
        </div>
        @else
        <div class="space-y-3">
            @foreach($posts as $post)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 hover:border-purple-200 transition">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3.5">
                        <a href="{{ route('profile.show', $post->character->name) }}" class="shrink-0">
                            <img src="{{ $post->character->avatar_url }}" alt="{{ $post->character->name }}" class="w-11 h-11 rounded-2xl object-cover border border-slate-200 shadow-2xs hover:scale-105 transition-transform">
                        </a>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('profile.show', $post->character->name) }}" class="font-black text-xs sm:text-sm text-slate-900 hover:text-purple-700 transition">
                                    {{ $post->character->name }}
                                </a>
                                @if($post->character->user && $post->character->user->role !== 'user')
                                    @php $badge = $post->character->user->role_badge; @endphp
                                    <span class="text-[9px] font-black uppercase tracking-wider {{ $badge['bg'] }} {{ $badge['text'] }} border {{ $badge['border'] }} px-2 py-0.5 rounded-md inline-flex items-center gap-1 shadow-2xs">
                                        <i class="fa-solid {{ $badge['icon'] }} text-[8px]"></i> {{ $badge['label'] }}
                                    </span>
                                @endif
                                <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">
                                    Level {{ $post->character->level }}
                                </span>
                                @if($post->character->gang)
                                <a href="{{ route('gangs.index') }}" class="text-[10px] font-black text-purple-700 bg-purple-50 border border-purple-200 px-2 py-0.5 rounded-full hover:bg-purple-100 transition">
                                    [{{ $post->character->gang->tag }}] {{ $post->character->gang->name }}
                                </a>
                                @endif
                                <span class="text-[11px] text-slate-400 font-medium ml-1">
                                    • {{ $post->created_at->diffForHumans() }}
                                </span>
                            </div>

                            <!-- Post Content -->
                            <p class="text-xs sm:text-sm text-slate-700 mt-2 font-medium leading-relaxed whitespace-pre-line">
                                {{ $post->content }}
                            </p>
                        </div>
                    </div>

                    <!-- Delete button if own post OR moderator -->
                    @if($post->character_id === $character->id || Auth::user()->canModerate())
                    <form action="{{ route('timeline.destroy', $post) }}" method="POST"
                          data-confirm="{{ $post->character_id === $character->id ? 'Weet je zeker dat je dit bericht definitief van de tijdlijn wilt verwijderen?' : 'Weet je zeker dat je dit bericht als moderator/beheerder wilt verwijderen?' }}"
                          data-confirm-title="{{ $post->character_id === $character->id ? 'Tijdlijn Bericht Verwijderen' : 'Bericht Verwijderen (Moderatie)' }}"
                          data-confirm-btn="Bericht Verwijderen"
                          data-confirm-type="danger"
                          data-confirm-icon="fa-trash-can"
                          class="shrink-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="{{ $post->character_id === $character->id ? 'text-slate-400 hover:text-rose-600 hover:bg-slate-100' : 'text-rose-600 hover:text-white bg-rose-50 hover:bg-rose-600 border border-rose-200 hover:border-rose-600 px-2.5 shadow-2xs' }} p-1.5 rounded-lg transition cursor-pointer flex items-center gap-1.5 text-[11px] font-bold" 
                                title="{{ $post->character_id === $character->id ? 'Verwijderen' : 'Verwijderen als moderator/beheerder' }}">
                            <i class="fa-regular fa-trash-can text-xs"></i>
                            @if($post->character_id !== $character->id)
                                <span class="hidden sm:inline">Mod Verwijder</span>
                            @endif
                        </button>
                    </form>
                    @endif
                </div>

                <!-- Footer Actions: Like, Comment count, etc -->
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <div class="flex items-center gap-4">
                        <form action="{{ route('timeline.like', $post) }}" method="POST">
                            @csrf
                            <button type="submit" class="flex items-center gap-1.5 text-slate-500 hover:text-pink-600 transition font-bold group">
                                <span class="p-1 rounded-lg group-hover:bg-pink-50 transition">
                                    <i class="fa-regular fa-heart group-hover:text-pink-600"></i>
                                </span>
                                <span class="text-[11px]">{{ $post->likes_count }} Likes</span>
                            </button>
                        </form>

                        <a href="{{ route('messages.index') }}" class="flex items-center gap-1.5 text-slate-500 hover:text-purple-600 transition font-bold group">
                            <span class="p-1 rounded-lg group-hover:bg-purple-50 transition">
                                <i class="fa-regular fa-paper-plane group-hover:text-purple-600"></i>
                            </span>
                            <span class="text-[11px]">Privébericht</span>
                        </a>
                    </div>

                    <a href="{{ route('profile.show', $post->character->name) }}" class="text-[11px] font-bold text-slate-400 hover:text-slate-600 transition">
                        Bekijk Profiel &rarr;
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-xs">
            {{ $posts->links() }}
        </div>
        @endif
    </div>

    <!-- Bottom Podium Ranglijst (Kliniek Respect Top 10, Clean TBS Style) -->
    @if(isset($topRankings) && $topRankings->count())
    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-5">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-slate-900">
                <i class="fa-solid fa-trophy text-amber-500"></i>
                <span>KLINIEK RESPECT RANGLIJST (TOP 10)</span>
            </div>
            <a href="{{ route('leaderboard.index') }}" class="text-xs font-bold text-purple-600 hover:text-purple-800 transition">
                Volledige ranglijst bekijken &rarr;
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 lg:grid-cols-10 gap-3 pt-2">
            @foreach($topRankings as $idx => $ranked)
            <div class="flex flex-col items-center text-center group">
                <div class="relative mb-2">
                    <img src="{{ $ranked->avatar_url }}" alt="{{ $ranked->name }}" 
                         onerror="this.onerror=null; this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}';"
                         class="w-14 h-14 sm:w-16 sm:h-16 rounded-full object-cover border-2 {{ $idx === 0 ? 'border-amber-400 ring-2 ring-amber-400/30' : ($idx === 1 ? 'border-purple-400' : 'border-slate-200') }} group-hover:scale-105 transition-transform shadow-xs">
                    
                    <span class="absolute -top-1 -left-1 w-5 h-5 rounded-full {{ $idx === 0 ? 'bg-amber-400 text-slate-950 font-black' : ($idx < 3 ? 'bg-purple-600 text-white font-bold' : 'bg-slate-800 text-white font-bold') }} text-[11px] flex items-center justify-center shadow-xs">
                        {{ $idx + 1 }}
                    </span>

                    <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 text-white text-[9px] flex items-center justify-center">
                        <i class="fa-solid fa-check"></i>
                    </span>
                </div>

                <a href="{{ route('profile.show', $ranked->name) }}" class="text-xs font-bold text-slate-900 group-hover:text-purple-600 transition truncate w-full block" title="{{ $ranked->name }}">
                    {{ $ranked->name }}
                </a>
                <span class="text-[10px] font-bold text-slate-400">
                    {{ number_format($ranked->respect, 0, ',', '.') }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
