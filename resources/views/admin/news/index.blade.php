@extends('admin.layout')

@section('admin-content')
<div class="space-y-6">

    <!-- Header Toolbar -->
    <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-newspaper text-purple-600"></i>
                <span>Nieuws Artikelen Beheer</span>
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Plaats officiële updates, beheer reacties, voeg afbeeldingen en links toe voor de patiënten.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.news.create') }}" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-black transition shadow-xs flex items-center gap-2">
                <i class="fa-solid fa-plus text-amber-300"></i> Nieuw Artikel Aanmaken
            </a>
            <a href="{{ route('news.index') }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-bold transition border border-slate-200 flex items-center gap-1.5">
                <i class="fa-solid fa-external-link text-slate-400"></i> Openbare Feed
            </a>
        </div>
    </div>

    <!-- Articles Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-200">
                        <th class="py-3 px-4">ARTIKEL</th>
                        <th class="py-3 px-3">AUTEUR</th>
                        <th class="py-3 px-3 text-center">REACTIES STATUS</th>
                        <th class="py-3 px-3 text-right">WEERGAVEN</th>
                        <th class="py-3 px-3 text-center">PINNED</th>
                        <th class="py-3 px-3">DATUM</th>
                        <th class="py-3 px-4 text-right">ACTIE</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($articles as $art)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl shrink-0">{{ $art->emoji }}</span>
                                <div class="min-w-0">
                                    <a href="{{ route('news.show', $art->slug) }}" target="_blank" class="font-extrabold text-slate-900 text-xs hover:text-purple-600 transition block truncate">
                                        {{ $art->title }}
                                    </a>
                                    <span class="text-[10px] text-slate-400 font-mono">/nieuws/{{ $art->slug }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-3 text-slate-700 font-bold">
                            {{ $art->author?->name ?? 'Directie' }}
                        </td>
                        <td class="py-3.5 px-3 text-center">
                            <form action="{{ route('admin.news.toggle-comments', $art) }}" method="POST" class="inline-block">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-extrabold transition cursor-pointer border {{ $art->comments_enabled ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100' }}" title="Klik om reacties te openen of sluiten">
                                    @if($art->comments_enabled)
                                        <i class="fa-solid fa-comments text-emerald-600 mr-1"></i> Open ({{ $art->comments_count }})
                                    @else
                                        <i class="fa-solid fa-lock text-rose-600 mr-1"></i> Gesloten ({{ $art->comments_count }})
                                    @endif
                                </button>
                            </form>
                        </td>
                        <td class="py-3.5 px-3 text-right font-mono font-bold text-slate-700">
                            {{ number_format($art->views_count, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-3 text-center">
                            @if($art->is_pinned)
                                <span class="bg-amber-100 text-amber-800 text-[10px] font-black px-2 py-0.5 rounded-full border border-amber-200 inline-flex items-center gap-1">
                                    <i class="fa-solid fa-thumbtack text-amber-600"></i> Ja
                                </span>
                            @else
                                <span class="text-slate-400 text-[10px]">Nee</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-3 text-slate-500 text-[11px]">
                            {{ $art->created_at->format('d-m-Y H:i') }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('news.show', $art->slug) }}" target="_blank" class="p-1.5 rounded-lg text-slate-400 hover:text-purple-600 hover:bg-slate-100 transition" title="Bekijk artikel">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </a>
                                <a href="{{ route('admin.news.edit', $art) }}" class="p-1.5 rounded-lg text-purple-600 hover:text-purple-800 hover:bg-purple-50 transition font-bold" title="Bewerken">
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
                                    <button type="submit" class="p-1.5 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 transition cursor-pointer" title="Verwijderen">
                                        <i class="fa-regular fa-trash-can text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400 text-xs">Nog geen artikelen aangemaakt. Maak het eerste artikel aan!</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 flex justify-end">
            {{ $articles->links() }}
        </div>
    </div>

</div>
@endsection
