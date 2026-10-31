@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    <!-- Top Breadcrumb Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('news.index') }}" class="inline-flex items-center gap-2 text-xs font-black text-slate-500 hover:text-purple-600 transition">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Terug naar alle nieuwsberichten</span>
        </a>
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold text-slate-400">
                <i class="fa-regular fa-eye mr-1"></i> {{ number_format($article->views_count, 0, ',', '.') }} weergaven
            </span>
            @if(Auth::user()->canModerate())
            <a href="{{ route('admin.news.edit', $article) }}" class="px-3 py-1 rounded-lg bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-bold transition flex items-center gap-1.5 border border-purple-200">
                <i class="fa-solid fa-pen-to-square"></i> Bewerken in Admin
            </a>
            @endif
        </div>
    </div>

    <!-- Main Article Card (Matching the user screenshot structure) -->
    <article class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 overflow-hidden">
        
        <!-- Header Ribbon -->
        <div class="bg-purple-50/80 dark:bg-purple-950/40 border-b border-purple-100 dark:border-purple-900/50 p-4 sm:p-5 flex items-center justify-between">
            <div class="flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-purple-700 dark:text-purple-300">
                <i class="fa-solid fa-bullhorn text-purple-500"></i>
                <span>Nieuws & Updates</span>
            </div>
            @if($article->is_pinned)
                <span class="bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-700 text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-full flex items-center gap-1 shadow-2xs">
                    <i class="fa-solid fa-thumbtack text-[9px] text-amber-600 dark:text-amber-400"></i> Vastgepind
                </span>
            @endif
        </div>

        <div class="p-6 sm:p-8 space-y-6">
            <!-- Title with Emoji -->
            <div class="border-b border-slate-100 dark:border-slate-800 pb-5">
                <h1 class="text-xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="text-3xl shrink-0">{{ $article->emoji }}</span>
                    <span>{{ $article->title }}</span>
                </h1>
            </div>

            <!-- Author info and Article Body -->
            <div class="flex flex-col md:flex-row items-start gap-6">
                
                <!-- Author Sidebar / Card -->
                <div class="shrink-0 w-full md:w-44 flex flex-row md:flex-col items-center text-center p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-700 gap-3">
                    @if($article->author)
                        <a href="{{ route('profile.show', $article->author->name) }}" class="relative group">
                            <img src="{{ $article->author->avatar_url }}" alt="{{ $article->author->name }}" class="w-16 h-16 rounded-2xl object-cover border-2 border-purple-200 dark:border-purple-700 shadow-2xs group-hover:scale-105 transition-transform">
                        </a>
                        <div class="text-left md:text-center min-w-0 flex-1 md:flex-initial">
                            <a href="{{ route('profile.show', $article->author->name) }}" class="text-xs sm:text-sm font-black text-slate-900 dark:text-white hover:text-purple-600 dark:hover:text-purple-400 transition block truncate">
                                {{ $article->author->name }}
                            </a>
                            @if($article->author->user && $article->author->user->role !== 'user')
                                @php $aBadge = $article->author->user->role_badge; @endphp
                                <span class="text-[9px] font-black uppercase tracking-wider {{ $aBadge['bg'] }} {{ $aBadge['text'] }} border {{ $aBadge['border'] }} px-2 py-0.5 rounded-md inline-flex items-center gap-1 mt-1 shadow-2xs">
                                    <i class="fa-solid {{ $aBadge['icon'] }} text-[8px]"></i> {{ $aBadge['label'] }}
                                </span>
                            @endif
                            <div class="text-[10px] text-slate-400 font-semibold mt-1.5">
                                {{ $article->created_at->diffForHumans() }}
                            </div>
                        </div>
                    @else
                        <div class="w-16 h-16 rounded-2xl bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 flex items-center justify-center text-2xl">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div>
                            <span class="text-xs font-black text-slate-900 dark:text-white block">Directie Kliniek</span>
                            <span class="text-[10px] text-slate-400 font-semibold block mt-1">
                                {{ $article->created_at->diffForHumans() }}
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Article Content Body -->
                <div class="flex-1 min-w-0 w-full">
                    @if($article->image_url)
                    <div class="mb-5 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 shadow-xs">
                        <img src="{{ $article->image_url }}" alt="{{ $article->title }}" class="w-full max-h-96 object-cover" onerror="this.style.display='none'">
                    </div>
                    @endif

                    <div class="text-xs sm:text-sm text-slate-800 dark:text-slate-200 font-medium leading-relaxed news-content prose dark:prose-invert max-w-none">
                        {!! $article->formatted_content !!}
                    </div>

                    @if($article->slug === 'de-grote-geldjacht-750-sterren' || str_contains($article->content, '[cash_event_leaderboard]'))
                        @include('game.news.partials.cash_hunt_leaderboard')
                    @endif
                </div>
            </div>
        </div>

        <!-- Article Footer Bar -->
        <div class="px-6 sm:px-8 py-4 bg-slate-50 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 font-bold">
            <div class="flex items-center gap-3">
                <span><i class="fa-solid fa-clock mr-1 text-slate-400"></i>{{ $article->created_at->translatedFormat('d F Y, H:i') }}</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="flex items-center gap-1 text-purple-700 dark:text-purple-300">
                    <i class="fa-solid fa-comments"></i>
                    <span>{{ $article->comments()->count() }} Reacties</span>
                </span>
            </div>
        </div>
    </article>

    <!-- Comments Section -->
    <div class="space-y-4">
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
            <h2 class="text-sm sm:text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-comments text-purple-600 dark:text-purple-400"></i>
                <span>Reacties op dit artikel ({{ $comments->total() }})</span>
            </h2>
        </div>

        <!-- Topic status banner: Closed vs Open -->
        @if(! $article->comments_enabled)
        <div class="p-6 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-center shadow-xs">
            <div class="inline-flex items-center gap-2 text-rose-600 dark:text-rose-400 font-extrabold text-xs sm:text-sm">
                <i class="fa-solid fa-lock text-rose-500"></i>
                <span>Je kunt niet reageren op een gesloten topic</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">
                De reacties voor dit artikel zijn gesloten door de moderators of leiding.
            </p>
        </div>
        @else
        <!-- New Comment Form -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-5" x-data="{ commentText: '' }">
            <form action="{{ route('news.comment', $article) }}" method="POST" class="space-y-3">
                @csrf
                <div class="flex items-start gap-3.5">
                    <img src="{{ $character->avatar_url }}" alt="{{ $character->name }}" class="w-10 h-10 rounded-xl object-cover border border-purple-200 shadow-2xs shrink-0">
                    <div class="flex-1 min-w-0">
                        <textarea name="content" 
                                  x-model="commentText"
                                   maxlength="500"
                                  required 
                                  rows="3" 
                                  placeholder="Laat een respectvolle reactie achter op dit nieuwsbericht, {{ $character->name }}..."
                                  class="w-full text-xs sm:text-sm font-semibold bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 rounded-xl p-3.5 focus:bg-white dark:focus:bg-slate-900 focus:border-purple-600 focus:ring-2 focus:ring-purple-200 dark:focus:ring-purple-900 outline-none resize-none transition"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1 pl-13">
                    <div class="text-[11px] font-bold" :class="commentText.length > 450 ? 'text-rose-500 font-extrabold' : 'text-slate-400'">
                        <span x-text="commentText.length"></span> / 500 tekens
                    </div>
                    <button type="submit" 
                            :disabled="commentText.trim().length === 0" 
                            class="px-5 py-2 text-xs font-black bg-purple-600 hover:bg-purple-700 disabled:opacity-40 text-white rounded-xl transition shadow-xs flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-paper-plane text-[10px]"></i>
                        <span>Plaats Reactie</span>
                    </button>
                </div>
            </form>
        </div>
        @endif

        <!-- Comments List -->
        @if($comments->isEmpty())
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-8 text-center space-y-2">
            <div class="w-12 h-12 mx-auto rounded-full bg-purple-50 dark:bg-purple-950/60 text-purple-400 flex items-center justify-center text-xl">
                <i class="fa-regular fa-comment-dots"></i>
            </div>
            <h4 class="text-xs font-black text-slate-700 dark:text-slate-200">Nog geen reacties</h4>
            <p class="text-xs text-slate-400 dark:text-slate-500 max-w-sm mx-auto">
                Wees de allereerste patiënt die zijn mening deelt onder dit nieuwsbericht!
            </p>
        </div>
        @else
        <div class="space-y-3">
            @foreach($comments as $comment)
            @if($comment->character)
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs flex items-start justify-between gap-4">
                <div class="flex items-start gap-3.5 min-w-0 flex-1">
                    <a href="{{ route('profile.show', $comment->character->name) }}" class="shrink-0">
                        <img src="{{ $comment->character->avatar_url }}" alt="{{ $comment->character->name }}" class="w-10 h-10 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shadow-2xs">
                    </a>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('profile.show', $comment->character->name) }}" class="font-black text-xs sm:text-sm text-slate-900 dark:text-white hover:text-purple-700 dark:hover:text-purple-400 transition">
                                {{ $comment->character->name }}
                            </a>
                            @if($comment->character->user && $comment->character->user->role !== 'user')
                                @php $cBadge = $comment->character->user->role_badge; @endphp
                                <span class="text-[9px] font-black uppercase tracking-wider {{ $cBadge['bg'] }} {{ $cBadge['text'] }} border {{ $cBadge['border'] }} px-1.5 py-0.2 rounded inline-flex items-center gap-1">
                                    <i class="fa-solid {{ $cBadge['icon'] }} text-[8px]"></i> {{ $cBadge['label'] }}
                                </span>
                            @endif
                            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-1.5 py-0.2 rounded">
                                Lvl {{ $comment->character->level }}
                            </span>
                            @if($comment->character->gang)
                            <span class="text-[10px] font-black text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950 border border-purple-100 dark:border-purple-800 px-1.5 py-0.2 rounded">
                                [{{ $comment->character->gang->tag }}]
                            </span>
                            @endif
                            <span class="text-[10px] text-slate-400 font-medium">
                                • {{ $comment->created_at->diffForHumans() }}
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-200 font-medium leading-relaxed mt-2 whitespace-pre-line">
                            {{ $comment->content }}
                        </p>
                    </div>
                </div>

                <!-- Delete button for Author or Moderator -->
                @if($comment->character_id === $character->id || Auth::user()->canModerate())
                <form action="{{ route('news.comment.destroy', $comment) }}" method="POST"
                      data-confirm="{{ $comment->character_id === $character->id ? 'Weet je zeker dat je jouw reactie wilt verwijderen?' : 'Weet je zeker dat je deze reactie als moderator wilt verwijderen?' }}"
                      data-confirm-title="Reactie Verwijderen"
                      data-confirm-btn="Verwijderen"
                      data-confirm-type="danger"
                      data-confirm-icon="fa-trash-can"
                      class="shrink-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-slate-400 hover:text-rose-600 p-1.5 rounded-lg hover:bg-slate-100 transition cursor-pointer" title="{{ Auth::user()->canModerate() ? 'Verwijderen als moderator' : 'Verwijderen' }}">
                        <i class="fa-regular fa-trash-can text-xs"></i>
                    </button>
                </form>
                @endif
            </div>
            @endif
            @endforeach
        </div>

        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-xs">
            {{ $comments->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
