@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    activeCategory: 'beginners',
    openQuestion: null,
    searchQuery: @js($search ?? ''),
    showSimulator: false,
    allOpen: false,
    calcStrength: 25,
    calcOppDefense: 20,
    activeRoadmapStep: 1,

    get winChance() {
        const ratio = this.calcStrength / Math.max(1, (this.calcStrength + this.calcOppDefense));
        return Math.min(95, Math.max(10, Math.round(ratio * 100)));
    },

    selectCategory(catKey) {
        this.activeCategory = catKey;
        this.openQuestion = null;
        this.allOpen = false;
        if (this.searchQuery) {
            this.searchQuery = '';
        }
    },

    toggleQuestion(key) {
        this.openQuestion = this.openQuestion === key ? null : key;
    },

    toggleAllQuestions() {
        this.allOpen = !this.allOpen;
        if (!this.allOpen) {
            this.openQuestion = null;
        }
    },

    isQuestionOpen(key) {
        return this.allOpen || this.openQuestion === key;
    },

    matchesSearch(qText, aText) {
        if (!this.searchQuery) return true;
        const q = this.searchQuery.toLowerCase().trim();
        return qText.toLowerCase().includes(q) || aText.toLowerCase().includes(q);
    }
}">

    <!-- Top Hero Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-purple-900 via-indigo-950 to-slate-900 p-6 sm:p-8 text-white shadow-xl border border-purple-500/30">
        <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-1.5 bg-purple-500/20 text-purple-200 text-xs font-bold px-3 py-1 rounded-full border border-purple-400/30 mb-2.5 backdrop-blur-xs">
                    <i class="fa-solid fa-book-bookmark text-amber-300"></i> Interactief Handboek &amp; Kennisbank
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white font-['Outfit']">
                    Kliniek Kennisbank & FAQ
                </h1>
                <p class="text-xs sm:text-sm text-purple-200/90 mt-1 max-w-xl leading-relaxed">
                    Alles van start tot einde beginner-friendly uitgelegd: geld sparen, missies, training, gevechten, de isoleercel, smokkel, heists en bendes. Kies links een thema of zoek direct.
                </p>
                <div class="mt-3 flex flex-wrap items-center gap-2 text-[11px] text-purple-300 font-bold">
                    <span class="inline-flex items-center gap-1 bg-white/10 px-2.5 py-0.5 rounded-full border border-white/10">
                        <i class="fa-solid fa-layer-group text-amber-300"></i> {{ count($categories) }} Categorieën
                    </span>
                    <span class="inline-flex items-center gap-1 bg-white/10 px-2.5 py-0.5 rounded-full border border-white/10">
                        <i class="fa-solid fa-circle-check text-emerald-400"></i> 100% Compleet Handboek
                    </span>
                    <span class="inline-flex items-center gap-1 bg-white/10 px-2.5 py-0.5 rounded-full border border-white/10">
                        <i class="fa-solid fa-graduation-cap text-cyan-300"></i> Beginnersgids Inbegrepen
                    </span>
                </div>
            </div>
            
            <div class="w-full md:w-80 shrink-0">
                <div class="relative">
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="Typ een zoekwoord (bijv. bank, coma, heists)..." 
                           class="w-full bg-white/10 dark:bg-black/40 border border-white/20 dark:border-white/10 rounded-xl px-4 py-3 text-xs text-white placeholder-purple-200/60 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:bg-white/20 transition backdrop-blur-md">
                    <button type="button" class="absolute right-3.5 top-3 text-purple-300">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </button>
                </div>
                <div class="flex items-center justify-between mt-2 text-[10px] text-purple-300">
                    <span x-show="!searchQuery">Filter per categorie of typ om live te zoeken</span>
                    <span x-show="searchQuery" class="text-amber-300 font-bold">Zoekmodus actief</span>
                    <button type="button" x-show="searchQuery" @click="searchQuery = ''" class="hover:underline text-amber-300 cursor-pointer">
                        Wis filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Onboarding Tutorial Quick Trigger Banner -->
    <div class="rounded-2xl bg-gradient-to-r from-purple-500/10 via-indigo-500/10 to-transparent dark:from-purple-900/30 dark:via-indigo-900/20 dark:to-transparent border border-purple-300 dark:border-purple-800/80 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-purple-600 text-white flex items-center justify-center text-lg font-black shrink-0 shadow-md shadow-purple-600/30">
                <i class="fa-solid fa-compass"></i>
            </div>
            <div>
                <h4 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white">
                    TBS Intake Rondleiding &amp; Uitleg (+ €1.000 Bonus)
                </h4>
                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Nieuw in de inrichting? Volg de interactieve 5-stappen rondleiding en leer alle basissystemen direct kennen.
                </p>
            </div>
        </div>
        <button type="button" 
                @click="window.dispatchEvent(new CustomEvent('tbs-start-intake-tour'))"
                class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white font-black text-xs transition-all duration-150 shadow-xs shrink-0 flex items-center justify-center gap-2 cursor-pointer">
            <i class="fa-solid fa-play text-[10px]"></i>
            <span>Start Rondleiding Nu</span>
        </button>
    </div>

    <!-- BEGINNER ROADMAP: VAN START TOT EINDE (INTERACTIVE STEPPER) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-purple-600 dark:text-purple-400">Chronologische Beginnersgids</span>
                <h2 class="text-sm sm:text-base font-black text-slate-900 dark:text-white font-['Outfit'] flex items-center gap-2">
                    <i class="fa-solid fa-route text-purple-500"></i>
                    <span>Van Binnenkomst tot Kliniekbaas: Het Complete Stappenplan</span>
                </h2>
            </div>
            <span class="text-xs text-slate-400 font-bold">Stap <span x-text="activeRoadmapStep"></span> van 6</span>
        </div>

        <!-- 6 Step Stepper Tabs -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2">
            <button type="button" @click="activeRoadmapStep = 1"
                    :class="activeRoadmapStep === 1 ? 'border-purple-600 bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 font-black' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                    class="p-2.5 rounded-xl border text-left transition cursor-pointer flex flex-col gap-1">
                <span class="text-[10px] font-extrabold uppercase opacity-75">Stap 1</span>
                <span class="text-xs font-black truncate">Dag 1: Start</span>
            </button>
            <button type="button" @click="activeRoadmapStep = 2"
                    :class="activeRoadmapStep === 2 ? 'border-purple-600 bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 font-black' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                    class="p-2.5 rounded-xl border text-left transition cursor-pointer flex flex-col gap-1">
                <span class="text-[10px] font-extrabold uppercase opacity-75">Stap 2</span>
                <span class="text-xs font-black truncate">Veilig Werken</span>
            </button>
            <button type="button" @click="activeRoadmapStep = 3"
                    :class="activeRoadmapStep === 3 ? 'border-purple-600 bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 font-black' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                    class="p-2.5 rounded-xl border text-left transition cursor-pointer flex flex-col gap-1">
                <span class="text-[10px] font-extrabold uppercase opacity-75">Stap 3</span>
                <span class="text-xs font-black truncate">Fitness &amp; Stats</span>
            </button>
            <button type="button" @click="activeRoadmapStep = 4"
                    :class="activeRoadmapStep === 4 ? 'border-purple-600 bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 font-black' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                    class="p-2.5 rounded-xl border text-left transition cursor-pointer flex flex-col gap-1">
                <span class="text-[10px] font-extrabold uppercase opacity-75">Stap 4</span>
                <span class="text-xs font-black truncate">Misdaad &amp; Maskers</span>
            </button>
            <button type="button" @click="activeRoadmapStep = 5"
                    :class="activeRoadmapStep === 5 ? 'border-purple-600 bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 font-black' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                    class="p-2.5 rounded-xl border text-left transition cursor-pointer flex flex-col gap-1">
                <span class="text-[10px] font-extrabold uppercase opacity-75">Stap 5</span>
                <span class="text-xs font-black truncate">Vechten &amp; Reizen</span>
            </button>
            <button type="button" @click="activeRoadmapStep = 6"
                    :class="activeRoadmapStep === 6 ? 'border-purple-600 bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 font-black' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                    class="p-2.5 rounded-xl border text-left transition cursor-pointer flex flex-col gap-1">
                <span class="text-[10px] font-extrabold uppercase opacity-75">Stap 6</span>
                <span class="text-xs font-black truncate">Bendes &amp; Heists</span>
            </button>
        </div>

        <!-- Step 1 Content -->
        <div x-show="activeRoadmapStep === 1" class="p-4 sm:p-5 rounded-xl bg-purple-50/50 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-900 space-y-3">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-purple-600 text-white flex items-center justify-center font-black text-sm shrink-0">1</span>
                <div>
                    <h4 class="text-sm font-black text-slate-900 dark:text-white">Binnenkomst &amp; Veiligheid (Eerste 10 Minuten)</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Voorkom direct dat je geld verliest of gewond raakt.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs text-slate-700 dark:text-slate-300">
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-purple-600 dark:text-purple-400 block font-black">🏥 1. Gratis Genezen</strong>
                    <p>Ga direct naar de Ziekenboeg. Zuster Brenda lapt je direct 100% gratis op naar volle Gezondheid (HP).</p>
                </div>
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-purple-600 dark:text-purple-400 block font-black">🎁 2. Gratis Draai</strong>
                    <p>Draai 1x gratis aan het Rad van Fortuin voor een mooie startersbonus aan contant geld of energie.</p>
                </div>
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-rose-600 dark:text-rose-400 block font-black">🏦 3. ALLES OP DE BANK</strong>
                    <p>Stort AL je contante geld op De Bank! Contant zakgeld kan anders worden geroofd via de Respectlijst.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 pt-1">
                <a href="{{ route('infirmary.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Naar Ziekenboeg</a>
                <a href="{{ route('daily.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Rad van Fortuin</a>
                <a href="{{ route('bank.index') }}" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-[11px] transition">Naar De Bank</a>
            </div>
        </div>

        <!-- Step 2 Content -->
        <div x-show="activeRoadmapStep === 2" class="p-4 sm:p-5 rounded-xl bg-purple-50/50 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-900 space-y-3">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-purple-600 text-white flex items-center justify-center font-black text-sm shrink-0">2</span>
                <div>
                    <h4 class="text-sm font-black text-slate-900 dark:text-white">Risicoloos Geld &amp; XP Verdienen (Level 1 t/m 3)</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Bouw je eerste kapitaal op zónder het risico om in de isoleercel te belanden.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs text-slate-700 dark:text-slate-300">
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-purple-600 dark:text-purple-400 block font-black">🔨 Dagbesteding bij Henk (0% Pakkans!)</strong>
                    <p>In de werkplaats werk je 100% legaal. Je verdient gegarandeerd eerlijk dagloon, bouwt Discipline op en vindt af en toe kluiscodes!</p>
                </div>
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-purple-600 dark:text-purple-400 block font-black">📜 Missies &amp; Opdrachten</strong>
                    <p>Voltooi beginnersmissies onder 'Voortgang > Missies'. Je krijgt royale contante beloningen en schiet vliegensvlug in levels omhoog.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 pt-1">
                <a href="{{ route('daycare.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Naar Dagbesteding</a>
                <a href="{{ route('missions.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Mijn Missies</a>
            </div>
        </div>

        <!-- Step 3 Content -->
        <div x-show="activeRoadmapStep === 3" class="p-4 sm:p-5 rounded-xl bg-purple-50/50 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-900 space-y-3">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-purple-600 text-white flex items-center justify-center font-black text-sm shrink-0">3</span>
                <div>
                    <h4 class="text-sm font-black text-slate-900 dark:text-white">Fitnessruimte &amp; Karakter Uitrusten (Level 3 t/m 6)</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Gebruik je energie slim om je vechtstatistieken op te bouwen.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs text-slate-700 dark:text-slate-300">
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-rose-600 dark:text-rose-400 block font-black">🥊 Kracht (Push-ups)</strong>
                    <p>Bepaalt hoeveel schade jij toebrengt. Richt op minstens 25 Kracht voor je eerste gevechten.</p>
                </div>
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-indigo-600 dark:text-indigo-400 block font-black">🛡️ Verdediging (Bankdrukken)</strong>
                    <p>Bepaalt hoeveel schade jij incasseert. Een hoge verdediging schrikt potentiële aanvallers af.</p>
                </div>
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-amber-600 dark:text-amber-400 block font-black">🏃 Snelheid (Loopband)</strong>
                    <p>Verhoogt je ontwijkkans in gevechten en helpt je sneller uit de isoleercel te ontsnappen.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 pt-1">
                <a href="{{ route('training.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Direct Trainen</a>
                <a href="{{ route('shop.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Winkel (Wapens &amp; EHBO)</a>
            </div>
        </div>

        <!-- Step 4 Content -->
        <div x-show="activeRoadmapStep === 4" class="p-4 sm:p-5 rounded-xl bg-purple-50/50 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-900 space-y-3">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-purple-600 text-white flex items-center justify-center font-black text-sm shrink-0">4</span>
                <div>
                    <h4 class="text-sm font-black text-slate-900 dark:text-white">Illegale Zaakjes, Zakkenrollen &amp; Maskers (Level 6 t/m 8)</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Verdien grote cashbedragen en bemachtig bivakmutsen tegen herkenning.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs text-slate-700 dark:text-slate-300">
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-purple-600 dark:text-purple-400 block font-black">🥷 Zakkenrollen voor Maskers</strong>
                    <p>Besluip zaalwachten of patiënten. Bivakmutsen/maskers die je steelt <strong>halveren automatisch je pakkans</strong> bij alle toekomstige misdrijven!</p>
                </div>
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-purple-600 dark:text-purple-400 block font-black">🚬 Hosselen &amp; Koffers Kraken</strong>
                    <p>Rol shag op de gang voor snelle cash en onderschep smokkelkoffers op het perron. Breek ze open met lockpicks of een breekijzer.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 pt-1">
                <a href="{{ route('pickpocket.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Zakkenrollen</a>
                <a href="{{ route('hustle.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Hosselen</a>
                <a href="{{ route('packages.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Koffers Onderscheppen</a>
            </div>
        </div>

        <!-- Step 5 Content -->
        <div x-show="activeRoadmapStep === 5" class="p-4 sm:p-5 rounded-xl bg-purple-50/50 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-900 space-y-3">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-purple-600 text-white flex items-center justify-center font-black text-sm shrink-0">5</span>
                <div>
                    <h4 class="text-sm font-black text-slate-900 dark:text-white">Luchtplaats, Reizen &amp; 3 Uur Verlof (Level 8 t/m 12)</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Verover reputatie, reis tussen paviljoens en ga buiten de poorten.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs text-slate-700 dark:text-slate-300">
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-purple-600 dark:text-purple-400 block font-black">🥊 Respectlijst Duels</strong>
                    <p>Vecht in de 5-seconden Showdown arena. Bij winst steel je respect én tot 15% contant geld van je slachtoffer!</p>
                </div>
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-purple-600 dark:text-purple-400 block font-black">🗺️ Reizen (+35 XP!)</strong>
                    <p>Reis via 'Verplaatsen' tussen Paviljoens en Het Kookhuis. Elke succesvolle verplaatsing levert direct +35 XP op.</p>
                </div>
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-purple-600 dark:text-purple-400 block font-black">🧠 3 Uur Verlof (00:00)</strong>
                    <p>Scoor &ge;60% bij het dagelijkse Behandelaarsgesprek om 3 uur Begeleid Verlof buiten de kliniek te verdienen met topmissies!</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 pt-1">
                <a href="{{ route('fight.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Naar Luchtplaats</a>
                <a href="{{ route('travel.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Verplaatsen (Reizen)</a>
                <a href="{{ route('treatment.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Behandelaarsgesprek</a>
            </div>
        </div>

        <!-- Step 6 Content -->
        <div x-show="activeRoadmapStep === 6" class="p-4 sm:p-5 rounded-xl bg-purple-50/50 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-900 space-y-3">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-purple-600 text-white flex items-center justify-center font-black text-sm shrink-0">6</span>
                <div>
                    <h4 class="text-sm font-black text-slate-900 dark:text-white">Bendes, Grote Heists &amp; Heerschappij (Level 12+)</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Sluit je aan bij het syndicaat, koop snelle wagens en domineer de 24 Uur Battle.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs text-slate-700 dark:text-slate-300">
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-purple-600 dark:text-purple-400 block font-black">👥 Bende &amp; Uurloon</strong>
                    <p>Richt een gang op of claim de €5.000.000 Overstap Bonus! Ontvang elk uur automatisch passief bende-uurloon uit vastgoed.</p>
                </div>
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-purple-600 dark:text-purple-400 block font-black">🏎️ Smokkel Garage &amp; Heists</strong>
                    <p>Koop een snelle vluchtwagen (GTI, M5, G63) en pleeg Grote Heists op juweliers, casino's en de Centrale Bank.</p>
                </div>
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-purple-600 dark:text-purple-400 block font-black">🎯 Liquidatie 2.0 &amp; Battle</strong>
                    <p>Schiet met kogels op rivalen op dezelfde afdeling, draag een Backfire-vest en win elke nacht gratis Sterren ⭐ in de 24u Battle!</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 pt-1">
                <a href="{{ route('gangs.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Mijn Bende</a>
                <a href="{{ route('garage.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Smokkel Garage</a>
                <a href="{{ route('heists.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Grote Heists</a>
                <a href="{{ route('battle.index') }}" class="px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-[11px] transition">24 Uur Battle</a>
            </div>
        </div>
    </div>

    <!-- 1. "IK WIL..." SNELLE PROBLEEMOPLOSSER (6 Directe Kaarten) -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2">
                <i class="fa-solid fa-bolt text-amber-500"></i>
                <span>Snelle Probleemoplosser voor Gevangenen</span>
            </h2>
            <span class="text-[11px] text-slate-400">Klik voor de directe oplossing</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3" x-data="{ activeSolver: null }">
            
            <!-- Solver 1: Energie leeg -->
            <div @click="activeSolver = activeSolver === 'energie' ? null : 'energie'"
                 class="bg-white dark:bg-slate-900 rounded-2xl p-4 border transition-all duration-200 cursor-pointer shadow-xs hover:border-purple-400 dark:hover:border-purple-600"
                 :class="activeSolver === 'energie' ? 'border-purple-500 ring-2 ring-purple-500/20 bg-purple-50/20 dark:bg-purple-950/20' : 'border-slate-200/90 dark:border-slate-800'">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 flex items-center justify-center text-sm font-black shrink-0">
                        <i class="fa-solid fa-battery-quarter"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-black text-slate-900 dark:text-white leading-tight">Energie is op</h3>
                        <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400">Hoe herstel ik snel?</span>
                    </div>
                </div>
                <div x-show="activeSolver === 'energie'" x-collapse class="pt-2 text-xs text-slate-600 dark:text-slate-300 space-y-1.5 border-t border-slate-100 dark:border-slate-800">
                    <p>• <strong>Wachten:</strong> +1 energie per 5 min (elke 3 min voor VIP, tot 2 min voor Lifetime).</p>
                    <p>• <strong>Kantine:</strong> Koop snacks zoals chocoladerepen en energiedrankjes in de winkel.</p>
                    <p>• <strong>Rad:</strong> Draai gratis aan het Rad van Fortuin.</p>
                    <a href="{{ route('shop.index', ['tab' => 'inventaris']) }}" class="inline-block mt-1 px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-black text-[10px] transition cursor-pointer">Naar Kantine &raquo;</a>
                </div>
            </div>

            <!-- Solver 2: Veilig geld sparen -->
            <div @click="activeSolver = activeSolver === 'bank' ? null : 'bank'"
                 class="bg-white dark:bg-slate-900 rounded-2xl p-4 border transition-all duration-200 cursor-pointer shadow-xs hover:border-blue-400 dark:hover:border-blue-600"
                 :class="activeSolver === 'bank' ? 'border-blue-500 ring-2 ring-blue-500/20 bg-blue-50/20 dark:bg-blue-950/20' : 'border-slate-200/90 dark:border-slate-800'">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300 flex items-center justify-center text-sm font-black shrink-0">
                        <i class="fa-solid fa-vault"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-black text-slate-900 dark:text-white leading-tight">Ik word beroofd</h3>
                        <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400">Geld 100% beschermen</span>
                    </div>
                </div>
                <div x-show="activeSolver === 'bank'" x-collapse class="pt-2 text-xs text-slate-600 dark:text-slate-300 space-y-1.5 border-t border-slate-100 dark:border-slate-800">
                    <p>• <strong>De Bank:</strong> Stort contant geld altijd direct op de bank. Bankgeld kan NOOIT worden gestolen via de Respectlijst of zakkenrollen!</p>
                    <p>• <strong>Liquidatie:</strong> Activeer 24u Cash Bescherming in de VIP Shop tegen liquidaties.</p>
                    <a href="{{ route('bank.index') }}" class="inline-block mt-1 px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-black text-[10px] transition cursor-pointer">Naar De Bank &raquo;</a>
                </div>
            </div>

            <!-- Solver 3: Isoleercel -->
            <div @click="activeSolver = activeSolver === 'gevangenis' ? null : 'gevangenis'"
                 class="bg-white dark:bg-slate-900 rounded-2xl p-4 border transition-all duration-200 cursor-pointer shadow-xs hover:border-amber-400 dark:hover:border-amber-600"
                 :class="activeSolver === 'gevangenis' ? 'border-amber-500 ring-2 ring-amber-500/20 bg-amber-50/20 dark:bg-amber-950/20' : 'border-slate-200/90 dark:border-slate-800'">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300 flex items-center justify-center text-sm font-black shrink-0">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-black text-slate-900 dark:text-white leading-tight">Ik zit in de Cel</h3>
                        <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400">Snelle vrijlating</span>
                    </div>
                </div>
                <div x-show="activeSolver === 'gevangenis'" x-collapse class="pt-2 text-xs text-slate-600 dark:text-slate-300 space-y-1.5 border-t border-slate-100 dark:border-slate-800">
                    <p>• <strong>IJzerzaagje:</strong> -30 min straftijd per zaagje.</p>
                    <p>• <strong>Luchtschacht:</strong> Waag 1x per straf een directe uitbraakpoging (20 energie).</p>
                    <p>• <strong>Vrijkoopakte:</strong> Koop jezelf vrij voor 25 Sterren ⭐ of kijk een sponsorvideo.</p>
                    <a href="{{ route('prison') }}" class="inline-block mt-1 px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-black text-[10px] transition cursor-pointer">Naar Isoleercel &raquo;</a>
                </div>
            </div>

            <!-- Solver 4: Gewond / HP laag -->
            <div @click="activeSolver = activeSolver === 'gezondheid' ? null : 'gezondheid'"
                 class="bg-white dark:bg-slate-900 rounded-2xl p-4 border transition-all duration-200 cursor-pointer shadow-xs hover:border-rose-400 dark:hover:border-rose-600"
                 :class="activeSolver === 'gezondheid' ? 'border-rose-500 ring-2 ring-rose-500/20 bg-rose-50/20 dark:bg-rose-950/20' : 'border-slate-200/90 dark:border-slate-800'">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300 flex items-center justify-center text-sm font-black shrink-0">
                        <i class="fa-solid fa-heart-pulse"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-black text-slate-900 dark:text-white leading-tight">HP is Bijna 0</h3>
                        <span class="text-[10px] font-bold text-rose-600 dark:text-rose-400">Gratis genezen</span>
                    </div>
                </div>
                <div x-show="activeSolver === 'gezondheid'" x-collapse class="pt-2 text-xs text-slate-600 dark:text-slate-300 space-y-1.5 border-t border-slate-100 dark:border-slate-800">
                    <p>• <strong>Zuster Brenda:</strong> Ga naar de Ziekenboeg. Brenda lapt je direct 100% GRATIS op!</p>
                    <p>• <strong>EHBO-kits:</strong> Gebruik verbanddozen uit je inventaris als je snel moet vechten.</p>
                    <a href="{{ route('infirmary.index') }}" class="inline-block mt-1 px-3 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-black text-[10px] transition cursor-pointer">Naar Ziekenboeg &raquo;</a>
                </div>
            </div>

            <!-- Solver 5: Gratis VIP -->
            <div @click="activeSolver = activeSolver === 'vip' ? null : 'vip'"
                 class="bg-white dark:bg-slate-900 rounded-2xl p-4 border transition-all duration-200 cursor-pointer shadow-xs hover:border-amber-400 dark:hover:border-amber-600"
                 :class="activeSolver === 'vip' ? 'border-amber-500 ring-2 ring-amber-500/20 bg-amber-50/20 dark:bg-amber-950/20' : 'border-slate-200/90 dark:border-slate-800'">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300 flex items-center justify-center text-sm font-black shrink-0">
                        <i class="fa-solid fa-crown"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-black text-slate-900 dark:text-white leading-tight">Gratis VIP</h3>
                        <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400">100% Kosteloos</span>
                    </div>
                </div>
                <div x-show="activeSolver === 'vip'" x-collapse class="pt-2 text-xs text-slate-600 dark:text-slate-300 space-y-1.5 border-t border-slate-100 dark:border-slate-800">
                    <p>• <strong>Sponsorkamer:</strong> Bekijk 250 korte 15s ads voor 24u GRATIS VIP!</p>
                    <p>• <strong>Discord:</strong> Koppel je account voor 3 DAGEN GRATIS VIP + €2.500 cash.</p>
                    <a href="{{ route('sponsor.index') }}" class="inline-block mt-1 px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-black text-[10px] transition cursor-pointer">Naar Sponsorkamer &raquo;</a>
                </div>
            </div>

            <!-- Solver 6: Doel van het spel -->
            <div @click="activeSolver = activeSolver === 'doel' ? null : 'doel'"
                 class="bg-white dark:bg-slate-900 rounded-2xl p-4 border transition-all duration-200 cursor-pointer shadow-xs hover:border-purple-400 dark:hover:border-purple-600"
                 :class="activeSolver === 'doel' ? 'border-purple-500 ring-2 ring-purple-500/20 bg-purple-50/20 dark:bg-purple-950/20' : 'border-slate-200/90 dark:border-slate-800'">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300 flex items-center justify-center text-sm font-black shrink-0">
                        <i class="fa-solid fa-trophy"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-black text-slate-900 dark:text-white leading-tight">Wat is het Doel?</h3>
                        <span class="text-[10px] font-bold text-purple-600 dark:text-purple-400">Heerschappij &amp; Eer</span>
                    </div>
                </div>
                <div x-show="activeSolver === 'doel'" x-collapse class="pt-2 text-xs text-slate-600 dark:text-slate-300 space-y-1.5 border-t border-slate-100 dark:border-slate-800">
                    <p>• <strong>Respectlijst:</strong> Strijd om de #1 positie van de kliniek.</p>
                    <p>• <strong>24-Uurs Battle:</strong> Doe dagelijks mee aan de afdelingsslag.</p>
                    <p>• <strong>Bendes:</strong> Richt je eigen clan op en hef bende-uurloon.</p>
                    <a href="{{ route('leaderboard.index') }}" class="inline-block mt-1 px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-black text-[10px] transition cursor-pointer">Respectlijst Bekijken &raquo;</a>
                </div>
            </div>

        </div>
    </div>

    <!-- 2. COLLAPSIBLE GEVECHT & STATS CALCULATOR -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-xs transition-all">
        <div class="flex items-center justify-between gap-3 cursor-pointer" @click="showSimulator = !showSimulator">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-rose-100 dark:bg-rose-950/70 text-rose-600 dark:text-rose-400 flex items-center justify-center text-sm font-black shadow-xs">
                    <i class="fa-solid fa-calculator"></i>
                </div>
                <div>
                    <h3 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider font-['Outfit']">
                        Interactieve Gevecht Simulator
                    </h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        Bereken direct je winstkans op basis van jouw Kracht en de Verdediging van een tegenstander.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-black px-2.5 py-1 rounded-full border hidden sm:inline-block"
                      :class="winChance >= 60 ? 'bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border-emerald-300' : (winChance >= 40 ? 'bg-amber-50 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border-amber-300' : 'bg-rose-50 dark:bg-rose-950 text-rose-700 dark:text-rose-300 border-rose-300')">
                    Winstkans <span x-text="winChance + '%'"></span>
                </span>
                <button type="button" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 flex items-center justify-center text-xs transition">
                    <i class="fa-solid" :class="showSimulator ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                </button>
            </div>
        </div>

        <div x-show="showSimulator" x-collapse class="pt-4 mt-3 border-t border-slate-100 dark:border-slate-800 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Slider 1: Jouw Kracht -->
                <div class="space-y-2 bg-slate-50/90 dark:bg-slate-800/60 p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-700/70">
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-extrabold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                            <i class="fa-solid fa-hand-fist text-rose-500"></i> Jouw Kracht (Aanval)
                        </span>
                        <span class="font-mono font-black text-rose-600 text-sm" x-text="calcStrength"></span>
                    </div>
                    <input type="range" min="10" max="150" step="5" x-model.number="calcStrength" class="w-full accent-rose-600 cursor-pointer">
                    <p class="text-[10px] text-slate-400">Verhoog je kracht door te trainen in de fitnessruimte ('/training').</p>
                </div>

                <!-- Slider 2: Tegenstander Verdediging -->
                <div class="space-y-2 bg-slate-50/90 dark:bg-slate-800/60 p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-700/70">
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-extrabold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                            <i class="fa-solid fa-shield-halved text-indigo-500"></i> Tegenstander Verdediging
                        </span>
                        <span class="font-mono font-black text-indigo-600 text-sm" x-text="calcOppDefense"></span>
                    </div>
                    <input type="range" min="10" max="150" step="5" x-model.number="calcOppDefense" class="w-full accent-indigo-600 cursor-pointer">
                    <p class="text-[10px] text-slate-400">Kogelwerende vesten en bepantsering verhogen de verdediging.</p>
                </div>
            </div>

            <!-- Simulator Outcome Advice -->
            <div class="p-3.5 rounded-xl border text-xs flex items-center gap-3"
                 :class="winChance >= 60 ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200' : (winChance >= 40 ? 'bg-amber-50 dark:bg-amber-950/40 border-amber-300 dark:border-amber-800 text-amber-800 dark:text-amber-200' : 'bg-rose-50 dark:bg-rose-950/40 border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200')">
                <i class="fa-solid text-lg shrink-0" :class="winChance >= 60 ? 'fa-circle-check text-emerald-600' : (winChance >= 40 ? 'fa-triangle-exclamation text-amber-600' : 'fa-skull-crossbones text-rose-600')"></i>
                <div>
                    <strong x-show="winChance >= 60">Uitstekende kans op overwinning!</strong>
                    <strong x-show="winChance >= 40 && winChance < 60">Gewaagd duel! Goede kans op winst, maar train voor extra zekerheid.</strong>
                    <strong x-show="winChance < 40">Hoog risico op verlies en ziekenboegopname! Train eerst je kracht.</strong>
                    <p class="text-[11px] opacity-90 mt-0.5">Bij winst steel je respect én tot 15% van zijn contante geld op zak!</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Horizontal Category Pills (Sticky & Fast) -->
    <div class="lg:hidden flex items-center gap-2 overflow-x-auto pb-2 no-scrollbar" x-show="!searchQuery">
        @foreach($categories as $catKey => $category)
        <button type="button" 
                @click="selectCategory(@js($catKey))"
                :class="activeCategory === @js($catKey) ? 'bg-purple-600 text-white shadow-md' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800'"
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition shrink-0 flex items-center gap-1.5 cursor-pointer">
            <i class="fa-solid {{ $category['icon'] }} text-[11px]"></i>
            <span>{{ trim(preg_replace('/^[^\p{L}\p{N}]+/u', '', $category['title'])) }}</span>
        </button>
        @endforeach
    </div>

    <!-- 3. MAIN KNOWLEDGE BASE AREA: MASTER-DETAIL / CATEGORY NAVIGATOR -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left Sidebar: Category List (Hidden during global search) -->
        <div class="lg:col-span-4 space-y-2 hidden lg:block" x-show="!searchQuery">
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs sticky top-20">
                <div class="flex items-center justify-between pb-3 mb-2 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                        <i class="fa-solid fa-list-ul text-purple-500"></i> Thema's &amp; Onderwerpen
                    </span>
                    <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 px-2 py-0.5 rounded-full">
                        {{ count($categories) }} categorieën
                    </span>
                </div>

                <div class="space-y-1 max-h-[750px] overflow-y-auto pr-1">
                    @foreach($categories as $catKey => $category)
                    <button type="button" 
                            @click="selectCategory(@js($catKey))"
                            :class="activeCategory === @js($catKey) 
                                ? 'bg-purple-600 text-white font-black shadow-xs' 
                                : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 font-medium'"
                            class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs transition-all flex items-center justify-between gap-3 cursor-pointer group">
                        
                        <div class="flex items-center gap-2.5 truncate">
                            <span class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0 text-xs transition"
                                  :class="activeCategory === @js($catKey) ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-purple-600 dark:text-purple-400 group-hover:bg-purple-100 dark:group-hover:bg-purple-950'">
                                <i class="fa-solid {{ $category['icon'] }}"></i>
                            </span>
                            <span class="truncate">{{ $category['title'] }}</span>
                        </div>

                        <span class="text-[10px] px-1.5 py-0.5 rounded-md shrink-0 font-mono font-bold"
                              :class="activeCategory === @js($catKey) ? 'bg-white/25 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-400'">
                            {{ count($category['questions']) }}
                        </span>
                    </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right Main Column: Questions & Answers -->
        <div :class="searchQuery ? 'lg:col-span-12' : 'lg:col-span-8'" class="space-y-4">
            
            <!-- Global Search Mode Header -->
            <div x-show="searchQuery" class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-magnifying-glass text-purple-500"></i>
                        <span>Zoekresultaten voor '<span x-text="searchQuery" class="text-purple-600 dark:text-purple-400"></span>'</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Hieronder zie je alle vragen en antwoorden die overeenkomen met je zoekopdracht.
                    </p>
                </div>
                <button type="button" 
                        @click="searchQuery = ''" 
                        class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-xs transition-all duration-150 active:scale-95 shrink-0 cursor-pointer">
                    <i class="fa-solid fa-xmark mr-1"></i> Sluit zoeken
                </button>
            </div>

            <!-- SEARCH RESULTS LIST -->
            <div x-show="searchQuery" class="space-y-3">
                @foreach($categories as $catKey => $category)
                    @foreach($category['questions'] as $qIndex => $item)
                    @php $uniqueKey = 'search_' . $catKey . '_' . $qIndex; @endphp
                    <div x-show="matchesSearch(@js($item['q']), @js($item['a']))"
                         class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-xs transition-all">
                        
                        <div class="flex items-center gap-2 mb-2">
                            <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70">
                                <i class="fa-solid {{ $category['icon'] }} text-[9px]"></i>
                                <span>{{ $category['title'] }}</span>
                            </span>
                        </div>

                        <button type="button" 
                                @click="toggleQuestion('{{ $uniqueKey }}')"
                                class="w-full text-left flex items-start justify-between gap-3 cursor-pointer group">
                            <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition">
                                {{ $item['q'] }}
                            </h4>
                            <span class="w-6 h-6 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 text-xs shrink-0 transition"
                                  :class="{ 'rotate-180 bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-300': isQuestionOpen('{{ $uniqueKey }}') }">
                                <i class="fa-solid fa-chevron-down text-[10px]"></i>
                            </span>
                        </button>

                        <div x-show="isQuestionOpen('{{ $uniqueKey }}')" x-cloak class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800 text-xs sm:text-sm text-slate-600 dark:text-slate-300 whitespace-pre-line leading-relaxed bg-slate-50/90 dark:bg-slate-800/60 p-4 rounded-xl border border-slate-200/80 dark:border-slate-700/70">
                            {{ $item['a'] }}

                            @if(!empty($item['links']))
                                <div class="mt-3.5 flex flex-wrap gap-2 pt-2.5 border-t border-slate-200/70 dark:border-slate-700/70">
                                    @foreach($item['links'] as $btn)
                                        <a href="{{ $btn['url'] }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-extrabold text-[11px] shadow-xs transition-all active:scale-95 cursor-pointer">
                                            <span>{{ $btn['text'] }}</span>
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[9px] opacity-80"></i>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                @endforeach
            </div>

            <!-- REGULAR CATEGORY VIEW (WHEN NOT SEARCHING) -->
            <div x-show="!searchQuery" class="space-y-4">
                @foreach($categories as $catKey => $category)
                <div x-show="activeCategory === '{{ $catKey }}'" class="space-y-4">
                    
                    <!-- Selected Category Header Card -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-xl bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 flex items-center justify-center text-lg font-black shrink-0">
                                <i class="fa-solid {{ $category['icon'] }}"></i>
                            </div>
                            <div>
                                <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white">
                                    {{ $category['title'] }}
                                </h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    {{ count($category['questions']) }} veelgestelde vragen over dit onderwerp
                                </p>
                            </div>
                        </div>

                        <div>
                            <button type="button" 
                                    @click="toggleAllQuestions()"
                                    class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-xs transition-all duration-150 active:scale-95 cursor-pointer flex items-center gap-1.5">
                                <i class="fa-solid" :class="allOpen ? 'fa-compress' : 'fa-expand'"></i>
                                <span x-text="allOpen ? 'Klap alles in' : 'Klap alles uit'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Accordion Question List for this Category -->
                    <div class="space-y-3">
                        @foreach($category['questions'] as $qIndex => $item)
                        @php $uniqueKey = $catKey . '_' . $qIndex; @endphp
                        <div class="bg-white dark:bg-slate-900 border rounded-2xl p-4 sm:p-5 shadow-xs transition-all duration-200"
                             :class="isQuestionOpen('{{ $uniqueKey }}') 
                                ? 'border-purple-400 dark:border-purple-600 ring-2 ring-purple-500/10' 
                                : 'border-slate-200/90 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700/80'">
                            
                            <button type="button" 
                                    @click="toggleQuestion('{{ $uniqueKey }}')"
                                    class="w-full text-left flex items-start justify-between gap-4 cursor-pointer group">
                                <span class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-slate-100 flex items-start gap-2.5">
                                    <span class="w-5 h-5 rounded-md bg-purple-50 dark:bg-purple-950 text-purple-600 dark:text-purple-400 flex items-center justify-center text-[10px] shrink-0 mt-0.5 font-bold">
                                        {{ $qIndex + 1 }}
                                    </span>
                                    <span class="group-hover:text-purple-600 dark:group-hover:text-purple-400 transition">{{ $item['q'] }}</span>
                                </span>
                                
                                <span class="w-7 h-7 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 text-xs shrink-0 transition"
                                      :class="{ 'rotate-180 bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-300': isQuestionOpen('{{ $uniqueKey }}') }">
                                    <i class="fa-solid fa-chevron-down text-[11px]"></i>
                                </span>
                            </button>

                            <div x-show="isQuestionOpen('{{ $uniqueKey }}')" 
                                 x-cloak
                                 class="mt-3.5 pt-3.5 border-t border-slate-100 dark:border-slate-800 text-xs sm:text-sm text-slate-700 dark:text-slate-200 whitespace-pre-line leading-relaxed bg-slate-50/90 dark:bg-slate-800/60 p-4 sm:p-5 rounded-xl border border-slate-200/80 dark:border-slate-700/70">
                                {{ $item['a'] }}

                                @if(!empty($item['links']))
                                    <div class="mt-3.5 flex flex-wrap gap-2 pt-2.5 border-t border-slate-200/70 dark:border-slate-700/70">
                                        @foreach($item['links'] as $btn)
                                            <a href="{{ $btn['url'] }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-extrabold text-[11px] shadow-xs transition-all active:scale-95 cursor-pointer">
                                                <span>{{ $btn['text'] }}</span>
                                                <i class="fa-solid fa-arrow-up-right-from-square text-[9px] opacity-80"></i>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                </div>
                @endforeach
            </div>

        </div>

    </div>

    <!-- Need More Help Banner -->
    <div class="bg-gradient-to-r from-purple-50 via-white to-purple-50 dark:from-slate-900 dark:via-purple-950/20 dark:to-slate-900 border border-purple-200/90 dark:border-purple-800/70 rounded-2xl p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-purple-600 text-white flex items-center justify-center text-xl shrink-0 shadow-md">
                <i class="fa-solid fa-headset"></i>
            </div>
            <div>
                <h4 class="text-sm font-black text-slate-900 dark:text-white">Niet gevonden wat je zocht?</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400">Stel je vraag direct in de Live Chat of open een officieel ticket bij het beheerdersteam.</p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto">
            <a href="{{ route('help.tickets.create') }}" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs transition-all duration-150 active:scale-95 shadow-xs cursor-pointer flex items-center justify-center gap-2">
                <i class="fa-solid fa-ticket"></i>
                <span>Nieuw Ticket Aanmaken</span>
            </a>
        </div>
    </div>

</div>
@endsection
