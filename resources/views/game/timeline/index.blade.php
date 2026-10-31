@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Top Hero Banner -->
    <div class="rounded-2xl shadow-xs p-6 sm:p-8 text-slate-800 dark:text-slate-100 relative overflow-hidden bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-50 dark:bg-purple-950/60 border border-purple-100 dark:border-purple-800/70 flex items-center justify-center text-2xl text-purple-600 dark:text-purple-400 shadow-2xs shrink-0">
                    <i class="fa-solid fa-bullhorn"></i>
                </div>
                <div>
                    <div class="inline-flex items-center gap-2 bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 text-[10px] font-black uppercase tracking-widest px-2.5 py-0.5 rounded-md border border-purple-200/80 dark:border-purple-800/70 mb-1">
                        TBS Kliniek Sociaal
                    </div>
                    <h1 class="text-xl sm:text-3xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                        Kliniek Tijdlijn (Feed)
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 max-w-xl">
                        De openbare praatpaal van de TBS kliniek. Deel updates, reageer op medepatiënten, deel smileys en volg alle ontwikkelingen op de voet.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <span class="px-3.5 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 text-xs font-bold text-purple-700 dark:text-purple-300 shadow-2xs">
                    <i class="fa-solid fa-comment-dots text-purple-600 dark:text-purple-400 mr-1.5"></i> Live Kliniek Feed
                </span>
            </div>
        </div>
    </div>

    <!-- Compose Box (Twitter Style with Smileys / Emoji Bar) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-5" 
         x-data="{ 
             text: '', 
             showEmojis: false,
             emojis: ['😂', '🔥', '👑', '❤️', '💀', '🥊', '💪', '💰', '💊', '🚬', '⚡', '😈', '💯', '😎', '👀', '🚨'],
             insertEmoji(emoji) {
                 this.text += emoji;
                 $refs.postInput.focus();
             }
         }">
        <form action="{{ route('timeline.store') }}" method="POST" class="space-y-3">
            @csrf
            <div class="flex items-start gap-3.5">
                <img src="{{ $character->avatar_url }}" alt="{{ $character->name }}" class="w-11 h-11 rounded-2xl object-cover border-2 border-purple-200 dark:border-purple-700 shadow-xs shrink-0">
                <div class="flex-1 min-w-0">
                    <textarea x-ref="postInput"
                              name="content" 
                              x-model="text"
                              maxlength="280"
                              required 
                              rows="3" 
                              placeholder="Wat gebeurt er op de afdeling, {{ $character->name }}? Deel een bericht (max 280 tekens)..."
                              class="w-full text-xs sm:text-sm font-semibold bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl p-3.5 focus:bg-white dark:focus:bg-slate-900 focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none resize-none transition"></textarea>
                </div>
            </div>

            <!-- Smileys / Emoji Bar -->
            <div class="sm:pl-14 flex flex-wrap items-center gap-1.5 pt-1">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 mr-1 flex items-center gap-1">
                    <i class="fa-regular fa-face-smile text-amber-500"></i> Smileys:
                </span>
                <template x-for="e in emojis" :key="e">
                    <button type="button" 
                            @click="insertEmoji(e)"
                            class="w-8 h-8 sm:w-7 sm:h-7 rounded-lg bg-slate-50 dark:bg-slate-800 hover:bg-purple-100 dark:hover:bg-purple-950/80 border border-slate-200/80 dark:border-slate-700 hover:border-purple-300 dark:hover:border-purple-700 text-sm flex items-center justify-center transition active:scale-95 cursor-pointer">
                        <span x-text="e"></span>
                    </button>
                </template>
            </div>

            <div class="flex items-center justify-between pt-2 sm:pl-14 border-t border-slate-100 dark:border-slate-800/80">
                <div class="text-[11px] font-bold" :class="text.length > 250 ? 'text-rose-500 font-extrabold' : 'text-slate-400 dark:text-slate-500'">
                    <span x-text="text.length"></span> / 280 tekens
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" 
                            :disabled="text.trim().length < 2" 
                            class="px-5 py-2 text-xs font-black bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 disabled:opacity-40 text-white rounded-xl transition shadow-xs flex items-center gap-2 cursor-pointer active:scale-95">
                        <i class="fa-solid fa-paper-plane text-amber-300"></i>
                        <span>Plaatsen</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Timeline Posts List -->
    <div class="space-y-4">
        @if($posts->isEmpty())
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-12 text-center space-y-3">
            <div class="w-14 h-14 mx-auto rounded-full bg-purple-50 dark:bg-purple-950/60 text-purple-400 dark:text-purple-300 flex items-center justify-center text-2xl">
                <i class="fa-solid fa-comment-slash"></i>
            </div>
            <h3 class="text-sm font-black text-slate-800 dark:text-slate-100">De tijdlijn is nog stil</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                Wees de allereerste patiënt die een spraakmakend bericht deelt op het openbare prikbord!
            </p>
        </div>
        @else
        <div class="space-y-4">
            @foreach($posts as $post)
            @if($post->character)
            @php
                $isLikedByMe = $post->isLikedBy($character);
                $myEmoji = $post->likedEmojiBy($character);
                $commentsCount = $post->comments->count();
            @endphp
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-5 hover:border-purple-300 dark:hover:border-purple-700/80 transition"
                 x-data="{ 
                     showReplies: {{ $commentsCount > 0 && request('comment_post') == $post->id ? 'true' : 'false' }},
                     replyText: '',
                     showReactionTray: false,
                     replyEmojis: ['😂', '🔥', '👑', '❤️', '💀', '🥊', '💯', '👍'],
                     insertReplyEmoji(e) {
                         this.replyText += e;
                         $refs.replyInput.focus();
                     }
                 }">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3.5">
                        <a href="{{ route('profile.show', $post->character->name) }}" class="shrink-0">
                            <img src="{{ $post->character->avatar_url }}" alt="{{ $post->character->name }}" class="w-11 h-11 rounded-2xl object-cover border border-slate-200 dark:border-slate-700 shadow-2xs hover:scale-105 transition-transform">
                        </a>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('profile.show', $post->character->name) }}" class="font-black text-xs sm:text-sm text-slate-900 dark:text-slate-100 hover:text-purple-600 dark:hover:text-purple-400 transition">
                                    {{ $post->character->name }}
                                </a>
                                @if($post->character->user && $post->character->user->role !== 'user')
                                    @php $badge = $post->character->user->role_badge; @endphp
                                    <span class="text-[9px] font-black uppercase tracking-wider {{ $badge['bg'] }} {{ $badge['text'] }} border {{ $badge['border'] }} px-2 py-0.5 rounded-md inline-flex items-center gap-1 shadow-2xs">
                                        <i class="fa-solid {{ $badge['icon'] }} text-[8px]"></i> {{ $badge['label'] }}
                                    </span>
                                @endif
                                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 px-2 py-0.5 rounded-full">
                                    Level {{ $post->character->level }}
                                </span>
                                @if($post->character->gang)
                                <a href="{{ route('gangs.index') }}" class="text-[10px] font-black text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 px-2 py-0.5 rounded-full hover:bg-purple-100 dark:hover:bg-purple-900/60 transition">
                                    [{{ $post->character->gang->tag }}] {{ $post->character->gang->name }}
                                </a>
                                @endif
                                <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium ml-1">
                                    &bull; {{ $post->created_at->diffForHumans() }}
                                </span>
                            </div>

                            <!-- Post Content -->
                            <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 mt-2 font-medium leading-relaxed whitespace-pre-line">
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
                                class="{{ $post->character_id === $character->id ? 'text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-slate-100 dark:hover:bg-slate-800' : 'text-rose-600 dark:text-rose-400 hover:text-white bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-600 dark:hover:bg-rose-600 border border-rose-200/80 dark:border-rose-900/70 hover:border-rose-600 px-2.5 shadow-2xs' }} p-1.5 rounded-lg transition cursor-pointer flex items-center gap-1.5 text-[11px] font-bold" 
                                title="{{ $post->character_id === $character->id ? 'Verwijderen' : 'Verwijderen als moderator/beheerder' }}">
                            <i class="fa-regular fa-trash-can text-xs"></i>
                            @if($post->character_id !== $character->id)
                                <span class="hidden sm:inline">Mod Verwijder</span>
                            @endif
                        </button>
                    </form>
                    @endif
                </div>

                <!-- Footer Actions: Like, Reacties, Privébericht -->
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-500 dark:text-slate-400">
                    <div class="flex items-center gap-3 sm:gap-4 relative">
                        
                        <!-- Like / Reaction Button with Emoji Tray -->
                        <div class="relative flex items-center" @mouseleave="showReactionTray = false">
                            <form action="{{ route('timeline.like', $post) }}" method="POST" class="inline-flex items-center">
                                @csrf
                                <input type="hidden" name="emoji" value="{{ $myEmoji ?: '❤️' }}">
                                <button type="submit" 
                                        @contextmenu.prevent="showReactionTray = true"
                                        class="flex items-center gap-1.5 font-bold transition group cursor-pointer {{ $isLikedByMe ? 'text-pink-600 dark:text-pink-400 font-black' : 'text-slate-500 dark:text-slate-400 hover:text-pink-600 dark:hover:text-pink-400' }}">
                                    <span class="p-1 rounded-lg {{ $isLikedByMe ? 'bg-pink-50 dark:bg-pink-950/60' : 'group-hover:bg-pink-50 dark:group-hover:bg-pink-950/40' }} transition">
                                        @if($isLikedByMe && $myEmoji && $myEmoji !== '❤️')
                                            <span class="text-sm">{{ $myEmoji }}</span>
                                        @else
                                            <i class="{{ $isLikedByMe ? 'fa-solid text-pink-600 dark:text-pink-400' : 'fa-regular' }} fa-heart group-hover:text-pink-600"></i>
                                        @endif
                                    </span>
                                    <span class="text-[11px]">{{ $post->likes_count }} {{ $post->likes_count === 1 ? 'Like' : 'Likes' }}</span>
                                </button>
                            </form>

                            <!-- Reaction Tray Trigger Button -->
                            <button type="button" 
                                    @click="showReactionTray = !showReactionTray"
                                    class="ml-1 p-1 text-slate-400 dark:text-slate-400 hover:text-amber-500 dark:hover:text-amber-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition text-xs cursor-pointer" 
                                    title="Kies smiley reactie">
                                <i class="fa-regular fa-face-smile"></i>
                            </button>

                            <!-- Quick Emoji Reactions Popover Tray -->
                            <div x-show="showReactionTray" 
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                 class="absolute left-0 bottom-full mb-1.5 z-30 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-700 rounded-2xl shadow-lg p-1.5 flex items-center gap-1 backdrop-blur-md">
                                @foreach(['❤️', '🔥', '😂', '👑', '💀', '🥊', '👍', '💪'] as $emo)
                                <form action="{{ route('timeline.like', $post) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="emoji" value="{{ $emo }}">
                                    <button type="submit" 
                                            class="w-7 h-7 rounded-xl hover:bg-purple-100 dark:hover:bg-purple-950/80 flex items-center justify-center text-sm transition transform hover:scale-125 cursor-pointer"
                                            title="Reageer met {{ $emo }}">
                                        {{ $emo }}
                                    </button>
                                </form>
                                @endforeach
                            </div>
                        </div>

                        <!-- Comment / Reageren Button -->
                        <button type="button" 
                                @click="showReplies = !showReplies" 
                                class="flex items-center gap-1.5 font-bold transition group cursor-pointer {{ $commentsCount > 0 ? 'text-purple-700 dark:text-purple-300' : 'text-slate-500 dark:text-slate-400 hover:text-purple-600 dark:hover:text-purple-400' }}">
                            <span class="p-1 rounded-lg {{ $commentsCount > 0 ? 'bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-300' : 'group-hover:bg-purple-50 dark:group-hover:bg-purple-950/40' }} transition">
                                <i class="fa-regular fa-comment group-hover:text-purple-600 dark:group-hover:text-purple-400"></i>
                            </span>
                            <span class="text-[11px]">{{ $commentsCount }} {{ $commentsCount === 1 ? 'Reactie' : 'Reacties' }}</span>
                        </button>

                        <!-- Privébericht Button -->
                        <button type="button" 
                                onclick="if(window.openTBSDirectChat){ window.openTBSDirectChat({{ $post->character->id }}, @js($post->character->name), @js($post->character->avatar_url)); } else { window.location.href='{{ route('chat.index', ['tab' => 'direct', 'recipient' => $post->character->id]) }}'; }"
                                class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400 hover:text-purple-600 dark:hover:text-purple-400 transition font-bold group cursor-pointer">
                            <span class="p-1 rounded-lg group-hover:bg-purple-50 dark:group-hover:bg-purple-950/40 transition">
                                <i class="fa-regular fa-paper-plane group-hover:text-purple-600 dark:group-hover:text-purple-400"></i>
                            </span>
                            <span class="text-[11px] hidden sm:inline">Privébericht</span>
                        </button>
                    </div>

                    <a href="{{ route('profile.show', $post->character->name) }}" class="text-[11px] font-bold text-slate-400 dark:text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
                        Bekijk Profiel &rarr;
                    </a>
                </div>

                <!-- REPLIES / COMMENTS SECTION (Interactive) -->
                <div x-show="showReplies" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800/80 space-y-3">
                    
                    <!-- Existing Comments List -->
                    @if($post->comments->isNotEmpty())
                    <div class="space-y-2.5">
                        @foreach($post->comments as $comment)
                        @if($comment->character)
                        <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-3 flex items-start justify-between gap-3">
                            <div class="flex items-start gap-2.5">
                                <a href="{{ route('profile.show', $comment->character->name) }}" class="shrink-0">
                                    <img src="{{ $comment->character->avatar_url }}" alt="{{ $comment->character->name }}" class="w-7 h-7 rounded-lg object-cover border border-slate-200 dark:border-slate-700 shadow-2xs">
                                </a>
                                <div>
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <a href="{{ route('profile.show', $comment->character->name) }}" class="font-extrabold text-xs text-slate-900 dark:text-slate-100 hover:text-purple-600 dark:hover:text-purple-400 transition">
                                            {{ $comment->character->name }}
                                        </a>
                                        @if($comment->character->user && $comment->character->user->role !== 'user')
                                            @php $cBadge = $comment->character->user->role_badge; @endphp
                                            <span class="text-[8px] font-black uppercase {{ $cBadge['bg'] }} {{ $cBadge['text'] }} px-1.5 py-0.2 rounded border {{ $cBadge['border'] }}">
                                                {{ $cBadge['label'] }}
                                            </span>
                                        @endif
                                        <span class="text-[10px] text-slate-400 dark:text-slate-500">
                                            &bull; {{ $comment->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-700 dark:text-slate-300 font-medium mt-1 leading-relaxed whitespace-pre-line">
                                        {{ $comment->content }}
                                    </p>
                                </div>
                            </div>

                            @if($comment->character_id === $character->id || Auth::user()->canModerate())
                            <form action="{{ route('timeline.comment.destroy', $comment) }}" method="POST"
                                  data-confirm="Weet je zeker dat je deze reactie wilt verwijderen?"
                                  data-confirm-btn="Verwijderen"
                                  data-confirm-type="danger"
                                  class="shrink-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 p-1 text-xs transition cursor-pointer" title="Reactie verwijderen">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                        @endif
                        @endforeach
                    </div>
                    @endif

                    <!-- Reply Input Box with Smileys -->
                    <form action="{{ route('timeline.comment', $post) }}" method="POST" class="space-y-2 pt-1">
                        @csrf
                        <div class="flex items-start gap-2.5">
                            <img src="{{ $character->avatar_url }}" alt="{{ $character->name }}" class="w-8 h-8 rounded-xl object-cover border border-purple-200 dark:border-purple-700 shrink-0">
                            <div class="flex-1">
                                <textarea x-ref="replyInput"
                                          name="content" 
                                          x-model="replyText"
                                          maxlength="300"
                                          required 
                                          rows="2" 
                                          placeholder="Schrijf een reactie op {{ $post->character->name }}..."
                                          class="w-full text-xs font-semibold bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl p-2.5 focus:bg-white dark:focus:bg-slate-900 focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none resize-none transition"></textarea>
                                
                                <div class="flex items-center justify-between pt-1">
                                    <!-- Mini Smiley Bar for Replies -->
                                    <div class="flex items-center gap-1">
                                        <span class="text-[10px] text-slate-400 dark:text-slate-500 font-bold mr-0.5"><i class="fa-regular fa-face-smile text-amber-500"></i>:</span>
                                        <template x-for="re in replyEmojis" :key="re">
                                            <button type="button" 
                                                    @click="insertReplyEmoji(re)"
                                                    class="w-6 h-6 rounded-md bg-slate-50 dark:bg-slate-800 hover:bg-purple-100 dark:hover:bg-purple-950/80 border border-slate-200/80 dark:border-slate-700 text-xs flex items-center justify-center transition cursor-pointer active:scale-95">
                                                <span x-text="re"></span>
                                            </button>
                                        </template>
                                    </div>

                                    <button type="submit" 
                                            :disabled="replyText.trim().length === 0" 
                                            class="px-3.5 py-1.5 text-xs font-extrabold bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 disabled:opacity-40 text-white rounded-lg transition shadow-2xs flex items-center gap-1.5 cursor-pointer active:scale-95">
                                        <i class="fa-solid fa-reply text-[10px]"></i>
                                        <span>Reageren (+5 XP)</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif
            @endforeach
        </div>

        <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
            {{ $posts->links() }}
        </div>
        @endif
    </div>

    <!-- Bottom Podium Ranglijst (Kliniek Respect Top 10, Clean TBS Style) -->
    @if(isset($topRankings) && $topRankings->count())
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-5">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-trophy text-amber-500"></i>
                <h3 class="text-xs font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">Kliniek Respect Ranglijst (Top 10)</h3>
            </div>
            <a href="{{ route('leaderboard.index') }}" class="text-xs font-bold text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 transition">
                Volledige ranglijst bekijken &rarr;
            </a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-5 md:grid-cols-10 gap-3">
            @foreach($topRankings as $rank => $patient)
            <div class="flex flex-col items-center text-center p-2 rounded-xl border border-slate-200/80 dark:border-slate-700/70 hover:border-purple-300 dark:hover:border-purple-600/60 bg-slate-50/90 dark:bg-slate-800/60 transition">
                <div class="relative">
                    <img src="{{ $patient->avatar_url }}" alt="{{ $patient->name }}" class="w-10 h-10 rounded-full object-cover border-2 border-purple-300 dark:border-purple-500/60 shadow-2xs">
                    <span class="absolute -top-1 -left-1 w-4 h-4 rounded-full bg-amber-400 text-slate-950 font-black text-[9px] flex items-center justify-center shadow-xs">
                        {{ $rank + 1 }}
                    </span>
                </div>
                <span class="font-extrabold text-[11px] text-slate-800 dark:text-slate-200 truncate max-w-full mt-1.5">{{ $patient->name }}</span>
                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">{{ number_format($patient->respect) }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
