@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Top Hero Banner -->
    <div class="rounded-2xl shadow-xs p-6 sm:p-8 text-slate-800 relative overflow-hidden bg-white border border-slate-200">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-50 border border-purple-100 flex items-center justify-center text-2xl text-purple-600 shadow-2xs shrink-0">
                    <i class="fa-solid fa-newspaper"></i>
                </div>
                <div>
                    <div class="inline-flex items-center gap-2 bg-purple-50 text-purple-700 text-[10px] font-black uppercase tracking-widest px-2.5 py-0.5 rounded-md border border-purple-200 mb-1">
                        Officiële Mededelingen
                    </div>
                    <h1 class="text-xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        Kliniek Nieuws
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5 max-w-xl">
                        Blijf op de hoogte van alle updates, spelregels, evenementen en vernieuwingen in de TBS Kliniek.
                    </p>
                </div>
            </div>

            @if(Auth::user()->canModerate())
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('admin.news.create') }}" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-black transition shadow-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-plus text-[10px]"></i> Nieuw Artikel Schrijven
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Pinned Articles (if any) -->
    @if(isset($pinnedArticles) && $pinnedArticles->isNotEmpty())
    <div class="space-y-3">
        <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-600">
            <i class="fa-solid fa-thumbtack text-amber-500"></i>
            <span>Vastgepinde Berichten</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($pinnedArticles as $pinned)
            <a href="{{ route('news.show', $pinned->slug) }}" class="bg-gradient-to-br from-purple-50/70 via-white to-amber-50/30 rounded-2xl border-2 border-purple-200 hover:border-purple-400 p-5 shadow-xs hover:shadow-md transition group flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <span class="inline-flex items-center gap-1.5 bg-amber-100 text-amber-900 border border-amber-300 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full shadow-2xs">
                            <i class="fa-solid fa-star text-amber-600 text-[9px]"></i> Belangrijk
                        </span>
                        <span class="text-[11px] font-semibold text-slate-400">
                            {{ $pinned->created_at->diffForHumans() }}
                        </span>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="text-2xl shrink-0 group-hover:scale-110 transition-transform">{{ $pinned->emoji }}</span>
                        <div>
                            <h3 class="text-base font-black text-slate-900 group-hover:text-purple-700 transition leading-snug">
                                {{ $pinned->title }}
                            </h3>
                            <p class="text-xs text-slate-600 font-medium line-clamp-2 mt-1">
                                {{ Str::limit(strip_tags($pinned->content), 120) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-purple-100 flex items-center justify-between text-xs text-slate-500 font-bold">
                    <div class="flex items-center gap-2">
                        @if($pinned->author)
                            <img src="{{ $pinned->author->avatar_url }}" alt="{{ $pinned->author->name }}" class="w-5 h-5 rounded-full object-cover">
                            <span class="text-slate-800 text-[11px]">{{ $pinned->author->name }}</span>
                        @else
                            <span class="text-slate-800 text-[11px]">Directie TBS Kliniek</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-3 text-[11px]">
                        <span><i class="fa-regular fa-eye mr-1 text-slate-400"></i>{{ $pinned->views_count }}</span>
                        <span><i class="fa-regular fa-comments mr-1 text-purple-500"></i>{{ $pinned->comments_count }}</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- All Articles List -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-black uppercase tracking-wider text-slate-600 flex items-center gap-2">
                <i class="fa-solid fa-list-ul text-purple-600"></i>
                <span>Alle Nieuwsberichten ({{ $articles->total() }})</span>
            </h2>
        </div>

        @if($articles->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-12 text-center space-y-3">
            <div class="w-14 h-14 mx-auto rounded-full bg-purple-50 text-purple-400 flex items-center justify-center text-2xl">
                <i class="fa-solid fa-newspaper"></i>
            </div>
            <h3 class="text-sm font-black text-slate-800">Nog geen nieuwsberichten</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">
                Er zijn momenteel nog geen officiële nieuwsartikelen gepubliceerd door de leiding.
            </p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($articles as $article)
            <a href="{{ route('news.show', $article->slug) }}" class="bg-white rounded-2xl border border-slate-200/80 hover:border-purple-300 p-5 shadow-xs hover:shadow-md transition group flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xl shrink-0 group-hover:scale-110 transition-transform">{{ $article->emoji }}</span>
                            <span class="text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md">
                                Update
                            </span>
                        </div>
                        <span class="text-[11px] font-semibold text-slate-400">
                            {{ $article->created_at->diffForHumans() }}
                        </span>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-black text-slate-900 group-hover:text-purple-700 transition leading-snug">
                            {{ $article->title }}
                        </h3>
                        <p class="text-xs text-slate-500 font-medium line-clamp-2 mt-1">
                            {{ Str::limit(strip_tags($article->content), 120) }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-bold">
                    <div class="flex items-center gap-2">
                        @if($article->author)
                            <img src="{{ $article->author->avatar_url }}" alt="{{ $article->author->name }}" class="w-5 h-5 rounded-full object-cover border border-slate-200">
                            <span class="text-slate-800 text-[11px]">{{ $article->author->name }}</span>
                        @else
                            <span class="text-slate-800 text-[11px]">TBS Kliniek Staf</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-3 text-[11px]">
                        <span><i class="fa-regular fa-eye mr-1 text-slate-400"></i>{{ $article->views_count }}</span>
                        <span class="text-purple-600"><i class="fa-regular fa-comments mr-1"></i>{{ $article->comments_count }}</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-xs">
            {{ $articles->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
