@extends('layouts.app')

@section('title', 'Nieuw Support Ticket Aanmaken - TBS Kliniek')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Breadcrumb / Back button -->
    <div class="flex items-center justify-between">
        <a href="{{ route('help.tickets.index') }}" 
           class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-slate-800 transition">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Terug naar Support Tickets</span>
        </a>

        <a href="{{ route('faq.index') }}" 
           class="text-xs font-bold text-purple-600 hover:text-purple-800 transition flex items-center gap-1.5">
            <i class="fa-solid fa-circle-question"></i>
            <span>Veelgestelde Vragen (FAQ)</span>
        </a>
    </div>

    <!-- Main Card -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Header -->
        <div class="p-5 sm:p-6 border-b border-slate-100 flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 border border-purple-200 text-purple-600 flex items-center justify-center text-xl shadow-2xs shrink-0">
                <i class="fa-solid fa-ticket-simple"></i>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Nieuw Support Ticket Openen</h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    TBS Kliniek is momenteel in <strong>Open Beta</strong>. We waarderen elke bugmelding, vraag of suggestie om de game nog beter te maken!
                </p>
            </div>
        </div>

        <!-- Tip Box -->
        <div class="mx-5 sm:mx-6 mt-5 p-4 rounded-xl bg-indigo-50/70 border border-indigo-100 flex items-start gap-3 text-xs text-indigo-900">
            <i class="fa-solid fa-lightbulb text-indigo-600 text-base mt-0.5 shrink-0"></i>
            <div class="space-y-1">
                <strong class="font-extrabold text-indigo-950">Tip voor sneller antwoord:</strong>
                <p class="text-indigo-800">
                    Kijk voor veelvoorkomende vragen over gevangenistijden, missies, gangs en VIP eerst in onze 
                    <a href="{{ route('faq.index') }}" class="underline font-bold hover:text-indigo-950">Kennisbank & FAQ</a>.
                    Beschrijf bij een bugmelding zo duidelijk mogelijk wat er gebeurde en op welke pagina.
                </p>
            </div>
        </div>

        <!-- Form -->
        <form action="{{ route('help.tickets.store') }}" method="POST" class="p-5 sm:p-6 space-y-5">
            @csrf

            <!-- Subject -->
            <div>
                <label for="subject" class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-1.5">
                    Onderwerp <span class="text-rose-500">*</span>
                </label>
                <input type="text" 
                       id="subject" 
                       name="subject" 
                       value="{{ old('subject') }}" 
                       placeholder="Bijv. Foutmelding bij missie 4, of: Suggestie voor gang oorlogen" 
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 text-xs font-medium text-slate-900 placeholder-slate-400 outline-none transition"
                       required>
                @error('subject')
                <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Category & Priority Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <!-- Category -->
                <div>
                    <label for="category" class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-1.5">
                        Categorie <span class="text-rose-500">*</span>
                    </label>
                    <select id="category" 
                            name="category" 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 text-xs font-bold text-slate-800 bg-white outline-none transition"
                            required>
                        <option value="bug" {{ old('category') === 'bug' ? 'selected' : '' }}> Bug / Foutmelding</option>
                        <option value="vraag" {{ old('category', 'vraag') === 'vraag' ? 'selected' : '' }}> Vraag over de Game</option>
                        <option value="feedback" {{ old('category') === 'feedback' ? 'selected' : '' }}> Feedback & Suggestie</option>
                        <option value="vip" {{ old('category') === 'vip' ? 'selected' : '' }}>⭐ VIP & Donaties</option>
                        <option value="overig" {{ old('category') === 'overig' ? 'selected' : '' }}> Overig</option>
                    </select>
                    @error('category')
                    <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Priority -->
                <div>
                    <label for="priority" class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-1.5">
                        Prioriteit <span class="text-rose-500">*</span>
                    </label>
                    <select id="priority" 
                            name="priority" 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 text-xs font-bold text-slate-800 bg-white outline-none transition"
                            required>
                        <option value="laag" {{ old('priority') === 'laag' ? 'selected' : '' }}>Laag (Geen haast)</option>
                        <option value="normaal" {{ old('priority', 'normaal') === 'normaal' ? 'selected' : '' }}>Normaal (Standaard)</option>
                        <option value="hoog" {{ old('priority') === 'hoog' ? 'selected' : '' }}>Hoog (Belangrijk probleem)</option>
                        <option value="spoed" {{ old('priority') === 'spoed' ? 'selected' : '' }}> Spoed (Game blocker)</option>
                    </select>
                    @error('priority')
                    <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <!-- Message -->
            <div>
                <label for="message" class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-1.5">
                    Bericht / Beschrijving <span class="text-rose-500">*</span>
                </label>
                <textarea id="message" 
                          name="message" 
                          rows="6" 
                          placeholder="Typ hier je uitgebreide vraag, bug-omschrijving of feedback. Hoe meer details, hoe sneller we kunnen helpen!"
                          class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 text-xs font-medium text-slate-900 placeholder-slate-400 outline-none transition resize-y"
                          required>{{ old('message') }}</textarea>
                @error('message')
                <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('help.tickets.index') }}" 
                   class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                    Annuleren
                </a>
                <button type="submit" 
                        class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-extrabold text-xs transition shadow-xs cursor-pointer flex items-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Ticket Versturen</span>
                </button>
            </div>

        </form>

    </div>

</div>
@endsection
