@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginatie" class="flex flex-col sm:flex-row items-center justify-between gap-3 select-none pt-2">
        
        {{-- MOBILE VIEW (< sm) --}}
        <div class="flex items-center justify-between w-full sm:hidden gap-2">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold rounded-xl bg-slate-100 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-800 text-slate-400 dark:text-slate-600 cursor-not-allowed">
                    <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    <span>Vorige</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" data-turbo="false" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 hover:border-purple-400 dark:hover:border-purple-500 hover:text-purple-600 dark:hover:text-purple-300 shadow-2xs transition active:scale-95">
                    <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    <span>Vorige</span>
                </a>
            @endif

            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                Pagina <strong class="font-bold text-slate-800 dark:text-slate-200 font-mono">{{ $paginator->currentPage() }}</strong>
                @if (method_exists($paginator, 'lastPage'))
                    van <span class="font-mono">{{ $paginator->lastPage() }}</span>
                @endif
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" data-turbo="false" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 hover:border-purple-400 dark:hover:border-purple-500 hover:text-purple-600 dark:hover:text-purple-300 shadow-2xs transition active:scale-95">
                    <span>Volgende</span>
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </a>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold rounded-xl bg-slate-100 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-800 text-slate-400 dark:text-slate-600 cursor-not-allowed">
                    <span>Volgende</span>
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </span>
            @endif
        </div>

        {{-- DESKTOP VIEW (>= sm) --}}
        <div class="hidden sm:flex sm:items-center sm:justify-between w-full gap-4">
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Toont
                    @if ($paginator->firstItem())
                        <span class="font-bold text-slate-700 dark:text-slate-200 font-mono">{{ $paginator->firstItem() }}</span>
                        tot
                        <span class="font-bold text-slate-700 dark:text-slate-200 font-mono">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    van
                    <span class="font-bold text-slate-700 dark:text-slate-200 font-mono">{{ $paginator->total() }}</span>
                    resultaten
                </p>
            </div>

            <div class="flex items-center gap-1">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-800 text-slate-400 dark:text-slate-600 flex items-center justify-center text-xs cursor-not-allowed" aria-disabled="true">
                        <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" data-turbo="false" class="w-8 h-8 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:border-purple-400 dark:hover:border-purple-500 hover:text-purple-600 dark:hover:text-purple-300 flex items-center justify-center text-xs transition shadow-2xs active:scale-95" aria-label="Vorige pagina">
                        <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    </a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="w-6 text-center text-xs text-slate-400 font-mono" aria-disabled="true">{{ $element }}</span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="w-8 h-8 rounded-xl bg-purple-600 text-white font-mono font-black text-xs flex items-center justify-center shadow-xs shadow-purple-600/30 border border-purple-600" aria-current="page">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" data-turbo="false" class="w-8 h-8 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:border-purple-400 dark:hover:border-purple-500 hover:text-purple-600 dark:hover:text-purple-300 font-mono font-bold text-xs flex items-center justify-center transition shadow-2xs active:scale-95">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" data-turbo="false" class="w-8 h-8 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:border-purple-400 dark:hover:border-purple-500 hover:text-purple-600 dark:hover:text-purple-300 flex items-center justify-center text-xs transition shadow-2xs active:scale-95" aria-label="Volgende pagina">
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </a>
                @else
                    <span class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-800 text-slate-400 dark:text-slate-600 flex items-center justify-center text-xs cursor-not-allowed" aria-disabled="true">
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </span>
                @endif
            </div>
        </div>

    </nav>
@endif
