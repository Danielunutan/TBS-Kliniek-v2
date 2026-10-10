@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'all', openQuestion: null }">

    <!-- Top Hero Banner -->
    <div class="bg-slate-900 border border-purple-500/30 rounded-2xl p-6 sm:p-8 text-white relative overflow-hidden shadow-lg">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-1.5 bg-purple-500/20 text-purple-300 text-xs font-bold px-3 py-1 rounded-full border border-purple-400/20 mb-2">
                    <i class="fa-solid fa-circle-question"></i> Helpcentrum & Documentatie
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Kliniek Kennisbank & FAQ</h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-xl">
                    Volledige uitleg over alle spelonderdelen, gevechtsregels, VIP-voordelen, beveiliging en wachtwoordherstel.
                </p>
            </div>
            
            <div class="w-full md:w-72">
                <form method="GET" action="{{ route('faq') }}">
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Zoek een vraag of onderwerp..." 
                               class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:bg-white/15">
                        <button type="submit" class="absolute right-3 top-2.5 text-purple-300 hover:text-white">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Quick Links Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('vip.index') }}" class="bg-white border border-slate-200 hover:border-purple-400 rounded-xl p-3.5 shadow-xs flex items-center gap-3 transition group">
            <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm group-hover:scale-110 transition">
                <i class="fa-solid fa-crown"></i>
            </div>
            <div>
                <h4 class="text-xs font-extrabold text-slate-900 group-hover:text-purple-600">VIP Shop</h4>
                <p class="text-[10px] text-slate-400">€7,50 pm via Discord</p>
            </div>
        </a>

        <a href="{{ route('fight.index') }}" class="bg-white border border-slate-200 hover:border-purple-400 rounded-xl p-3.5 shadow-xs flex items-center gap-3 transition group">
            <div class="w-9 h-9 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-sm group-hover:scale-110 transition">
                <i class="fa-solid fa-hand-fist"></i>
            </div>
            <div>
                <h4 class="text-xs font-extrabold text-slate-900 group-hover:text-purple-600">De Luchtplaats</h4>
                <p class="text-[10px] text-slate-400">Max 100 gevechten/dag</p>
            </div>
        </a>

        <a href="{{ route('vault.index') }}" class="bg-white border border-slate-200 hover:border-purple-400 rounded-xl p-3.5 shadow-xs flex items-center gap-3 transition group">
            <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm group-hover:scale-110 transition">
                <i class="fa-solid fa-vault"></i>
            </div>
            <div>
                <h4 class="text-xs font-extrabold text-slate-900 group-hover:text-purple-600">Kluis-Kraak</h4>
                <p class="text-[10px] text-slate-400">Centrale jackpot kraken</p>
            </div>
        </a>

        <a href="{{ route('settings.account') }}" class="bg-white border border-slate-200 hover:border-purple-400 rounded-xl p-3.5 shadow-xs flex items-center gap-3 transition group">
            <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm group-hover:scale-110 transition">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <div>
                <h4 class="text-xs font-extrabold text-slate-900 group-hover:text-purple-600">Beveiliging & 2FA</h4>
                <p class="text-[10px] text-slate-400">Wachtwoord & Remote sessies</p>
            </div>
        </a>
    </div>

    <!-- FAQ Accordion Sections -->
    <div class="space-y-6">
        @forelse($categories as $catKey => $category)
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-sm">
                    <i class="fa-solid {{ $category['icon'] }}"></i>
                </div>
                <h3 class="text-sm font-extrabold uppercase tracking-wide text-slate-900">
                    {{ $category['title'] }}
                </h3>
            </div>

            <div class="space-y-3">
                @foreach($category['questions'] as $qIndex => $item)
                @php $uniqueKey = $catKey . '_' . $qIndex; @endphp
                <div class="border border-slate-200 rounded-xl overflow-hidden transition-all duration-200"
                     :class="{ 'border-purple-400 ring-1 ring-purple-400/20 bg-purple-50/20': openQuestion === '{{ $uniqueKey }}' }">
                    <button type="button" 
                            @click="openQuestion = openQuestion === '{{ $uniqueKey }}' ? null : '{{ $uniqueKey }}'"
                            class="w-full px-4 py-3.5 text-left flex items-center justify-between gap-4 cursor-pointer hover:bg-slate-50 transition">
                        <span class="text-xs font-extrabold text-slate-900">
                            {{ $item['q'] }}
                        </span>
                        <i class="fa-solid fa-chevron-down text-slate-400 text-xs transition-transform duration-200"
                           :class="{ 'rotate-180 text-purple-600': openQuestion === '{{ $uniqueKey }}' }"></i>
                    </button>
                    
                    <div x-show="openQuestion === '{{ $uniqueKey }}'" x-cloak class="px-4 pb-4 pt-1 text-xs text-slate-600 leading-relaxed border-t border-slate-100/60 bg-white">
                        {{ $item['a'] }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="bg-white border border-slate-200 rounded-2xl p-10 text-center text-slate-500 shadow-xs space-y-2">
            <i class="fa-solid fa-magnifying-glass text-2xl text-slate-300"></i>
            <h3 class="text-sm font-bold text-slate-700">Geen artikelen gevonden voor '{{ $search }}'</h3>
            <p class="text-xs text-slate-400">Probeer een ander zoekwoord of bekijk alle onderwerpen.</p>
            <a href="{{ route('faq') }}" class="inline-block mt-2 px-4 py-1.5 rounded-lg bg-purple-600 text-white text-xs font-bold">
                Reset Zoekopdracht
            </a>
        </div>
        @endforelse
    </div>

</div>
@endsection
