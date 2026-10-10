@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- SPELERPROFIEL CARD (Exact match to middle-left screen in mockup) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-6">
        <!-- Top Profile Banner -->
        <div class="bg-gradient-to-r from-[#210d3d] via-[#431477] to-[#210d3d] rounded-2xl p-6 text-white flex flex-col md:flex-row items-center gap-6 shadow-md relative overflow-hidden">
            <!-- Avatar -->
            <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden border-2 border-amber-400 shadow-md shrink-0 bg-purple-950">
                <img src="{{ asset('assets/characters/henkie.jpg') }}" alt="{{ $profileCharacter->name }}" class="w-full h-full object-cover">
            </div>

            <!-- Name, Level & Quote -->
            <div class="flex-1 space-y-2 text-center md:text-left">
                <div class="flex items-center justify-center md:justify-start gap-3 flex-wrap">
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                        {{ $profileCharacter->name }}
                    </h1>
                    <span class="px-2.5 py-0.5 rounded-full bg-amber-500 text-purple-950 font-black text-xs uppercase tracking-wider">
                        Level {{ $profileCharacter->level }}
                    </span>
                </div>

                <p class="text-xs text-purple-200 italic font-medium">
                    "{{ $profileCharacter->status_message }}"
                </p>

                <!-- XP Bar -->
                <div class="max-w-md pt-1">
                    <div class="flex justify-between text-[11px] font-bold text-purple-300 mb-1">
                        <span>Voortgang</span>
                        <span>{{ number_format($profileCharacter->xp, 0, ',', '.') }} / {{ number_format($nextLevelXp, 0, ',', '.') }} XP</span>
                    </div>
                    <div class="w-full bg-purple-950/80 rounded-full h-2 overflow-hidden border border-purple-800">
                        <div class="bg-gradient-to-r from-purple-400 to-amber-400 h-full rounded-full" style="width: {{ $xpPercentage }}%"></div>
                    </div>
                </div>

                <!-- Badges Row matching mockup -->
                <div class="flex items-center justify-center md:justify-start gap-2 pt-2 flex-wrap text-[10px] font-bold">
                    <span class="px-2.5 py-1 rounded-lg bg-purple-900/80 text-amber-300 border border-purple-700">
                        <i class="fa-solid fa-burger mr-1"></i> Kantinebende
                    </span>
                    <span class="px-2.5 py-1 rounded-lg bg-purple-900/80 text-purple-200 border border-purple-700">
                        <i class="fa-solid fa-crown mr-1"></i> Kantinelegende
                    </span>
                </div>
            </div>
        </div>

        <!-- 3 Currency/Respect Badges matching mockup -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-purple-100 text-purple-800 flex items-center justify-center text-base font-black shrink-0">
                    <i class="fa-solid fa-shield"></i>
                </span>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block leading-none">Respect</span>
                    <strong class="text-base font-black text-slate-900">{{ number_format($profileCharacter->respect, 0, ',', '.') }}</strong>
                </div>
            </div>

            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-base font-black shrink-0">
                    <i class="fa-solid fa-coins"></i>
                </span>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block leading-none">Geld (Contant)</span>
                    <strong class="text-base font-black text-slate-900">{{ number_format($profileCharacter->cash, 0, ',', '.') }} KC</strong>
                </div>
            </div>

            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-base font-black shrink-0">
                    <i class="fa-solid fa-building-columns"></i>
                </span>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block leading-none">Bank</span>
                    <strong class="text-base font-black text-slate-900">{{ number_format($profileCharacter->bank, 0, ',', '.') }} KC</strong>
                </div>
            </div>
        </div>

        <!-- Statistieken & Bio Row matching mockup -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
            <!-- Statistieken -->
            <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100 space-y-3">
                <h3 class="text-xs font-black uppercase text-slate-800 tracking-wider">
                    Statistieken
                </h3>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between items-center text-slate-600">
                        <span><i class="fa-solid fa-hand-fist text-purple-700 mr-2"></i>Gevechten</span>
                        <strong class="text-slate-900">{{ $profileCharacter->fights_won + $profileCharacter->fights_lost }} ({{ $profileCharacter->fights_won }}/{{ $profileCharacter->fights_lost }})</strong>
                    </div>
                    <div class="flex justify-between items-center text-slate-600">
                        <span><i class="fa-solid fa-mask text-amber-600 mr-2"></i>Diefstallen</span>
                        <strong class="text-slate-900">{{ $profileCharacter->thefts_succeeded + $profileCharacter->thefts_failed }} ({{ $profileCharacter->thefts_succeeded }}/{{ $profileCharacter->thefts_failed }})</strong>
                    </div>
                    <div class="flex justify-between items-center text-slate-600">
                        <span><i class="fa-solid fa-bullseye text-emerald-600 mr-2"></i>Missies</span>
                        <strong class="text-slate-900">{{ $profileCharacter->missions_completed }} ({{ $profileCharacter->missions_completed }}/6)</strong>
                    </div>
                    <div class="flex justify-between items-center text-slate-600">
                        <span><i class="fa-solid fa-lock text-rose-600 mr-2"></i>Gevangenisstraf</span>
                        <strong class="text-slate-900">{{ $profileCharacter->prison_sentences }} (45 min)</strong>
                    </div>
                </div>
            </div>

            <!-- Bio -->
            <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100 space-y-3">
                <h3 class="text-xs font-black uppercase text-slate-800 tracking-wider">
                    Bio
                </h3>
                <p class="text-xs text-slate-700 leading-relaxed italic bg-white p-3.5 rounded-xl border border-slate-100 min-h-[90px]">
                    {{ $profileCharacter->bio ?: 'Henkie de Frikandelkoning. Altijd in voor een goed broodje en een stevige deal. De kantine van de kliniek is mijn speeltuin.' }}
                </p>
            </div>
        </div>
    </div>

    <!-- GASTENBOEK CARD (Exact match to bottom-center-right screen in mockup) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h2 class="text-base font-black text-slate-900 uppercase tracking-tight">
                Gastenboek
            </h2>
            <span class="text-xs font-bold text-slate-400">{{ $guestbookEntries->total() }} berichten</span>
        </div>

        <!-- Entries List -->
        <div class="space-y-4">
            @forelse($guestbookEntries as $entry)
            <div class="flex items-start gap-3.5 text-xs">
                <div class="w-9 h-9 rounded-full bg-purple-100 text-purple-900 flex items-center justify-center font-black shrink-0 text-xs shadow-2xs">
                    {{ substr($entry->author->name, 0, 2) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('profile.show', $entry->author->name) }}" class="font-black text-purple-950 hover:underline text-xs">
                            {{ $entry->author->name }}
                        </a>
                        <span class="text-[10px] text-slate-400 font-medium">{{ $entry->created_at->format('d-m-Y H:i') }}</span>
                    </div>
                    <p class="text-slate-700 bg-slate-50/90 p-3 rounded-xl border border-slate-100 mt-1 leading-relaxed">
                        {{ $entry->message }}
                    </p>
                </div>
            </div>
            @empty
            <p class="text-xs text-slate-400 italic text-center py-4">Nog geen berichten achtergelaten.</p>
            @endforelse
        </div>

        <!-- Pagination -->
        <div>
            {{ $guestbookEntries->links() }}
        </div>

        <!-- Guestbook Input Bar matching mockup -->
        <div class="pt-4 border-t border-slate-100">
            <form method="POST" action="{{ route('guestbook.post', $profileCharacter->name) }}" class="flex items-center gap-2">
                @csrf
                <input type="text" name="message" placeholder="Plaats een bericht..." required class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs outline-none focus:bg-white focus:ring-2 focus:ring-purple-600 transition">
                <button type="submit" class="bg-[#6d28d9] hover:bg-[#5b21b6] text-white w-10 h-10 rounded-xl flex items-center justify-center text-xs shadow-xs transition cursor-pointer shrink-0">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
