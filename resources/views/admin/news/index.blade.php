@extends('admin.layout')

@section('admin-content')
<div class="space-y-6">

    <!-- Header Toolbar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-newspaper text-purple-600 dark:text-purple-400"></i>
                <span>Nieuws Artikelen Beheer</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Plaats officiële updates, beheer reacties, voeg afbeeldingen en links toe voor de patiënten.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.news.create') }}" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white text-xs font-black transition shadow-xs flex items-center gap-2 cursor-pointer active:scale-95">
                <i class="fa-solid fa-plus text-amber-300"></i> Nieuw Artikel Aanmaken
            </a>
            <a href="{{ route('news.index') }}" target="_blank" rel="noopener noreferrer" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition border border-slate-200/80 dark:border-slate-700 flex items-center gap-1.5 cursor-pointer active:scale-95">
                <i class="fa-solid fa-external-link text-slate-400 dark:text-slate-500"></i> Openbare Feed
            </a>
        </div>
    </div>

    <!-- Articles Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50/90 dark:bg-slate-800/60 text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200/80 dark:border-slate-800">
                        <th class="py-3 px-4">ARTIKEL</th>
                        <th class="py-3 px-3">AUTEUR</th>
                        <th class="py-3 px-3 text-center">REACTIES STATUS</th>
                        <th class="py-3 px-3 text-right">WEERGAVEN</th>
                        <th class="py-3 px-3 text-center">PINNED</th>
                        <th class="py-3 px-3">DATUM</th>
                        <th class="py-3 px-4 text-right">ACTIE</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 font-medium">
                    @forelse($articles as $art)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl shrink-0">{{ $art->emoji }}</span>
                                <div class="min-w-0">
                                    <a href="{{ route('news.show', $art->slug) }}" target="_blank" rel="noopener noreferrer" class="font-extrabold text-slate-900 dark:text-white text-xs hover:text-purple-600 dark:hover:text-purple-400 transition block truncate">
                                        {{ $art->title }}
                                    </a>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">/nieuws/{{ $art->slug }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-3 text-slate-700 dark:text-slate-300 font-bold">
                            {{ $art->author?->name ?? 'Directie' }}
                        </td>
                        <td class="py-3.5 px-3 text-center">
                            <form action="{{ route('admin.news.toggle-comments', $art) }}" method="POST" class="inline-block">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-extrabold transition cursor-pointer border {{ $art->comments_enabled ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200/80 dark:border-emerald-800/70 hover:bg-emerald-100 dark:hover:bg-emerald-900/40' : 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200/80 dark:border-rose-800/70 hover:bg-rose-100 dark:hover:bg-rose-900/40' }}" title="Klik om reacties te openen of sluiten">
                                    @if($art->comments_enabled)
                                        <i class="fa-solid fa-comments text-emerald-600 dark:text-emerald-400 mr-1"></i> Open ({{ $art->comments_count }})
                                    @else
                                        <i class="fa-solid fa-lock text-rose-600 dark:text-rose-400 mr-1"></i> Gesloten ({{ $art->comments_count }})
                                    @endif
                                </button>
                            </form>
                        </td>
                        <td class="py-3.5 px-3 text-right font-mono font-bold text-slate-700 dark:text-slate-300">
                            {{ number_format($art->views_count, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-3 text-center">
                            @if($art->is_pinned)
                                <span class="bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 text-[10px] font-black px-2 py-0.5 rounded-full border border-amber-200/80 dark:border-amber-800/70 inline-flex items-center gap-1">
                                    <i class="fa-solid fa-thumbtack text-amber-600 dark:text-amber-400"></i> Ja
                                </span>
                            @else
                                <span class="text-slate-400 dark:text-slate-500 text-[10px]">Nee</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-3 text-slate-500 dark:text-slate-400 text-[11px]">
                            {{ $art->created_at->format('d-m-Y H:i') }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('news.show', $art->slug) }}" target="_blank" rel="noopener noreferrer" class="p-1.5 rounded-lg text-slate-400 dark:text-slate-500 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Bekijk artikel">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </a>
                                <a href="{{ route('admin.news.edit', $art) }}" class="p-1.5 rounded-lg text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-950/50 transition font-bold" title="Bewerken">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </a>
                                <form action="{{ route('admin.news.destroy', $art) }}" method="POST"
                                      data-confirm="Weet je zeker dat je dit nieuwsartikel definitief wilt verwijderen?"
                                      data-confirm-title="Nieuwsartikel Verwijderen"
                                      data-confirm-btn="Verwijderen"
                                      data-confirm-type="danger"
                                      data-confirm-icon="fa-trash-can"
                                      class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-rose-500 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition cursor-pointer" title="Verwijderen">
                                        <i class="fa-regular fa-trash-can text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400 dark:text-slate-500 text-xs">Nog geen artikelen aangemaakt. Maak het eerste artikel aan!</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200/80 dark:border-slate-800 flex justify-end">
            {{ $articles->links() }}
        </div>
    </div>

</div>
@endsection
