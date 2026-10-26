@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginatie" class="flex items-center justify-between gap-3 select-none pt-2">
        
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl bg-slate-100 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-800 text-slate-400 dark:text-slate-600 cursor-not-allowed">
                <i class="fa-solid fa-chevron-left text-[10px]"></i>
                <span>Vorige</span>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 hover:border-purple-400 hover:text-purple-600 dark:hover:text-purple-300 shadow-2xs transition active:scale-95">
                <i class="fa-solid fa-chevron-left text-[10px]"></i>
                <span>Vorige</span>
            </a>
        @endif

        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
            Pagina <strong class="font-bold text-slate-800 dark:text-slate-200 font-mono">{{ $paginator->currentPage() }}</strong>
        </span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 hover:border-purple-400 hover:text-purple-600 dark:hover:text-purple-300 shadow-2xs transition active:scale-95">
                <span>Volgende</span>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </a>
        @else
            <span class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl bg-slate-100 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-800 text-slate-400 dark:text-slate-600 cursor-not-allowed">
                <span>Volgende</span>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </span>
        @endif

    </nav>
@endif
