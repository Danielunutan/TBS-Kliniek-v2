@extends('layouts.app')

@section('title', 'Nieuw Support Ticket Aanmaken - TBS Kliniek')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="ticketForm()">

    <!-- Breadcrumb / Back button -->
    <div class="flex items-center justify-between">
        <a href="{{ route('help.tickets.index') }}" 
           class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Terug naar Support Tickets</span>
        </a>

        <a href="{{ route('faq.index') }}" 
           class="text-xs font-bold text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 transition flex items-center gap-1.5">
            <i class="fa-solid fa-circle-question"></i>
            <span>Veelgestelde Vragen (FAQ)</span>
        </a>
    </div>

    <!-- Main Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
        
        <!-- Header -->
        <div class="p-6 sm:p-8 border-b border-slate-100 dark:border-slate-800 flex items-start justify-between gap-4 bg-gradient-to-r from-purple-50/50 via-white to-indigo-50/30 dark:from-purple-950/20 dark:via-slate-900 dark:to-indigo-950/20">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-600 text-white flex items-center justify-center text-2xl shadow-md shadow-purple-600/20 shrink-0">
                    <i class="fa-solid fa-ticket-simple"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2.5 py-0.5 rounded-full bg-purple-100 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 text-[10px] font-black uppercase tracking-wider border border-purple-200 dark:border-purple-800">
                            Helpdesk & Support
                        </span>
                        <span class="text-[11px] text-slate-400 dark:text-slate-500 font-bold">&bull; Snelle Reactie</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">Nieuw Support Ticket Openen</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Meld een bug, stel een vraag over de game of vraag de exclusieve <strong>Bende Overstap Bonus</strong> aan.
                    </p>
                </div>
            </div>
        </div>

        <!-- FORM -->
        <form action="{{ route('help.tickets.store') }}" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf

            <!-- STEP 1: CATEGORY SELECTION (CUSTOM INTERACTIVE SELECTOR) -->
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">
                    Selecteer Categorie <span class="text-rose-500">*</span>
                </label>
                
                <input type="hidden" name="category" :value="selectedCategory">

                <!-- Custom Category Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    
                    <!-- Gang Overstap Bonus Card (Special Spotlight) -->
                    <button type="button" 
                            @click="selectCategory('gang_overstap')"
                            :class="selectedCategory === 'gang_overstap' ? 'border-amber-500 ring-2 ring-amber-500/20 bg-amber-50/70 dark:bg-amber-950/30' : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/60 hover:border-amber-300 dark:hover:border-amber-700'"
                            class="p-4 rounded-2xl border text-left transition cursor-pointer relative group flex flex-col justify-between space-y-2.5 shadow-2xs">
                        <div class="flex items-center justify-between gap-2">
                            <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-500 border border-amber-500/20 flex items-center justify-center text-base shrink-0 group-hover:scale-105 transition">
                                <i class="fa-solid fa-users-between-lines"></i>
                            </div>
                            <span class="px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 text-[9px] font-black uppercase tracking-wider shadow-2xs animate-pulse">
                                🔥 €5M + 31D VIP
                            </span>
                        </div>
                        <div>
                            <h4 class="text-xs font-black text-slate-900 dark:text-white">Bende Overstap Bonus</h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-snug">
                                Stap over van een andere game met je bende voor gratis bende + €5M kluisgeld.
                            </p>
                        </div>
                        <div class="flex items-center gap-1.5 text-[10px] font-extrabold text-amber-600 dark:text-amber-400 pt-1 border-t border-amber-200/60 dark:border-amber-800/40">
                            <span>Exclusieve Clan Regeling</span> &rarr;
                        </div>
                    </button>

                    <!-- Vraag over de Game -->
                    <button type="button" 
                            @click="selectCategory('vraag')"
                            :class="selectedCategory === 'vraag' ? 'border-purple-500 ring-2 ring-purple-500/20 bg-purple-50/70 dark:bg-purple-950/30' : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/60 hover:border-purple-300 dark:hover:border-purple-700'"
                            class="p-4 rounded-2xl border text-left transition cursor-pointer relative group flex flex-col justify-between space-y-2.5 shadow-2xs">
                        <div class="w-9 h-9 rounded-xl bg-purple-50 dark:bg-purple-950/80 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-800 flex items-center justify-center text-base shrink-0 group-hover:scale-105 transition">
                            <i class="fa-solid fa-circle-question"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-black text-slate-900 dark:text-white">Vraag over de Game</h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-snug">
                                Vragen over functies, statistieken, missies, de bank of afdelingen.
                            </p>
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 pt-1 border-t border-slate-100 dark:border-slate-800">Algemene hulp</span>
                    </button>

                    <!-- Bug / Foutmelding -->
                    <button type="button" 
                            @click="selectCategory('bug')"
                            :class="selectedCategory === 'bug' ? 'border-rose-500 ring-2 ring-rose-500/20 bg-rose-50/70 dark:bg-rose-950/30' : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/60 hover:border-rose-300 dark:hover:border-rose-700'"
                            class="p-4 rounded-2xl border text-left transition cursor-pointer relative group flex flex-col justify-between space-y-2.5 shadow-2xs">
                        <div class="w-9 h-9 rounded-xl bg-rose-50 dark:bg-rose-950/80 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800 flex items-center justify-center text-base shrink-0 group-hover:scale-105 transition">
                            <i class="fa-solid fa-bug"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-black text-slate-900 dark:text-white">Bug / Foutmelding</h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-snug">
                                Meld een technisch mankement, paginafout of vastloper in het spel.
                            </p>
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 pt-1 border-t border-slate-100 dark:border-slate-800">Technisch probleem</span>
                    </button>

                    <!-- Feedback & Suggesties -->
                    <button type="button" 
                            @click="selectCategory('feedback')"
                            :class="selectedCategory === 'feedback' ? 'border-indigo-500 ring-2 ring-indigo-500/20 bg-indigo-50/70 dark:bg-indigo-950/30' : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/60 hover:border-indigo-300 dark:hover:border-indigo-700'"
                            class="p-4 rounded-2xl border text-left transition cursor-pointer relative group flex flex-col justify-between space-y-2.5 shadow-2xs">
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 flex items-center justify-center text-base shrink-0 group-hover:scale-105 transition">
                            <i class="fa-solid fa-lightbulb"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-black text-slate-900 dark:text-white">Feedback & Ideeën</h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-snug">
                                Suggesties om de gameplay, misdaden of economie te verbeteren.
                            </p>
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 pt-1 border-t border-slate-100 dark:border-slate-800">Spelverbetering</span>
                    </button>

                    <!-- VIP & Donaties -->
                    <button type="button" 
                            @click="selectCategory('vip')"
                            :class="selectedCategory === 'vip' ? 'border-amber-500 ring-2 ring-amber-500/20 bg-amber-50/70 dark:bg-amber-950/30' : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/60 hover:border-amber-300 dark:hover:border-amber-700'"
                            class="p-4 rounded-2xl border text-left transition cursor-pointer relative group flex flex-col justify-between space-y-2.5 shadow-2xs">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/80 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800 flex items-center justify-center text-base shrink-0 group-hover:scale-105 transition">
                            <i class="fa-solid fa-crown"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-black text-slate-900 dark:text-white">VIP & Donaties</h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-snug">
                                Vragen over VIP-lidmaatschap, donaties of ontbrekende VIP-tijd.
                            </p>
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 pt-1 border-t border-slate-100 dark:border-slate-800">VIP & Shop</span>
                    </button>

                    <!-- Overig -->
                    <button type="button" 
                            @click="selectCategory('overig')"
                            :class="selectedCategory === 'overig' ? 'border-slate-500 ring-2 ring-slate-500/20 bg-slate-50 dark:bg-slate-800' : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/60 hover:border-slate-400'"
                            class="p-4 rounded-2xl border text-left transition cursor-pointer relative group flex flex-col justify-between space-y-2.5 shadow-2xs">
                        <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-base shrink-0 group-hover:scale-105 transition">
                            <i class="fa-solid fa-message"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-black text-slate-900 dark:text-white">Overige Vragen</h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-snug">
                                Alle overige zaken en vragen die niet in bovenstaande vakken passen.
                            </p>
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 pt-1 border-t border-slate-100 dark:border-slate-800">Algemeen contact</span>
                    </button>

                </div>
                @error('category')
                <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- DYNAMIC SECTION A: GANG OVERSTAP BONUS FIELDS -->
            <div x-show="selectedCategory === 'gang_overstap'" x-cloak class="space-y-5 animate-in fade-in duration-200">
                
                <!-- Gang Transfer Announcement Banner -->
                <div class="rounded-2xl p-5 bg-gradient-to-r from-amber-500/15 via-purple-500/10 to-indigo-500/15 border-2 border-amber-400/40 dark:border-amber-500/30 space-y-3 relative overflow-hidden">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-400 text-slate-950 flex items-center justify-center text-lg font-black shadow-xs shrink-0">
                            <i class="fa-solid fa-crown"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white">Bende Overstap Bonus Regeling</h3>
                            <p class="text-xs text-slate-600 dark:text-slate-300">Stap met je hele clan/bende over naar TBS Kliniek en ontvang direct een gigantische startbonus!</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-2 text-xs">
                        <div class="p-3 rounded-xl bg-white/80 dark:bg-slate-900/80 border border-amber-300/60 dark:border-amber-700/60 flex items-center gap-2.5">
                            <i class="fa-solid fa-shield-halved text-amber-500 text-base"></i>
                            <div>
                                <span class="block text-[10px] font-bold text-slate-400 uppercase">Bende Oprichting</span>
                                <strong class="text-slate-900 dark:text-white font-black">100% Gratis & Direct</strong>
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-white/80 dark:bg-slate-900/80 border border-emerald-300/60 dark:border-emerald-700/60 flex items-center gap-2.5">
                            <i class="fa-solid fa-vault text-emerald-500 text-base"></i>
                            <div>
                                <span class="block text-[10px] font-bold text-slate-400 uppercase">Kluis Startkapitaal</span>
                                <strong class="text-emerald-700 dark:text-emerald-400 font-black">€ 5.000.000 Cash</strong>
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-white/80 dark:bg-slate-900/80 border border-purple-300/60 dark:border-purple-700/60 flex items-center gap-2.5">
                            <i class="fa-solid fa-star text-purple-500 text-base"></i>
                            <div>
                                <span class="block text-[10px] font-bold text-slate-400 uppercase">VIP Voor Iedereen</span>
                                <strong class="text-purple-700 dark:text-purple-300 font-black">31 Dagen Gratis VIP</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fields Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    
                    <!-- Gewenste Bendenaam (2 cols) -->
                    <div class="sm:col-span-2 space-y-1.5">
                        <label for="gang_name" class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            Gewenste Bendenaam <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-users text-xs"></i>
                            </span>
                            <input type="text" 
                                   id="gang_name" 
                                   name="gang_name" 
                                   value="{{ old('gang_name') }}" 
                                   placeholder="Bijv. Peaky Blinders, Mocro Maffia, Cartel" 
                                   class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 focus:bg-white dark:focus:bg-slate-800 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 text-xs font-bold text-slate-900 dark:text-white placeholder-slate-400 outline-none transition"
                                   :required="selectedCategory === 'gang_overstap'">
                        </div>
                        @error('gang_name')
                        <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Bende Tag / Afkorting (1 col) -->
                    <div class="space-y-1.5">
                        <label for="gang_tag" class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            Bende Tag (2-4 tekens) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-mono text-xs">
                                #
                            </span>
                            <input type="text" 
                                   id="gang_tag" 
                                   name="gang_tag" 
                                   maxlength="4" 
                                   value="{{ old('gang_tag') }}" 
                                   placeholder="PB" 
                                   class="w-full pl-8 pr-4 py-2.5 uppercase font-mono rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 focus:bg-white dark:focus:bg-slate-800 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 text-xs font-bold text-slate-900 dark:text-white placeholder-slate-400 outline-none transition"
                                   :required="selectedCategory === 'gang_overstap'">
                        </div>
                        @error('gang_tag')
                        <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <!-- Link naar Gang op andere game/website -->
                <div class="space-y-1.5">
                    <label for="external_gang_link" class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Link naar jullie Bende op de andere game/website <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-link text-xs"></i>
                        </span>
                        <input type="url" 
                               id="external_gang_link" 
                               name="external_gang_link" 
                               value="{{ old('external_gang_link') }}" 
                               placeholder="https://www.anderegame.nl/gang/profiel-link-of-pagina" 
                               class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 focus:bg-white dark:focus:bg-slate-800 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 text-xs font-medium text-slate-900 dark:text-white placeholder-slate-400 outline-none transition"
                               :required="selectedCategory === 'gang_overstap'">
                    </div>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500">
                        Plaats hier de URL naar jullie bende, clanprofiel of bewijs op Barribob, Boevenbende, OSG, etc.
                    </p>
                    @error('external_gang_link')
                    <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Overstappende Leden (Accountnamen) -->
                <div class="space-y-1.5">
                    <label for="member_names" class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Accountnamen van overstappende leden (Optioneel)
                    </label>
                    <textarea id="member_names" 
                              name="member_names" 
                              rows="3" 
                              placeholder="Vul hier de gebruikersnamen in van je vrienden die meekomen (gescheiden door komma's of op nieuwe regels). Als ze al geregistreerd zijn in TBS Kliniek ontvangen ze direct VIP!"
                              class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 focus:bg-white dark:focus:bg-slate-800 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 text-xs font-medium text-slate-900 dark:text-white placeholder-slate-400 outline-none transition resize-y">{{ old('member_names') }}</textarea>
                    @error('member_names')
                    <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Toelichting / Bewijs -->
                <div class="space-y-1.5">
                    <label for="proof_details" class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Extra Toelichting of Screenshot Links (Optioneel)
                    </label>
                    <textarea id="proof_details" 
                              name="proof_details" 
                              rows="2" 
                              placeholder="Eventuele toelichting of links naar Imgur/screenshot van jullie bendeleden..."
                              class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 focus:bg-white dark:focus:bg-slate-800 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 text-xs font-medium text-slate-900 dark:text-white placeholder-slate-400 outline-none transition resize-y">{{ old('proof_details') }}</textarea>
                    @error('proof_details')
                    <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <!-- DYNAMIC SECTION B: REGULAR TICKET FIELDS (When not gang transfer) -->
            <div x-show="selectedCategory !== 'gang_overstap'" class="space-y-5 animate-in fade-in duration-200">
                
                <!-- Subject -->
                <div class="space-y-1.5">
                    <label for="subject" class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Onderwerp <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           id="subject" 
                           name="subject" 
                           value="{{ old('subject') }}" 
                           placeholder="Bijv. Vraag over kluis kraken, of: Foutmelding bij missie 4" 
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 focus:bg-white dark:focus:bg-slate-800 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 text-xs font-bold text-slate-900 dark:text-white placeholder-slate-400 outline-none transition"
                           :required="selectedCategory !== 'gang_overstap'">
                    @error('subject')
                    <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Message -->
                <div class="space-y-1.5">
                    <label for="message" class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Bericht / Toelichting <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="message" 
                              name="message" 
                              rows="6" 
                              placeholder="Typ hier je uitgebreide vraag, bug-omschrijving of feedback. Hoe meer details en screenshots, hoe sneller we kunnen helpen!"
                              class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 focus:bg-white dark:focus:bg-slate-800 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 text-xs font-medium text-slate-900 dark:text-white placeholder-slate-400 outline-none transition resize-y"
                              :required="selectedCategory !== 'gang_overstap'">{{ old('message') }}</textarea>
                    @error('message')
                    <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <!-- CUSTOM PRIORITY SELECTOR -->
            <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">
                    Prioriteit
                </label>
                
                <input type="hidden" name="priority" :value="selectedPriority">

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                    
                    <button type="button" 
                            @click="selectedPriority = 'laag'"
                            :class="selectedPriority === 'laag' ? 'bg-slate-100 dark:bg-slate-700 border-slate-400 dark:border-slate-500 text-slate-900 dark:text-white font-black shadow-xs' : 'bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-bold'"
                            class="py-2.5 px-3 rounded-xl border text-xs flex items-center justify-center gap-2 cursor-pointer transition">
                        <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                        <span>Laag</span>
                    </button>

                    <button type="button" 
                            @click="selectedPriority = 'normaal'"
                            :class="selectedPriority === 'normaal' ? 'bg-indigo-50 dark:bg-indigo-950/70 border-indigo-500 text-indigo-700 dark:text-indigo-300 font-black shadow-xs ring-1 ring-indigo-500/30' : 'bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-bold'"
                            class="py-2.5 px-3 rounded-xl border text-xs flex items-center justify-center gap-2 cursor-pointer transition">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        <span>Normaal</span>
                    </button>

                    <button type="button" 
                            @click="selectedPriority = 'hoog'"
                            :class="selectedPriority === 'hoog' ? 'bg-amber-50 dark:bg-amber-950/70 border-amber-500 text-amber-700 dark:text-amber-300 font-black shadow-xs ring-1 ring-amber-500/30' : 'bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-bold'"
                            class="py-2.5 px-3 rounded-xl border text-xs flex items-center justify-center gap-2 cursor-pointer transition">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span>Hoog</span>
                    </button>

                    <button type="button" 
                            @click="selectedPriority = 'spoed'"
                            :class="selectedPriority === 'spoed' ? 'bg-rose-50 dark:bg-rose-950/70 border-rose-500 text-rose-700 dark:text-rose-300 font-black shadow-xs ring-1 ring-rose-500/30' : 'bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-bold'"
                            class="py-2.5 px-3 rounded-xl border text-xs flex items-center justify-center gap-2 cursor-pointer transition">
                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                        <span>Spoed</span>
                    </button>

                </div>
            </div>

            <!-- SUBMIT BAR -->
            <div class="flex items-center justify-between gap-3 pt-6 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('help.tickets.index') }}" 
                   class="px-5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition cursor-pointer">
                    Annuleren
                </a>
                
                <button type="submit" 
                        class="px-6 py-3 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-black text-xs transition shadow-md shadow-purple-600/20 cursor-pointer flex items-center gap-2">
                    <i class="fa-solid fa-paper-plane text-amber-300"></i>
                    <span x-text="selectedCategory === 'gang_overstap' ? 'Bende Overstap Bonus Aanvragen' : 'Support Ticket Verzenden'"></span>
                </button>
            </div>

        </form>

    </div>

</div>

<script>
function ticketForm() {
    return {
        selectedCategory: '{{ old('category', request('category', 'gang_overstap')) }}',
        selectedPriority: '{{ old('priority', 'normaal') }}',

        selectCategory(cat) {
            this.selectedCategory = cat;
        }
    };
}
</script>
@endsection
