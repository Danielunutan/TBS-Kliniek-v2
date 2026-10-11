@extends('admin.layout')

@section('admin-content')
<div class="space-y-6 max-w-4xl">

    <!-- Header Toolbar -->
    <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-xs flex items-center justify-between">
        <div>
            <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-plus-circle text-purple-600"></i>
                <span>Nieuw Artikel Schrijven</span>
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Stel een nieuw bericht op voor de TBS Kliniek patiënten.
            </p>
        </div>

        <a href="{{ route('admin.news.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> Terug
        </a>
    </div>

    <!-- Create Form -->
    <form action="{{ route('admin.news.store') }}" method="POST" class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs space-y-6">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
            <!-- Emoji Picker -->
            <div class="sm:col-span-3">
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                    Emoji / Icoon
                </label>
                <input type="text" name="emoji" value="{{ old('emoji', '💖') }}" required maxlength="20" placeholder="bijv. 💖, 👻, 📢"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-base text-center font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-purple-200 focus:border-purple-600 outline-none transition">
            </div>

            <!-- Title -->
            <div class="sm:col-span-9">
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                    Titel van het artikel
                </label>
                <input type="text" name="title" value="{{ old('title') }}" required maxlength="150" placeholder="bijv. Hyves Update of Nachtelijke Isoleercel Regels"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-xs sm:text-sm font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-purple-200 focus:border-purple-600 outline-none transition">
            </div>
        </div>

        <!-- Optional Banner / Cover Image URL -->
        <div>
            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                <span>Header Afbeelding URL (Optioneel)</span>
                <span class="text-[10px] font-normal text-slate-400">Directe link naar .jpg of .png</span>
            </label>
            <input type="url" name="image_url" value="{{ old('image_url') }}" placeholder="https://voorbeeld.nl/plaatje.jpg"
                   class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-xs font-mono font-medium text-slate-900 focus:bg-white focus:ring-2 focus:ring-purple-200 focus:border-purple-600 outline-none transition">
        </div>

        <!-- Rich Teksteditor Partial -->
        <div>
            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">
                Inhoud & Tekst van het artikel
            </label>
            @include('admin.news._editor', ['content' => old('content')])
        </div>

        <!-- Options Box -->
        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl grid grid-cols-1 sm:grid-cols-3 gap-4">
            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" name="comments_enabled" value="1" {{ old('comments_enabled', true) ? 'checked' : '' }} class="rounded text-purple-600 focus:ring-purple-500 w-4 h-4">
                <div>
                    <span class="text-xs font-black text-slate-900 block">Reacties Toestaan</span>
                    <span class="text-[10px] text-slate-500 block leading-tight">Leden kunnen reageren onder dit artikel</span>
                </div>
            </label>

            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" name="is_pinned" value="1" {{ old('is_pinned', false) ? 'checked' : '' }} class="rounded text-purple-600 focus:ring-purple-500 w-4 h-4">
                <div>
                    <span class="text-xs font-black text-slate-900 block">Vastpinnen (Belangrijk)</span>
                    <span class="text-[10px] text-slate-500 block leading-tight">Toon bovenaan met uitgelichte badge</span>
                </div>
            </label>

            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" name="is_published" value="1" {{ old('is_published', true) ? 'checked' : '' }} class="rounded text-purple-600 focus:ring-purple-500 w-4 h-4">
                <div>
                    <span class="text-xs font-black text-slate-900 block">Direct Publiceren</span>
                    <span class="text-[10px] text-slate-500 block leading-tight">Direct zichtbaar op de live website</span>
                </div>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('admin.news.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                Annuleren
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-black text-xs uppercase tracking-wider transition shadow-sm flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-paper-plane text-amber-300"></i> Artikel Publiceren
            </button>
        </div>
    </form>

</div>
@endsection
