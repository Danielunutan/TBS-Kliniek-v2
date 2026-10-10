@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Profile Dossier Header -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-gradient-to-r from-[#241142] via-[#3a156b] to-[#241142] p-6 text-white flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="w-20 h-20 rounded-xl bg-purple-950 border-2 border-amber-400 flex items-center justify-center text-amber-300 text-3xl font-black shadow-md shrink-0">
                    <i class="fa-solid fa-id-card"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-2xl font-black tracking-wide text-white">{{ $profileCharacter->name }}</h1>
                        <span class="px-2 py-0.5 rounded bg-amber-500 text-purple-950 font-black text-xs uppercase tracking-wider">Level {{ $profileCharacter->level }}</span>
                        @if($profileCharacter->gang)
                        <span class="px-2 py-0.5 rounded bg-purple-950 text-purple-200 font-bold text-xs border border-purple-800">
                            [{{ $profileCharacter->gang->tag }}] {{ $profileCharacter->gang->name }}
                        </span>
                        @endif
                    </div>
                    <p class="text-xs font-semibold text-purple-200 mt-1">Dossiertitel: <span class="text-amber-300">{{ $profileCharacter->title }}</span></p>
                    <p class="text-xs text-purple-300 italic mt-2 bg-purple-950/60 py-1 px-3 rounded border border-purple-800/60 inline-block">
                        <i class="fa-solid fa-quote-left text-[10px] mr-1 text-amber-400"></i>{{ $profileCharacter->status_message }}
                    </p>
                </div>
            </div>

            <!-- Profile Summary Badges -->
            <div class="flex items-center gap-3 text-xs">
                <div class="bg-purple-950/80 px-3 py-2 rounded border border-purple-800 text-center">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Respect</span>
                    <strong class="text-amber-300 text-sm">{{ $profileCharacter->respect }}</strong>
                </div>
                <div class="bg-purple-950/80 px-3 py-2 rounded border border-purple-800 text-center">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Missies</span>
                    <strong class="text-emerald-300 text-sm">{{ $profileCharacter->missions_completed }}</strong>
                </div>
                <div class="bg-purple-950/80 px-3 py-2 rounded border border-purple-800 text-center">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Diefstallen</span>
                    <strong class="text-purple-300 text-sm">{{ $profileCharacter->thefts_succeeded }}</strong>
                </div>
            </div>
        </div>

        <!-- Bio text -->
        <div class="p-6 bg-slate-50 border-t border-slate-200 text-xs">
            <h3 class="font-black uppercase text-slate-700 tracking-wider mb-1 flex items-center gap-1.5">
                <i class="fa-solid fa-file-lines text-purple-700"></i>
                <span>Medisch & Psychiatrisch Dossier (Biografie)</span>
            </h3>
            <p class="text-slate-600 leading-relaxed italic bg-white p-3 rounded border border-slate-200">
                {{ $profileCharacter->bio ?: 'Geen biografie ingevuld.' }}
            </p>
        </div>
    </div>

    @if($myCharacter->id === $profileCharacter->id)
    <!-- Edit Bio Accordion -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6" x-data="{ open: false }">
        <div class="flex items-center justify-between cursor-pointer" @click="open = !open">
            <h3 class="text-sm font-black uppercase text-slate-800 tracking-wide flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-purple-700"></i>
                <span>Eigen Dossier & Status Bijwerken</span>
            </h3>
            <button type="button" class="text-xs font-bold text-purple-900 flex items-center gap-1">
                <span x-text="open ? 'Inklappen' : 'Bewerken'"></span>
                <i class="fa-solid" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
        </div>

        <form x-show="open" x-cloak method="POST" action="{{ route('profile.update') }}" class="mt-4 space-y-4 pt-3 border-t border-slate-100">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Korte Statuszin (max 120 tekens)</label>
                <input type="text" name="status_message" value="{{ old('status_message', $profileCharacter->status_message) }}" maxlength="120" class="w-full px-3 py-2 text-xs border border-slate-300 rounded focus:ring-2 focus:ring-purple-600 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Dossier Bio (max 1000 tekens)</label>
                <textarea name="bio" rows="3" maxlength="1000" class="w-full px-3 py-2 text-xs border border-slate-300 rounded focus:ring-2 focus:ring-purple-600 outline-none">{{ old('bio', $profileCharacter->bio) }}</textarea>
            </div>
            <button type="submit" class="bg-purple-900 hover:bg-purple-800 text-white font-bold py-2 px-4 rounded text-xs shadow cursor-pointer">
                Opslaan in Dossier
            </button>
        </form>
    </div>
    @endif

    <!-- Guestbook Section -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-6">
        <div class="flex items-center justify-between border-b border-slate-200 pb-4">
            <h2 class="text-base font-black text-slate-900 uppercase tracking-wide flex items-center gap-2">
                <i class="fa-solid fa-book-open text-purple-700"></i>
                <span>Gastenboek van {{ $profileCharacter->name }}</span>
            </h2>
            <span class="text-xs font-bold text-slate-500">{{ $guestbookEntries->total() }} berichten</span>
        </div>

        <!-- Post Form -->
        <form method="POST" action="{{ route('guestbook.post', $profileCharacter->name) }}" class="space-y-3 bg-slate-50 p-4 rounded-lg border border-slate-200">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Laat een bericht achter in het dossier</label>
                <textarea name="message" rows="2" placeholder="Schrijf een respectvol of sarcastisch berichtje..." required class="w-full px-3 py-2 text-xs border border-slate-300 rounded focus:ring-2 focus:ring-purple-600 outline-none bg-white"></textarea>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-amber-500 hover:bg-amber-400 text-purple-950 font-black text-xs py-2 px-4 rounded shadow transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Bericht Plaatsen</span>
                </button>
            </div>
        </form>

        <!-- Messages List -->
        <div class="divide-y divide-slate-100">
            @forelse($guestbookEntries as $entry)
            <div class="py-4 space-y-1">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('profile.show', $entry->author->name) }}" class="font-black text-xs text-purple-950 hover:underline">
                            {{ $entry->author->name }}
                        </a>
                        <span class="text-[10px] px-1.5 py-0.2 rounded bg-purple-100 text-purple-900 font-bold">
                            Lvl {{ $entry->author->level }}
                        </span>
                    </div>
                    <span class="text-[10px] text-slate-400">{{ $entry->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-xs text-slate-700 leading-relaxed bg-white p-2.5 rounded border border-slate-100">
                    {{ $entry->message }}
                </p>
            </div>
            @empty
            <p class="text-xs text-slate-500 py-6 text-center italic">Nog geen berichten achtergelaten in dit gastenboek. Wees de eerste!</p>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="pt-2">
            {{ $guestbookEntries->links() }}
        </div>
    </div>
</div>
@endsection
