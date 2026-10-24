@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'all', openQuestion: null }">

    <!-- Top Hero Banner (Clean TBS White Style / Dark Mode Ready) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xs relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-1.5 bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 text-xs font-bold px-3 py-1 rounded-full border border-purple-200 dark:border-purple-800/60 mb-2">
                    <i class="fa-solid fa-circle-question"></i> Helpcentrum & Handboek
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Kliniek Kennisbank & FAQ</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-xl">
                    Uitgebreide gids en duidelijke instructies voor alle functies: van profielpersonalisatie en acties tot de krant, ziekenboeg en gang wars.
                </p>
            </div>
            
            <div class="w-full md:w-72">
                <form method="GET" action="{{ route('faq') }}">
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Zoek een vraag of onderwerp..." 
                               class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-2.5 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-600 focus:bg-white dark:focus:bg-slate-800">
                        <button type="submit" class="absolute right-3 top-2.5 text-purple-600 dark:text-purple-400 hover:text-purple-800">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Quick Links Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('vip.index') }}" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-purple-400 dark:hover:border-purple-600 rounded-xl p-3.5 shadow-xs flex items-center gap-3 transition group">
            <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm group-hover:scale-110 transition shrink-0">
                <i class="fa-solid fa-crown"></i>
            </div>
            <div>
                <h4 class="text-xs font-extrabold text-slate-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400">VIP Shop</h4>
                <p class="text-[10px] text-slate-400">100% Gratis via Ads</p>
            </div>
        </a>

        <a href="{{ route('fight.index') }}" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-purple-400 dark:hover:border-purple-600 rounded-xl p-3.5 shadow-xs flex items-center gap-3 transition group">
            <div class="w-9 h-9 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-300 flex items-center justify-center text-sm group-hover:scale-110 transition shrink-0">
                <i class="fa-solid fa-hand-fist"></i>
            </div>
            <div>
                <h4 class="text-xs font-extrabold text-slate-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400">De Luchtplaats</h4>
                <p class="text-[10px] text-slate-400">Max 100 gevechten/dag</p>
            </div>
        </a>

        <a href="{{ route('vault.index') }}" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-purple-400 dark:hover:border-purple-600 rounded-xl p-3.5 shadow-xs flex items-center gap-3 transition group">
            <div class="w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center text-sm group-hover:scale-110 transition shrink-0">
                <i class="fa-solid fa-vault"></i>
            </div>
            <div>
                <h4 class="text-xs font-extrabold text-slate-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400">Kluis-Kraak</h4>
                <p class="text-[10px] text-slate-400">Centrale jackpot kraken</p>
            </div>
        </a>

        <a href="{{ route('settings.account') }}" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-purple-400 dark:hover:border-purple-600 rounded-xl p-3.5 shadow-xs flex items-center gap-3 transition group">
            <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm group-hover:scale-110 transition shrink-0">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <div>
                <h4 class="text-xs font-extrabold text-slate-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400">Beveiliging & 2FA</h4>
                <p class="text-[10px] text-slate-400">Wachtwoord & Sessies</p>
            </div>
        </a>
    </div>

    <!-- FAQ Accordion Sections -->
    <div class="space-y-6">
        @forelse($categories as $catKey => $category)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
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
                <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden transition-all duration-200"
                     :class="{ 'border-purple-400 dark:border-purple-500 ring-1 ring-purple-400/20 bg-purple-50/20 dark:bg-purple-950/20': openQuestion === '{{ $uniqueKey }}' }">
                    <button type="button" 
                            @click="openQuestion = openQuestion === '{{ $uniqueKey }}' ? null : '{{ $uniqueKey }}'"
                            class="w-full px-4 py-3.5 text-left flex items-center justify-between gap-4 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/60 transition">
                        <span class="text-xs font-bold text-slate-900 dark:text-slate-100">
                            {{ $item['q'] }}
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
        @empty
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-10 text-center text-slate-500 dark:text-slate-400 shadow-xs space-y-2">
            <i class="fa-solid fa-magnifying-glass text-2xl text-slate-300 dark:text-slate-600"></i>
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200">Geen artikelen gevonden voor '{{ $search }}'</h3>
            <p class="text-xs text-slate-400 dark:text-slate-500">Probeer een ander zoekwoord of bekijk alle onderwerpen.</p>
            <a href="{{ route('faq') }}" class="inline-block mt-2 px-4 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold transition">
                Reset Zoekopdracht
            </a>
        </div>
        @endforelse
    </div>

</div>
@endsection
