@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    activeCategory: 'beginners',
    openQuestion: 'beginners_0',
    searchQuery: @js($search ?? ''),
    showSimulator: false,
    allOpen: false,
    calcStrength: 25,
    calcOppDefense: 20,
    activeRoadmapStep: 1,

    get winChance() {
        const str = Math.max(1, Number(this.calcStrength) || 1);
        const def = Math.max(1, Number(this.calcOppDefense) || 1);
        const ratio = str / Math.max(1, str + def);
        return Math.min(95, Math.max(10, Math.round(ratio * 100)));
    },

    selectCategory(catKey) {
        this.activeCategory = catKey;
        this.openQuestion = catKey + '_0';
        this.allOpen = false;
        if (this.searchQuery) {
            this.searchQuery = '';
        }
    },

    toggleQuestion(key) {
        if (this.allOpen) {
            this.allOpen = false;
            this.openQuestion = key;
            return;
        }
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
        const q = (this.searchQuery || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
        if (!q) return true;
        const hay = ((qText || '') + ' ' + (aText || ''))
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
        return q.split(/\s+/).every(term => hay.includes(term));
    }
}">

    <!-- Top Hero Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-purple-900 via-indigo-950 to-slate-900 p-6 sm:p-8 text-white shadow-xl border border-purple-500/30">
        <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-1.5 bg-purple-500/20 text-purple-200 text-xs font-bold px-3 py-1 rounded-full border border-purple-400/30 mb-2.5 backdrop-blur-xs">
                    <i class="fa-solid fa-book-bookmark text-amber-300"></i> Simpel &amp; Stap-voor-Stap Uitgelegd
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white font-['Outfit']">
                    Kliniek Kennisbank & FAQ
                </h1>
                <p class="text-xs sm:text-sm text-purple-200/90 mt-1 max-w-xl leading-relaxed">
                    Van je allereerste minuut als groentje tot ervaren bendeleider: hier staat alles in gewone, simpele taal uitgelegd. Elk antwoord heeft een korte samenvatting én een pro-tip!
                </p>
                <div class="mt-3 flex flex-wrap items-center gap-2 text-[11px] text-purple-300 font-bold">
                    <span class="inline-flex items-center gap-1 bg-white/10 px-2.5 py-0.5 rounded-full border border-white/10">
                        <i class="fa-solid fa-layer-group text-amber-300"></i> {{ count($categories) }} Thema's
                    </span>
                    <span class="inline-flex items-center gap-1 bg-white/10 px-2.5 py-0.5 rounded-full border border-white/10">
                        <i class="fa-solid fa-circle-check text-emerald-400"></i> Beginner t/m Pro
                    </span>
                    <span class="inline-flex items-center gap-1 bg-white/10 px-2.5 py-0.5 rounded-full border border-white/10">
                        <i class="fa-solid fa-bolt text-cyan-300"></i> Directe Snelkoppelingen
                    </span>
                </div>
            </div>
            
            <div class="w-full md:w-80 shrink-0">
                <div class="relative">
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="Wat zoek je? (bijv. bank, vip, cel, masker)..." 
                           class="w-full bg-white/10 dark:bg-black/40 border border-white/20 dark:border-white/10 rounded-xl px-4 py-3 text-xs text-white placeholder-purple-200/60 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:bg-white/20 transition backdrop-blur-md">
                    <button type="button" class="absolute right-3.5 top-3 text-purple-300">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </button>
                </div>
                <div class="flex items-center justify-between mt-2 text-[10px] text-purple-300">
                    <span x-show="!searchQuery.trim()">Kies een thema of typ een woord in</span>
                    <span x-show="searchQuery.trim()" class="text-amber-300 font-bold">Zoekmodus actief</span>
                    <button type="button" x-show="searchQuery.trim()" @click="searchQuery = ''" class="hover:underline text-amber-300 cursor-pointer">
                        Wis zoektekst
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
                    TBS Intake Rondleiding &amp; Uitleg (+ €1.000 Gratis Startbonus)
                </h4>
                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Net begonnen? Start de korte 5-stappen rondleiding door de kliniek en ontvang direct €1.000 cadeau!
                </p>
            </div>
        </div>
        @auth
        <button type="button" 
                @click="window.dispatchEvent(new CustomEvent('tbs-start-intake-tour'))"
                class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white font-black text-xs transition-all duration-150 shadow-xs shrink-0 flex items-center justify-center gap-2 cursor-pointer">
            <i class="fa-solid fa-play text-[10px]"></i>
            <span>Start Rondleiding Nu</span>
        </button>
        @else
        <a href="{{ route('register') }}"
           class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white font-black text-xs transition-all duration-150 shadow-xs shrink-0 flex items-center justify-center gap-2 cursor-pointer">
            <i class="fa-solid fa-play text-[10px]"></i>
            <span>Start Rondleiding Nu</span>
        </a>
        @endauth
    </div>

    <!-- DE 5 GOUDEN REGELS VOOR BEGINNERS (30-SECONDEN SPIEKBRIEFJE) -->
    <div class="bg-amber-50/70 dark:bg-amber-950/20 border border-amber-200/90 dark:border-amber-800/60 rounded-2xl p-4 sm:p-5 shadow-xs">
        <div class="flex items-center gap-2 mb-3">
            <span class="w-6 h-6 rounded-lg bg-amber-500 text-white flex items-center justify-center text-xs font-black shrink-0">
                <i class="fa-solid fa-star"></i>
            </span>
            <h2 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white uppercase tracking-wide font-['Outfit']">
                De 5 Gouden Regels (30-Seconden Spiekbriefje voor Beginners)
            </h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2.5 text-xs">
            <a href="{{ route('bank.index') }}" class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-amber-200/80 dark:border-slate-800 hover:border-purple-400 transition block">
                <span class="font-black text-emerald-600 dark:text-emerald-400 block mb-0.5">1. 🏦 Zet Geld op de Bank</span>
                <span class="text-[11px] text-slate-600 dark:text-slate-300 leading-snug block">Geld in je broekzak kan gestolen worden. Op De Bank staat het veilig!</span>
            </a>
            <a href="{{ route('daycare.index') }}" class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-amber-200/80 dark:border-slate-800 hover:border-purple-400 transition block">
                <span class="font-black text-purple-600 dark:text-purple-400 block mb-0.5">2. 🔨 Werk bij Henk (0% Straf)</span>
                <span class="text-[11px] text-slate-600 dark:text-slate-300 leading-snug block">In de Werkplaats verdien je veilig geld zonder kans op de isoleercel.</span>
            </a>
            <a href="{{ route('infirmary.index') }}" class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-amber-200/80 dark:border-slate-800 hover:border-purple-400 transition block">
                <span class="font-black text-rose-600 dark:text-rose-400 block mb-0.5">3. 🏥 Gratis Genezen</span>
                <span class="text-[11px] text-slate-600 dark:text-slate-300 leading-snug block">Gewond? Zuster Ingrid in de Ziekenboeg geneest je (+45 HP per 4 min) 100% gratis.</span>
            </a>
            <a href="{{ route('pickpocket.index') }}" class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-amber-200/80 dark:border-slate-800 hover:border-purple-400 transition block">
                <span class="font-black text-indigo-600 dark:text-indigo-400 block mb-0.5">4. 🥷 Scoor een Masker</span>
                <span class="text-[11px] text-slate-600 dark:text-slate-300 leading-snug block">Steel een masker bij Zakkenrollen: dit halveert je kans om gepakt te worden!</span>
            </a>
            <a href="{{ route('dashboard') }}" class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-amber-200/80 dark:border-slate-800 hover:border-purple-400 transition block">
                <span class="font-black text-amber-600 dark:text-amber-400 block mb-0.5">5. 🎁 Pak €35.000+ Startgeld</span>
                <span class="text-[11px] text-slate-600 dark:text-slate-300 leading-snug block">Doe de 5 Rookie Taken op je Dashboard, koppel Discord &amp; zet meldingen aan!</span>
            </a>
        </div>
    </div>

    <!-- BEGINNER ROADMAP: VAN START TOT EINDE (INTERACTIVE STEPPER) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-purple-600 dark:text-purple-400">Stap-voor-Stap Groeipad</span>
                <h2 class="text-sm sm:text-base font-black text-slate-900 dark:text-white font-['Outfit'] flex items-center gap-2">
                    <i class="fa-solid fa-route text-purple-500"></i>
                    <span>Van Binnenkomst tot Kliniekbaas: Het Complete Stappenplan</span>
                </h2>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="activeRoadmapStep = Math.max(1, activeRoadmapStep - 1)" :disabled="activeRoadmapStep === 1" class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold disabled:opacity-40 cursor-pointer">&laquo; Vorige</button>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-black">Stap <span x-text="activeRoadmapStep"></span> / 6</span>
                <button type="button" @click="activeRoadmapStep = Math.min(6, activeRoadmapStep + 1)" :disabled="activeRoadmapStep === 6" class="px-2.5 py-1 rounded-lg bg-purple-600 text-white text-xs font-bold disabled:opacity-40 cursor-pointer">Volgende &raquo;</button>
            </div>
        </div>

        <!-- 6 Step Stepper Tabs -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2">
            <button type="button" @click="activeRoadmapStep = 1"
                    :class="activeRoadmapStep === 1 ? 'border-purple-600 bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 font-black' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                    class="p-2.5 rounded-xl border text-left transition cursor-pointer flex flex-col gap-1">
                <span class="text-[10px] font-extrabold uppercase opacity-75">Stap 1 (Beginner)</span>
                <span class="text-xs font-black truncate">Dag 1: Veilig Starten</span>
            </button>
            <button type="button" @click="activeRoadmapStep = 2"
                    :class="activeRoadmapStep === 2 ? 'border-purple-600 bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 font-black' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                    class="p-2.5 rounded-xl border text-left transition cursor-pointer flex flex-col gap-1">
                <span class="text-[10px] font-extrabold uppercase opacity-75">Stap 2 (Level 1-3)</span>
                <span class="text-xs font-black truncate">Veilig Geld &amp; XP</span>
            </button>
            <button type="button" @click="activeRoadmapStep = 3"
                    :class="activeRoadmapStep === 3 ? 'border-purple-600 bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 font-black' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                    class="p-2.5 rounded-xl border text-left transition cursor-pointer flex flex-col gap-1">
                <span class="text-[10px] font-extrabold uppercase opacity-75">Stap 3 (Level 3-6)</span>
                <span class="text-xs font-black truncate">Gym &amp; Uitrusting</span>
            </button>
            <button type="button" @click="activeRoadmapStep = 4"
                    :class="activeRoadmapStep === 4 ? 'border-purple-600 bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 font-black' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                    class="p-2.5 rounded-xl border text-left transition cursor-pointer flex flex-col gap-1">
                <span class="text-[10px] font-extrabold uppercase opacity-75">Stap 4 (Level 6-8)</span>
                <span class="text-xs font-black truncate">Misdaad &amp; Maskers</span>
            </button>
            <button type="button" @click="activeRoadmapStep = 5"
                    :class="activeRoadmapStep === 5 ? 'border-purple-600 bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 font-black' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                    class="p-2.5 rounded-xl border text-left transition cursor-pointer flex flex-col gap-1">
                <span class="text-[10px] font-extrabold uppercase opacity-75">Stap 5 (Gevorderd)</span>
                <span class="text-xs font-black truncate">Vechten &amp; Reizen</span>
            </button>
            <button type="button" @click="activeRoadmapStep = 6"
                    :class="activeRoadmapStep === 6 ? 'border-purple-600 bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 font-black' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                    class="p-2.5 rounded-xl border text-left transition cursor-pointer flex flex-col gap-1">
                <span class="text-[10px] font-extrabold uppercase opacity-75">Stap 6 (Pro Baas)</span>
                <span class="text-xs font-black truncate">Bendes &amp; Heists</span>
            </button>
        </div>

        <!-- Step 1 Content -->
        <div x-show="activeRoadmapStep === 1" class="p-4 sm:p-5 rounded-xl bg-purple-50/50 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-900 space-y-3">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-purple-600 text-white flex items-center justify-center font-black text-sm shrink-0">1</span>
                <div>
                    <h4 class="text-sm font-black text-slate-900 dark:text-white">Je Eerste 10 Minuten: Gratis Bonussen &amp; Geld Veiligstellen</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Zo pak je direct een vliegende start zonder een cent te verliezen.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs text-slate-700 dark:text-slate-300">
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-purple-600 dark:text-purple-400 block font-black">🏥 1. Gratis Genezen &amp; Rad</strong>
                    <p>Haal gratis +45 HP bij Zuster Ingrid in de Ziekenboeg en draai 1x gratis aan het Rad van Fortuin.</p>
                </div>
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-purple-600 dark:text-purple-400 block font-black">🎯 2. De 5 Rookie Taken</strong>
                    <p>Vink de 5 makkelijke beginnersopdrachten op je Dashboard af voor <strong>€6.500 cash, 30 kogels en 1 dag gratis VIP</strong>!</p>
                </div>
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-rose-600 dark:text-rose-400 block font-black">🏦 3. ALLES OP DE BANK</strong>
                    <p>Stort je contante geld altijd direct gratis op De Bank. Op de bank kan niemand het van je stelen op de Luchtplaats!</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 pt-1">
                <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Dashboard (Rookie Taken)</a>
                <a href="{{ route('infirmary.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Ziekenboeg</a>
                <a href="{{ route('daily.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Rad van Fortuin</a>
                <a href="{{ route('bank.index') }}" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-[11px] transition">Naar De Bank</a>
            </div>
        </div>

        <!-- Step 2 Content -->
        <div x-show="activeRoadmapStep === 2" class="p-4 sm:p-5 rounded-xl bg-purple-50/50 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-900 space-y-3">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-purple-600 text-white flex items-center justify-center font-black text-sm shrink-0">2</span>
                <div>
                    <h4 class="text-sm font-black text-slate-900 dark:text-white">Risicoloos Geld &amp; Ervaring (XP) Verdienen (Level 1 t/m 3)</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Verdien je eerste duizenden euro's met 0% kans op de isoleercel.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs text-slate-700 dark:text-slate-300">
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-purple-600 dark:text-purple-400 block font-black">🔨 Dagbesteding bij Henk (100% Veilig)</strong>
                    <p>In de Werkplaats werk je legaal. Je wordt nooit gearresteerd, krijgt altijd geld en vindt soms gratis kluiscodes en zaagjes!</p>
                </div>
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-purple-600 dark:text-purple-400 block font-black">📜 Missies &amp; Opdrachten</strong>
                    <p>Voltooi de missies onder 'Voortgang > Missies'. Ze wijzen je de weg en geven veel startgeld en XP.</p>
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
                    <h4 class="text-sm font-black text-slate-900 dark:text-white">Sterker Worden in de Gym &amp; Winkel (Level 3 t/m 6)</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Train je 6 eigenschappen (10 Energie per sessie) en koop wapens &amp; vesten die direct automatisch werken.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs text-slate-700 dark:text-slate-300">
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-rose-600 dark:text-rose-400 block font-black">🥊 Kracht (Harder Slaan)</strong>
                    <p>Train Kracht in het Krachthonk om veel meer schade te doen als je aanvalt op de Luchtplaats.</p>
                </div>
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-indigo-600 dark:text-indigo-400 block font-black">🛡️ Verdediging &amp; Conditie</strong>
                    <p>Verdediging blokkeert schade van vijanden en Conditie helpt je langer overleven in gevechten.</p>
                </div>
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-amber-600 dark:text-amber-400 block font-black">🧠 Intelligentie, Charisma &amp; Discipline</strong>
                    <p>Verhoog je slagingskans bij kluizen, hacking, smokkelhandel en ontsnappingen!</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 pt-1">
                <a href="{{ route('training.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Naar de Gym</a>
                <a href="{{ route('shop.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Kliniek Winkel</a>
            </div>
        </div>

        <!-- Step 4 Content -->
        <div x-show="activeRoadmapStep === 4" class="p-4 sm:p-5 rounded-xl bg-purple-50/50 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-900 space-y-3">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-purple-600 text-white flex items-center justify-center font-black text-sm shrink-0">4</span>
                <div>
                    <h4 class="text-sm font-black text-slate-900 dark:text-white">Hosselen, Zakkenrollen &amp; Maskers Scoren (Level 6 t/m 8)</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Pak grotere bedragen en halveer je celkans met een bivakmuts.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs text-slate-700 dark:text-slate-300">
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-purple-600 dark:text-purple-400 block font-black">🥷 Zakkenrollen voor een Masker</strong>
                    <p>Steel een bivakmuts (masker) bij Zakkenrollen. Zolang je een masker hebt, <strong>halveert je kans om opgepakt te worden</strong> bij al je misdaden!</p>
                </div>
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-purple-600 dark:text-purple-400 block font-black">🚬 Hosselen &amp; Koffers Kraken</strong>
                    <p>Doe snelle deals op de gang en breek onderschepte smokkelkoffers open met een breekijzer of lockpick.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 pt-1">
                <a href="{{ route('pickpocket.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Zakkenrollen</a>
                <a href="{{ route('hustle.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Hosselen</a>
                <a href="{{ route('packages.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Koffers Kraken</a>
            </div>
        </div>

        <!-- Step 5 Content -->
        <div x-show="activeRoadmapStep === 5" class="p-4 sm:p-5 rounded-xl bg-purple-50/50 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-900 space-y-3">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-purple-600 text-white flex items-center justify-center font-black text-sm shrink-0">5</span>
                <div>
                    <h4 class="text-sm font-black text-slate-900 dark:text-white">Luchtplaats Gevechten, Reizen (+35 XP) &amp; Verlof (Level 8 t/m 12)</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Steel contant geld van rivalen en stijg razendsnel in level.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs text-slate-700 dark:text-slate-300">
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-purple-600 dark:text-purple-400 block font-black">🥊 Luchtplaats (Respectlijst)</strong>
                    <p>Win het gevecht en steel direct Respect én 10% tot 25% van het contante geld van je tegenstander!</p>
                </div>
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-purple-600 dark:text-purple-400 block font-black">🗺️ Reizen = +35 XP</strong>
                    <p>Reis tussen de gebouwen via 'Verplaatsen'. Elke reis geeft je meteen +35 XP voor snelle level-ups.</p>
                </div>
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-purple-600 dark:text-purple-400 block font-black">🧠 3 Uur Verlof (Elke 2 Uur)</strong>
                    <p>Scoor 60%+ bij Dr. Vink in het Behandelaarsgesprek voor €2.500 + 3u Verlof en doe de 3 buiten-missies!</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 pt-1">
                <a href="{{ route('fight.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Naar Luchtplaats</a>
                <a href="{{ route('travel.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Verplaatsen (+35 XP)</a>
                <a href="{{ route('treatment.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Behandelaarsgesprek</a>
            </div>
        </div>

        <!-- Step 6 Content -->
        <div x-show="activeRoadmapStep === 6" class="p-4 sm:p-5 rounded-xl bg-purple-50/50 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-900 space-y-3">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-purple-600 text-white flex items-center justify-center font-black text-sm shrink-0">6</span>
                <div>
                    <h4 class="text-sm font-black text-slate-900 dark:text-white">Pro Endgame: Bendes, Grote Heists, Controlepunten &amp; Liquidatie (Level 12+)</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Bouw een miljoenenimperium en heers over de kliniek.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs text-slate-700 dark:text-slate-300">
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-purple-600 dark:text-purple-400 block font-black">👥 Bendes &amp; Controlepunten</strong>
                    <p>Sluit je aan bij een Bende voor automatisch Bende-Uurloon en verover de 5 Controlepunten op de locaties voor geld per minuut + Sterren ⭐!</p>
                </div>
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-purple-600 dark:text-purple-400 block font-black">🏎️ Garage &amp; Grote Heists</strong>
                    <p>Koop een snelle vluchtwagen en pleeg gewapende overvallen op juweliers, casino's en de Centrale Bank.</p>
                </div>
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <strong class="text-purple-600 dark:text-purple-400 block font-black">🎯 Liquidatie 2.0 &amp; 24u Battle</strong>
                    <p>Schiet met kogels op rivalen (+25% Sluipaanval op dezelfde afdeling), draag een Backfire-vest en win elke nacht Sterren in de 24 Uur Battle!</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 pt-1">
                <a href="{{ route('gangs.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Bendes</a>
                <a href="{{ route('fight.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Luchtplaats (Controlepunt)</a>
                <a href="{{ route('garage.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition">Garage</a>
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
            <span class="text-[11px] text-slate-400">Klik op je probleem voor de directe oplossing</span>
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
                    <p>• <strong>Kantine:</strong> Eet chocolade of energiedrank uit je inventaris.</p>
                    <p>• <strong>Rad:</strong> Draai gratis aan het Rad van Fortuin.</p>
                    <a href="{{ route('shop.index', ['tab' => 'inventaris']) }}" @click.stop class="inline-block mt-1 px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-black text-[10px] transition cursor-pointer">Naar Kantine &raquo;</a>
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
                    <p>• <strong>De Bank:</strong> Zet al je contant geld altijd direct gratis op De Bank. Bankgeld kan niet gestolen worden op de Luchtplaats!</p>
                    <p>• <strong>Liquidatie:</strong> Activeer 24u Cash Bescherming in de VIP Shop.</p>
                    <a href="{{ route('bank.index') }}" @click.stop class="inline-block mt-1 px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-black text-[10px] transition cursor-pointer">Naar De Bank &raquo;</a>
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
                    <p>• <strong>IJzerzaagje:</strong> Haalt direct 30 min van je straf af.</p>
                    <p>• <strong>Luchtschacht:</strong> Probeer zelf te ontsnappen (20 energie).</p>
                    <p>• <strong>Sterren:</strong> Betaal 10 ⭐ in de Sterrenwinkel of 25 ⭐ direct in de cel om meteen vrij te komen.</p>
                    <a href="{{ route('prison') }}" @click.stop class="inline-block mt-1 px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-black text-[10px] transition cursor-pointer">Naar Isoleercel &raquo;</a>
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
                    <p>• <strong>Zuster Ingrid:</strong> Ga naar de Ziekenboeg voor +45 HP elke 4 min (100% GRATIS)!</p>
                    <p>• <strong>EHBO-kits:</strong> Gebruik een verbanddoos uit je inventaris.</p>
                    <a href="{{ route('infirmary.index') }}" @click.stop class="inline-block mt-1 px-3 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-black text-[10px] transition cursor-pointer">Naar Ziekenboeg &raquo;</a>
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
                    <p>• <strong>Discord:</strong> Koppel je account voor 3 DAGEN GRATIS VIP + €2.500.</p>
                    <p>• <strong>Rookie Taken:</strong> Voltooi de 5 taken op je Dashboard voor 1 DAG GRATIS VIP.</p>
                    <p>• <strong>Sponsorkamer:</strong> 250 korte video's = 24u GRATIS VIP!</p>
                    <a href="{{ route('discord.verify') }}" @click.stop class="inline-block mt-1 px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-black text-[10px] transition cursor-pointer">Discord &amp; VIP &raquo;</a>
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
                    <p>• <strong>Respectlijst:</strong> Word de #1 van de hele kliniek.</p>
                    <p>• <strong>24-Uurs Battle:</strong> Win elke nacht gratis Sterren ⭐.</p>
                    <p>• <strong>Bendes:</strong> Richt een gang op en incasseer uurloon.</p>
                    <a href="{{ route('leaderboard.index') }}" @click.stop class="inline-block mt-1 px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-black text-[10px] transition cursor-pointer">Ranglijst Bekijken &raquo;</a>
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
                        Twijfel je of je iemand kunt verslaan? Bereken hier vooraf je winstkans!
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
                    <p class="text-[10px] text-slate-400">Verhoog je kracht door push-ups te doen in de Gym ('/training').</p>
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
                    <p class="text-[10px] text-slate-400">Kogelwerende vesten en bankdrukken verhogen de verdediging.</p>
                </div>
            </div>

            <!-- Simulator Outcome Advice -->
            <div class="p-3.5 rounded-xl border text-xs flex items-center gap-3"
                 :class="winChance >= 60 ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200' : (winChance >= 40 ? 'bg-amber-50 dark:bg-amber-950/40 border-amber-300 dark:border-amber-800 text-amber-800 dark:text-amber-200' : 'bg-rose-50 dark:bg-rose-950/40 border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200')">
                <i class="fa-solid text-lg shrink-0" :class="winChance >= 60 ? 'fa-circle-check text-emerald-600' : (winChance >= 40 ? 'fa-triangle-exclamation text-amber-600' : 'fa-skull-crossbones text-rose-600')"></i>
                <div>
                    <strong x-show="winChance >= 60">Grote kans op winst! Dit doelwit kun je veilig aanvallen.</strong>
                    <strong x-show="winChance >= 40 && winChance < 60">Spannend gevecht! Je maakt een redelijke kans, maar een extra training helpt.</strong>
                    <strong x-show="winChance < 40">Gevaarlijk! Deze speler is nog te sterk. Train eerst je Kracht in de Gym.</strong>
                    <p class="text-[11px] opacity-90 mt-0.5">Bij winst steel je respect én 10% tot 25% van zijn contante zakgeld!</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Horizontal Category Pills (Sticky & Fast) -->
    <div class="lg:hidden flex items-center gap-2 overflow-x-auto pb-2 no-scrollbar" x-show="!searchQuery.trim()">
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
        <div class="lg:col-span-4 space-y-2 hidden lg:block" x-show="!searchQuery.trim()">
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
        <div :class="searchQuery.trim() ? 'lg:col-span-12' : 'lg:col-span-8'" class="space-y-4">
            
            <!-- Global Search Mode Header -->
            <div x-show="searchQuery.trim()" class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-magnifying-glass text-purple-500"></i>
                        <span>Zoekresultaten voor '<span x-text="searchQuery" class="text-purple-600 dark:text-purple-400"></span>'</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Klik op een vraag om het antwoord en de directe snelkoppelingen te openen.
                    </p>
                </div>
                <button type="button" 
                        @click="searchQuery = ''" 
                        class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-xs transition-all duration-150 active:scale-95 shrink-0 cursor-pointer">
                    <i class="fa-solid fa-xmark mr-1"></i> Sluit zoeken
                </button>
            </div>

            <!-- SEARCH RESULTS LIST -->
            <div x-show="searchQuery.trim()" class="space-y-3">
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
            <div x-show="!searchQuery.trim()" class="space-y-4">
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
                                    {{ count($category['questions']) }} duidelijke vragen &amp; antwoorden in dit hoofdstuk
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
                <h4 class="text-sm font-black text-slate-900 dark:text-white">Staat je vraag er niet tussen?</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400">Vraag het direct aan Zaalwacht Arjan in de Live Chat (rechtsonder) of open een Support Ticket bij het team.</p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto">
            <a href="{{ route('help.tickets.create') }}" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs transition-all duration-150 active:scale-95 shadow-xs cursor-pointer flex items-center justify-center gap-2">
                <i class="fa-solid fa-ticket"></i>
                <span>Nieuw Ticket Aanmaken</span>
            </a>
        </div>
    </div>

    @auth
        @include('partials.intake_tour_modal', ['rookieData' => ['tour_completed' => true]])
    @endauth

</div>
@endsection
