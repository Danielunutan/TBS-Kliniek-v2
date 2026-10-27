@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    activeCategory: 'beginners',
    openQuestion: null,
    searchQuery: '{{ addslashes($search) }}',
    showSimulator: false,
    allOpen: false,
    calcStrength: 25,
    calcOppDefense: 20,

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
                    Vind direct antwoorden op al je vragen over geld verdienen, gevechten, de isoleercel, smokkel en bendes. Kies links een thema of gebruik de live zoekbalk.
                </p>
            </div>
            
            <div class="w-full md:w-80 shrink-0">
                <div class="relative">
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="Typ een zoekwoord (bijv. bank, coma, vip)..." 
                           class="w-full bg-white/10 dark:bg-black/40 border border-white/20 dark:border-white/10 rounded-xl px-4 py-3 text-xs text-white placeholder-purple-200/60 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:bg-white/20 transition backdrop-blur-md">
                    <button type="button" class="absolute right-3.5 top-3 text-purple-300">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </button>
                </div>
                <div class="flex items-center justify-between mt-2 text-[10px] text-purple-300">
                    <span x-show="!searchQuery">Filter per categorie of typ om te zoeken</span>
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
                    Nieuw of al langer lid in de inrichting? Volg de interactieve 5-stappen rondleiding en leer alle basissystemen.
                </p>
            </div>
        </div>
        <button type="button" 
                @click="window.dispatchEvent(new CustomEvent('tbs-start-intake-tour'))"
                class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 active:scale-95 text-white font-black text-xs transition shadow-md shadow-purple-600/25 shrink-0 flex items-center justify-center gap-2 cursor-pointer">
            <i class="fa-solid fa-play text-[10px]"></i>
            <span>Start Rondleiding Nu</span>
        </button>
    </div>

    <!-- 1. "IK WIL..." SNELLE PROBLEEMOPLOSSER (4 Directe Kaarten) -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2">
                <i class="fa-solid fa-bolt text-amber-500"></i>
                <span>Snelle Probleemoplosser voor Gevangenen</span>
            </h2>
            <span class="text-[11px] text-slate-400">Klik voor de oplossing</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5" x-data="{ activeSolver: null }">
            
            <!-- Solver 1: Energie leeg -->
            <div @click="activeSolver = activeSolver === 'energie' ? null : 'energie'"
                 class="bg-white dark:bg-slate-900 rounded-xl p-4 border transition-all duration-200 cursor-pointer shadow-xs hover:border-purple-400 dark:hover:border-purple-600"
                 :class="activeSolver === 'energie' ? 'border-purple-500 ring-2 ring-purple-500/20 bg-purple-50/20 dark:bg-purple-950/20' : 'border-slate-200 dark:border-slate-800'">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 flex items-center justify-center text-sm font-black shrink-0">
                        <i class="fa-solid fa-battery-quarter"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-black text-slate-900 dark:text-white leading-tight">Mijn Energie is op</h3>
                        <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400">Hoe herstel ik snel?</span>
                    </div>
                </div>
                <div x-show="activeSolver === 'energie'" x-collapse class="pt-2 text-xs text-slate-600 dark:text-slate-300 space-y-1.5 border-t border-slate-100 dark:border-slate-800">
                    <p>• <strong>Wachten:</strong> Je energie herstelt automatisch elke 3 minuten.</p>
                    <p>• <strong>Sponsorkamer:</strong> Bekijk 15s video's voor directe energie en beloningen.</p>
                    <p>• <strong>Kantine:</strong> Koop snacks in de kliniekwinkel.</p>
                    <a href="{{ route('sponsor.index') }}" class="inline-block mt-1 px-3 py-1 bg-emerald-600 text-white rounded-lg font-bold text-[10px]">Naar Sponsorkamer &raquo;</a>
                </div>
            </div>

            <!-- Solver 2: Veilig geld sparen -->
            <div @click="activeSolver = activeSolver === 'bank' ? null : 'bank'"
                 class="bg-white dark:bg-slate-900 rounded-xl p-4 border transition-all duration-200 cursor-pointer shadow-xs hover:border-blue-400 dark:hover:border-blue-600"
                 :class="activeSolver === 'bank' ? 'border-blue-500 ring-2 ring-blue-500/20 bg-blue-50/20 dark:bg-blue-950/20' : 'border-slate-200 dark:border-slate-800'">
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
                    <p>• <strong>De Bank:</strong> Stort al je contanten altijd op de bank. Bankgeld kan <em>nooit</em> worden gestolen.</p>
                    <p>• <strong>Rente:</strong> Je ontvangt elke dag om 00:00 uur rente over je banksaldo.</p>
                    <a href="{{ route('bank.index') }}" class="inline-block mt-1 px-3 py-1 bg-blue-600 text-white rounded-lg font-bold text-[10px]">Naar de Bank &raquo;</a>
                </div>
            </div>

            <!-- Solver 3: Isoleercel -->
            <div @click="activeSolver = activeSolver === 'gevangenis' ? null : 'gevangenis'"
                 class="bg-white dark:bg-slate-900 rounded-xl p-4 border transition-all duration-200 cursor-pointer shadow-xs hover:border-amber-400 dark:hover:border-amber-600"
                 :class="activeSolver === 'gevangenis' ? 'border-amber-500 ring-2 ring-amber-500/20 bg-amber-50/20 dark:bg-amber-950/20' : 'border-slate-200 dark:border-slate-800'">
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
                    <p>• <strong>IJzerzaagje:</strong> Zaag tralies door met een zaagje uit je inventaris of de kantine.</p>
                    <p>• <strong>Vrienden:</strong> Vraag een mede-gedetineerde om je celdeur te forceren.</p>
                    <p>• <strong>Borgtocht:</strong> Betaal 10 Sterren in de winkel voor een directe celuitbraak.</p>
                    <a href="{{ route('prison') }}" class="inline-block mt-1 px-3 py-1 bg-amber-500 text-slate-950 rounded-lg font-black text-[10px]">Naar Isoleercel &raquo;</a>
                </div>
            </div>

            <!-- Solver 4: Doel van het spel -->
            <div @click="activeSolver = activeSolver === 'doel' ? null : 'doel'"
                 class="bg-white dark:bg-slate-900 rounded-xl p-4 border transition-all duration-200 cursor-pointer shadow-xs hover:border-purple-400 dark:hover:border-purple-600"
                 :class="activeSolver === 'doel' ? 'border-purple-500 ring-2 ring-purple-500/20 bg-purple-50/20 dark:bg-purple-950/20' : 'border-slate-200 dark:border-slate-800'">
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
                    <p>• <strong>24-Uurs Battle:</strong> Doe dagelijks mee aan de grote afdelingsslag.</p>
                    <p>• <strong>Bendes:</strong> Richt je eigen clan op, hef bende-uurloon en domineer territoria.</p>
                    <a href="{{ route('leaderboard') }}" class="inline-block mt-1 px-3 py-1 bg-purple-600 text-white rounded-lg font-bold text-[10px]">Respectlijst Bekijken &raquo;</a>
                </div>
            </div>

        </div>
    </div>

    <!-- 2. COLLAPSIBLE GEVECHT & STATS CALCULATOR -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-xs transition-all">
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
                    Winstkans: <span x-text="winChance + '%'"></span>
                </span>
                <button type="button" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 flex items-center justify-center text-xs transition">
                    <i class="fa-solid" :class="showSimulator ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                </button>
            </div>
        </div>

        <div x-show="showSimulator" x-collapse class="pt-4 mt-3 border-t border-slate-100 dark:border-slate-800 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Slider 1: Jouw Kracht -->
                <div class="space-y-2 bg-slate-50 dark:bg-slate-800/50 p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-700">
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-extrabold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                            <i class="fa-solid fa-hand-fist text-rose-500"></i> Jouw Kracht (Aanval)
                        </span>
                        <span class="font-mono font-black text-rose-600 text-sm" x-text="calcStrength"></span>
                    </div>
                    <input type="range" min="10" max="150" step="5" x-model.number="calcStrength" class="w-full accent-rose-600 cursor-pointer">
                    <p class="text-[10px] text-slate-400">Verhoog je kracht door te trainen in de fitnessruimte.</p>
                </div>

                <!-- Slider 2: Tegenstander Verdediging -->
                <div class="space-y-2 bg-slate-50 dark:bg-slate-800/50 p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-700">
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
                @click="selectCategory('{{ $catKey }}')"
                :class="activeCategory === '{{ $catKey }}' ? 'bg-purple-600 text-white shadow-md' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800'"
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition shrink-0 flex items-center gap-1.5 cursor-pointer">
            <i class="fa-solid {{ $category['icon'] }} text-[11px]"></i>
            <span>{{ trim(preg_replace('/^[^a-zA-Z0-9]+/', '', $category['title'])) }}</span>
        </button>
        @endforeach
    </div>

    <!-- 3. MAIN KNOWLEDGE BASE AREA: MASTER-DETAIL / CATEGORY NAVIGATOR -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left Sidebar: Category List (Hidden during global search) -->
        <div class="lg:col-span-4 space-y-2 hidden lg:block" x-show="!searchQuery">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
                <div class="flex items-center justify-between pb-3 mb-2 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                        <i class="fa-solid fa-list-ul text-purple-500"></i> Onderwerpen
                    </span>
                    <span class="text-[11px] font-bold text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full">
                        {{ count($categories) }} categorieën
                    </span>
                </div>

                <div class="space-y-1 max-h-[700px] overflow-y-auto pr-1">
                    @foreach($categories as $catKey => $category)
                    <button type="button" 
                            @click="selectCategory('{{ $catKey }}')"
                            :class="activeCategory === '{{ $catKey }}' 
                                ? 'bg-purple-600 text-white font-extrabold shadow-md shadow-purple-600/30' 
                                : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 font-medium'"
                            class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs transition-all flex items-center justify-between gap-3 cursor-pointer group">
                        
                        <div class="flex items-center gap-2.5 truncate">
                            <span class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0 text-xs transition"
                                  :class="activeCategory === '{{ $catKey }}' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-purple-600 dark:text-purple-400 group-hover:bg-purple-100 dark:group-hover:bg-purple-950'">
                                <i class="fa-solid {{ $category['icon'] }}"></i>
                            </span>
                            <span class="truncate">{{ $category['title'] }}</span>
                        </div>

                        <span class="text-[10px] px-1.5 py-0.5 rounded-md shrink-0 font-mono font-bold"
                              :class="activeCategory === '{{ $catKey }}' ? 'bg-white/25 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-400'">
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
            <div x-show="searchQuery" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex items-center justify-between gap-4">
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
                        class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs transition shrink-0 cursor-pointer">
                    <i class="fa-solid fa-xmark mr-1"></i> Sluit zoeken
                </button>
            </div>

            <!-- SEARCH RESULTS LIST -->
            <div x-show="searchQuery" class="space-y-3">
                @php $hasAnyQuestion = false; @endphp
                @foreach($categories as $catKey => $category)
                    @foreach($category['questions'] as $qIndex => $item)
                    @php $uniqueKey = 'search_' . $catKey . '_' . $qIndex; @endphp
                    <div x-show="matchesSearch('{{ addslashes($item['q']) }}', '{{ addslashes($item['a']) }}')"
                         class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-xs transition-all">
                        
                        <div class="flex items-center gap-2 mb-2">
                            <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300">
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

                        <div x-show="isQuestionOpen('{{ $uniqueKey }}')" x-cloak class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800 text-xs sm:text-sm text-slate-600 dark:text-slate-300 whitespace-pre-line leading-relaxed bg-slate-50/60 dark:bg-slate-800/40 p-4 rounded-xl">
                            {{ $item['a'] }}
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
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
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
                                    class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-extrabold text-xs transition cursor-pointer flex items-center gap-1.5">
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
                                : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'">
                            
                            <button type="button" 
                                    @click="toggleQuestion('{{ $uniqueKey }}')"
                                    class="w-full text-left flex items-start justify-between gap-4 cursor-pointer group">
                                <span class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-slate-100 flex items-start gap-2.5">
                                    <span class="w-5 h-5 rounded-md bg-purple-50 dark:bg-purple-950 text-purple-600 dark:text-purple-400 flex items-center justify-center text-[10px] shrink-0 mt-0.5">
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
                                 class="mt-3.5 pt-3.5 border-t border-slate-100 dark:border-slate-800 text-xs sm:text-sm text-slate-700 dark:text-slate-200 whitespace-pre-line leading-relaxed bg-slate-50/70 dark:bg-slate-800/50 p-4 sm:p-5 rounded-xl border border-slate-200/60 dark:border-slate-800/60">
                                {{ $item['a'] }}
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
    <div class="bg-gradient-to-r from-purple-50 via-white to-purple-50 dark:from-slate-900 dark:via-purple-950/20 dark:to-slate-900 border border-purple-200 dark:border-purple-800 rounded-2xl p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
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
            <a href="{{ route('help.tickets.create') }}" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-black text-xs transition flex items-center justify-center gap-2 shadow-xs">
                <i class="fa-solid fa-ticket"></i>
                <span>Nieuw Ticket Aanmaken</span>
            </a>
        </div>
    </div>

</div>
@endsection
