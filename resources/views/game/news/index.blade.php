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
            <a href="{{ route('news.show', $pinned->slug) }}" class="bg-gradient-to-br from-purple-50/70 via-white to-amber-50/30 dark:from-purple-950/40 dark:via-slate-900 dark:to-slate-900/90 rounded-2xl border-2 border-purple-200 dark:border-purple-800/60 hover:border-purple-400 dark:hover:border-purple-500 p-5 shadow-xs hover:shadow-md transition group flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <span class="inline-flex items-center gap-1.5 bg-amber-100 dark:bg-amber-950/80 text-amber-900 dark:text-amber-300 border border-amber-300 dark:border-amber-700 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full shadow-2xs">
                            <i class="fa-solid fa-star text-amber-600 dark:text-amber-400 text-[9px]"></i> Belangrijk
                        </span>
                        <span class="text-[11px] font-semibold text-slate-400">
                            {{ $pinned->created_at->diffForHumans() }}
                        </span>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="text-2xl shrink-0 group-hover:scale-110 transition-transform">{{ $pinned->emoji }}</span>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white group-hover:text-purple-700 dark:group-hover:text-purple-400 transition leading-snug">
                                {{ $pinned->title }}
                            </h3>
                            <p class="text-xs text-slate-600 dark:text-slate-300 font-medium line-clamp-2 mt-1">
                                {{ Str::limit(strip_tags($pinned->content), 120) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-purple-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 font-bold">
                    <div class="flex items-center gap-2">
                        @if($pinned->author)
                            <img src="{{ $pinned->author->avatar_url }}" alt="{{ $pinned->author->name }}" class="w-5 h-5 rounded-full object-cover">
                            <span class="text-slate-800 dark:text-slate-200 text-[11px]">{{ $pinned->author->name }}</span>
                        @else
                            <span class="text-slate-800 dark:text-slate-200 text-[11px]">Directie TBS Kliniek</span>
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
            <a href="{{ route('news.show', $article->slug) }}" class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/80 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-600 p-5 shadow-xs hover:shadow-md transition group flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xl shrink-0 group-hover:scale-110 transition-transform">{{ $article->emoji }}</span>
                            <span class="text-[10px] font-black uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-2 py-0.5 rounded-md">
                                Update
                            </span>
                        </div>
                        <span class="text-[11px] font-semibold text-slate-400">
                            {{ $article->created_at->diffForHumans() }}
                        </span>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white group-hover:text-purple-700 dark:group-hover:text-purple-400 transition leading-snug">
                            {{ $article->title }}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium line-clamp-2 mt-1">
                            {{ Str::limit(strip_tags($article->content), 120) }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 font-bold">
                    <div class="flex items-center gap-2">
                        @if($article->author)
                            <img src="{{ $article->author->avatar_url }}" alt="{{ $article->author->name }}" class="w-5 h-5 rounded-full object-cover border border-slate-200 dark:border-slate-700">
                            <span class="text-slate-800 dark:text-slate-200 text-[11px]">{{ $article->author->name }}</span>
                        @else
                            <span class="text-slate-800 dark:text-slate-200 text-[11px]">TBS Kliniek Staf</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-3 text-[11px]">
                        <span><i class="fa-regular fa-eye mr-1 text-slate-400"></i>{{ $article->views_count }}</span>
                        <span class="text-purple-600 dark:text-purple-400"><i class="fa-regular fa-comments mr-1"></i>{{ $article->comments_count }}</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        <div class="p-4 bg-white dark:bg-[#111827] rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
            {{ $articles->links() }}
        </div>
        @endif
    </div>

    <!-- REFERRAL SECTIE ONDER HET NIEUWS (Vrienden uitnodigen met unieke @username) -->
    @if(isset($referralStats))
    <div class="bg-white dark:bg-[#111827] rounded-2xl p-5 sm:p-6 shadow-xs border border-purple-200/90 dark:border-slate-800 relative overflow-hidden" 
         x-data="{ 
             copied: false,
             copyLink() {
                 navigator.clipboard.writeText('{{ $referralStats['link'] }}');
                 this.copied = true;
                 setTimeout(() => this.copied = false, 2500);
             }
         }">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-start gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-purple-50 dark:bg-slate-800 border border-purple-200 dark:border-slate-700 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl shrink-0 mt-0.5 shadow-2xs">
                    <i class="fa-solid fa-crown text-amber-500"></i>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/40">
                            <i class="fa-solid fa-gift text-amber-500 text-[10px]"></i>
                            Gratis VIP Actie
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 dark:bg-purple-950/50 text-purple-800 dark:text-purple-300 border border-purple-200 dark:border-purple-800/40">
                            3 Vrienden = 1 Maand VIP
                        </span>
                    </div>
                    <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight">
                        Nodig Vrienden Uit via jouw @Gebruikersnaam Link
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium leading-relaxed max-w-xl">
                        Deel jouw persoonlijke referral link met je vrienden of in Discord groepen. Voor elke 3 actieve spelers ontvang jij direct <strong class="text-purple-700 dark:text-purple-400 font-extrabold">1 maand gratis VIP</strong>!
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0 self-start md:self-center p-2.5 rounded-xl bg-slate-50 dark:bg-purple-950/40 border border-slate-200 dark:border-purple-800/40 shadow-2xs">
                <div class="text-center px-2">
                    <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 dark:text-purple-300/70 block mb-0.5">Aangebracht</span>
                    <span class="text-xl font-black text-slate-900 dark:text-white leading-none">{{ $referralStats['total'] }}</span>
                </div>
                <div class="w-px h-6 bg-slate-200 dark:bg-purple-800/50"></div>
                <div class="text-center px-2">
                    <span class="text-[9px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400 block mb-0.5">VIP Maanden</span>
                    <span class="text-xl font-black text-amber-600 dark:text-amber-400 leading-none">{{ $referralStats['rewarded_months'] }}</span>
                </div>
            </div>
        </div>

        @if(! $referralStats['has_username'])
        <!-- Notice when username is not configured yet -->
        <div class="mt-4 p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3 text-xs text-amber-900 dark:text-amber-200 font-bold">
                <i class="fa-solid fa-triangle-exclamation text-amber-500 text-lg shrink-0"></i>
                <span>Je hebt nog geen unieke @gebruikersnaam ingesteld! Stel eerst je gebruikersnaam in zodat jouw link jouw eigen naam bevat.</span>
            </div>
            <a href="{{ route('settings.account') }}" class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs transition shrink-0 text-center shadow-xs">
                Stel @gebruikersnaam In
            </a>
        </div>
        @endif

        <!-- Referral Link Copy Box -->
        <div class="pt-4">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-purple-300/70 block mb-1.5">
                Jouw Persoonlijke Aanmeldlink (met @<span>{{ $referralStats['username'] }}</span>)
            </span>
            <div class="flex flex-col sm:flex-row items-stretch gap-2">
                <div class="relative flex-1">
                    <input type="text" readonly value="{{ $referralStats['link'] }}" 
                           @click="$el.select()"
                           class="w-full rounded-xl px-4 py-2.5 text-xs sm:text-sm font-mono font-bold text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-purple-950/50 border border-slate-200 dark:border-purple-800 outline-none select-all transition shadow-2xs">
                </div>
                <button type="button" 
                        @click="copyLink()"
                        class="px-5 py-2.5 rounded-xl bg-slate-900 dark:bg-purple-600 hover:bg-slate-800 dark:hover:bg-purple-700 active:scale-95 text-white font-extrabold text-xs uppercase tracking-wider transition cursor-pointer shadow-xs flex items-center justify-center gap-2 shrink-0">
                    <i class="fa-solid" :class="copied ? 'fa-check text-emerald-400' : 'fa-copy'"></i>
                    <span x-text="copied ? 'Gekopieerd!' : 'Kopieer Link'">Kopieer Link</span>
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
