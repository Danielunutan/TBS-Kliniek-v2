@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    activeCategory: 'all',
    openQuestion: null,
    searchQuery: '{{ addslashes($search) }}',
    calcStrength: 25,
    calcOppDefense: 20,
    get winChance() {
        const ratio = this.calcStrength / Math.max(1, (this.calcStrength + this.calcOppDefense));
        return Math.min(95, Math.max(10, Math.round(ratio * 100)));
    },
    filterCategory(cat) {
        this.activeCategory = cat;
        this.openQuestion = null;
    },
    isQuestionVisible(catKey, qText, aText) {
        if (this.activeCategory !== 'all' && this.activeCategory !== catKey) {
            return false;
        }
        if (!this.searchQuery) return true;
        const q = this.searchQuery.toLowerCase();
        return qText.toLowerCase().includes(q) || aText.toLowerCase().includes(q);
    }
}">

    <!-- Top Hero Banner (Rich Darkmode & Clean TBS Styling) -->
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
                    Vind direct antwoorden op al je vragen over geld verdienen, vechten, de isoleercel, smokkel en bendes. Gebruik de live zoekbalk of de interactieve hulpmiddelen hieronder!
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
                    <span>Directe live filtering actief</span>
                    <button type="button" x-show="searchQuery" @click="searchQuery = ''" class="hover:underline text-amber-300 cursor-pointer">
                        Wis filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 1. "IK WIL..." SNELLE PROBLEEMOPLOSSER (4 Directe Kaarten) -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2">
                <i class="fa-solid fa-bolt text-amber-500"></i>
                <span>Snelle Probleemoplosser voor Gevangenen</span>
            </h2>
            <span class="text-[11px] text-slate-400">Klik op een situatie voor directe hulp</span>
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
                <div x-show="activeSolver === 'energie'" x-collapse class="pt-2 text-xs text-slate-600 dark:text-slate-300 space-y-2 border-t border-slate-100 dark:border-slate-800">
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
                <div x-show="activeSolver === 'bank'" x-collapse class="pt-2 text-xs text-slate-600 dark:text-slate-300 space-y-2 border-t border-slate-100 dark:border-slate-800">
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
                <div x-show="activeSolver === 'gevangenis'" x-collapse class="pt-2 text-xs text-slate-600 dark:text-slate-300 space-y-2 border-t border-slate-100 dark:border-slate-800">
                    <p>• <strong>15s Video:</strong> Elke advertentie haalt 15 minuten van je straftijd af!</p>
                    <p>• <strong>Vrienden:</strong> Vraag een mede-gedetineerde om je celdeur te forceren.</p>
                    <p>• <strong>Borg:</strong> Betaal 2 Sterren om per seconde vrij te komen.</p>
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
                <div x-show="activeSolver === 'doel'" x-collapse class="pt-2 text-xs text-slate-600 dark:text-slate-300 space-y-2 border-t border-slate-100 dark:border-slate-800">
                    <p>• <strong>Respectlijst:</strong> Strijd om de #1 positie van de kliniek.</p>
                    <p>• <strong>24-Uurs Battle:</strong> Doe dagelijks mee aan de grote afdelingsslag.</p>
                    <p>• <strong>Bendes:</strong> Richt je eigen clan op, hef bende-uurloon en domineer territoria.</p>
                    <a href="{{ route('leaderboard') }}" class="inline-block mt-1 px-3 py-1 bg-purple-600 text-white rounded-lg font-bold text-[10px]">Respectlijst Bekijken &raquo;</a>
                </div>
            </div>

        </div>
    </div>

    <!-- 2. INTERACTIEVE GEVECHT & STATS CALCULATOR -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-100 dark:bg-rose-950/70 text-rose-600 dark:text-rose-400 flex items-center justify-center text-base font-black shadow-xs">
                    <i class="fa-solid fa-calculator"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider font-['Outfit']">
                        Interactieve Gevecht Simulator
                    </h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        Ontdek hoe jouw Kracht en de Verdediging van een tegenstander jouw winstkans bepalen op de Luchtplaats.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-black px-3 py-1 rounded-full border"
                      :class="winChance >= 60 ? 'bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border-emerald-300' : (winChance >= 40 ? 'bg-amber-50 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border-amber-300' : 'bg-rose-50 dark:bg-rose-950 text-rose-700 dark:text-rose-300 border-rose-300')">
                    Berekende Winstkans: <span x-text="winChance + '%'"></span>
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
            <!-- Slider 1: Jouw Kracht -->
            <div class="space-y-2 bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-200/80 dark:border-slate-700">
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
            <div class="space-y-2 bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-200/80 dark:border-slate-700">
                <div class="flex justify-between items-center text-xs">
                    <span class="font-extrabold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-indigo-500"></i> Tegenstander Verdediging
                    </span>
                    <span class="font-mono font-black text-indigo-600 text-sm" x-text="calcOppDefense"></span>
                </div>
                <input type="range" min="10" max="150" step="5" x-model.number="calcOppDefense" class="w-full accent-indigo-600 cursor-pointer">
                <p class="text-[10px] text-slate-400">Kogelwerende vesten en uitrusting verhogen de verdediging.</p>
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

    <!-- 3. THEMA CATEGORIE FILTER PILLS -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
        <button type="button" 
                @click="filterCategory('all')"
                :class="activeCategory === 'all' ? 'bg-purple-600 text-white shadow-xs' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800'"
                class="px-4 py-2 rounded-xl text-xs font-black transition cursor-pointer shrink-0 flex items-center gap-1.5">
            <i class="fa-solid fa-layer-group text-[11px]"></i>
            <span>Alle Vragen</span>
        </button>

        @foreach($categories as $catKey => $category)
        <button type="button" 
                @click="filterCategory('{{ $catKey }}')"
                :class="activeCategory === '{{ $catKey }}' ? 'bg-purple-600 text-white shadow-xs' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800'"
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer shrink-0 flex items-center gap-1.5">
            <i class="fa-solid {{ $category['icon'] }} text-[11px]"></i>
            <span>{{ $category['title'] }}</span>
        </button>
        @endforeach
    </div>

    <!-- 4. FAQ ACCORDION SECTIONS (WITH REAL-TIME CLIENT-SIDE FILTER) -->
    <div class="space-y-6">
        @foreach($categories as $catKey => $category)
        <div x-show="activeCategory === 'all' || activeCategory === '{{ $catKey }}'"
             class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4 transition-all">
            
            <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 flex items-center justify-center text-sm shrink-0">
                    <i class="fa-solid {{ $category['icon'] }}"></i>
                </div>
                <h3 class="text-sm font-black uppercase tracking-wide text-slate-900 dark:text-white">
                    {{ $category['title'] }}
                </h3>
            </div>

            <div class="space-y-3">
                @foreach($category['questions'] as $qIndex => $item)
                @php $uniqueKey = $catKey . '_' . $qIndex; @endphp
                <div x-show="isQuestionVisible('{{ $catKey }}', '{{ addslashes($item['q']) }}', '{{ addslashes($item['a']) }}')"
                     class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden transition-all duration-200"
                     :class="{ 'border-purple-400 dark:border-purple-500 ring-1 ring-purple-400/20 bg-purple-50/20 dark:bg-purple-950/20': openQuestion === '{{ $uniqueKey }}' }">
                    <button type="button" 
                            @click="openQuestion = openQuestion === '{{ $uniqueKey }}' ? null : '{{ $uniqueKey }}'"
                            class="w-full px-4 py-3.5 text-left flex items-center justify-between gap-4 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/60 transition">
                        <span class="text-xs font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-circle-question text-purple-500/80 text-[11px]"></i>
                            <span>{{ $item['q'] }}</span>
                        </span>
                        <i class="fa-solid fa-chevron-down text-slate-400 text-xs transition-transform duration-200 shrink-0"
                           :class="{ 'rotate-180 text-purple-600 dark:text-purple-400': openQuestion === '{{ $uniqueKey }}' }"></i>
                    </button>
                    
                    <div x-show="openQuestion === '{{ $uniqueKey }}'" x-cloak class="px-4 pb-4 pt-1 text-xs text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100/60 dark:border-slate-800/60 bg-white dark:bg-slate-900">
                        {{ $item['a'] }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    <!-- Need More Help Banner -->
    <div class="bg-gradient-to-r from-purple-50 via-white to-purple-50 dark:from-slate-900 dark:via-purple-950/20 dark:to-slate-900 border border-purple-200 dark:border-purple-800 rounded-2xl p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-purple-600 text-white flex items-center justify-center text-xl shrink-0 shadow-md">
                <i class="fa-solid fa-headset"></i>
            </div>
            <div>
                <h4 class="text-sm font-black text-slate-900 dark:text-white">Niet gevonden wat je zocht?</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400">Stel je vraag in de Live Chat of open een officieel ticket bij het beheerdersteam.</p>
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
