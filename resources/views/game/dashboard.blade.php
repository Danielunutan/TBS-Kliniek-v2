@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

    <!-- Main Content (Left) -->
    <div class="xl:col-span-8 space-y-6">
        
        <!-- Open Beta Notice Card -->
        <div class="bg-white border border-purple-200/90 rounded-2xl p-4 sm:p-5 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-purple-50 border border-purple-200 text-purple-600 flex items-center justify-center text-lg shrink-0 mt-0.5 sm:mt-0 shadow-2xs">
                    <i class="fa-solid fa-flask-vial"></i>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-100 text-purple-800 border border-purple-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-purple-600 animate-pulse"></span>
                            Open Beta Testfase
                        </span>
                        <span class="text-xs font-black text-slate-900">TBS Kliniek de Game is momenteel in Open Beta!</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1 font-medium leading-relaxed">
                        We bouwen elke dag aan nieuwe gameplay en updates. Mocht je fouten, bugs of onduidelijkheden tegenkomen, of heb je goede feedback en suggesties? Maak eenvoudig een ticket aan onder <strong>Helptool</strong> of raadpleeg de Kennisbank.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto shrink-0">
                <a href="{{ route('help.tickets.create') }}" 
                   class="flex-1 sm:flex-none px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-extrabold text-xs transition shadow-xs cursor-pointer inline-flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-plus text-[10px]"></i>
                    <span>Ticket Aanmaken</span>
                </a>
                <a href="{{ route('help.tickets.index') }}" 
                   class="flex-1 sm:flex-none px-3.5 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 font-bold text-xs transition inline-flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-headset text-slate-500"></i>
                    <span>Helpdesk</span>
                </a>
            </div>
        </div>

        <!-- Welcome Hero Card -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden relative">
            <!-- Optional subtle pattern background -->
            <div class="absolute inset-0 bg-slate-50/50"></div>
            
            <div class="relative p-6 sm:p-8 flex flex-col sm:flex-row gap-6 items-center sm:items-start">
                <!-- Large Avatar with level -->
                <div class="relative shrink-0">
                    <img src="{{ $character->avatar_url }}" alt="{{ $character->name }}" class="w-32 h-32 rounded-xl object-cover shadow-md border-4 border-white bg-slate-100">
                    <div class="absolute -bottom-3 -right-3 bg-white p-1 rounded-lg shadow-sm border border-slate-100">
                        <div class="bg-slate-900 text-white font-bold text-xs px-3 py-1.5 rounded-lg flex flex-col items-center leading-none">
                            <span class="text-[9px] text-amber-400 font-bold uppercase">Level</span>
                            <span class="text-lg">{{ $character->level }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex-1 w-full text-center sm:text-left">
                    <h1 class="text-3xl sm:text-4xl font-bold text-slate-900 tracking-tight mb-2">Welkom terug, {{ $character->name }}!</h1>
                    <p class="text-slate-500 font-medium mb-6">De kliniek is zwaar, maar jij bent zwaarder.</p>

                    <!-- XP Bar -->
                    @php
                        $lvlService = app(\App\Services\LevelService::class);
                        $reqXp = $lvlService->getXpRequiredForNextLevel($character->level);
                        $pctXp = min(100, max(0, round(($character->xp / max(1, $reqXp)) * 100)));
                    @endphp
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-end mb-1">
                                <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Level {{ $character->level }}</span>
                                <span class="text-xs font-bold text-slate-900">{{ number_format($character->xp, 0, ',', '.') }} / {{ number_format($reqXp, 0, ',', '.') }} XP</span>
                            </div>
                            <div class="h-2.5 bg-slate-100 rounded-full overflow-hidden border border-slate-200">
                                <div class="h-full bg-purple-600 rounded-full transition-all duration-500" style="width: {{ $pctXp }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Row inside Welcome Card -->
            <div class="bg-slate-50/80 border-t border-slate-100 grid grid-cols-2 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-slate-200">
                <div class="p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shadow-inner">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Contant Geld</p>
                        <p class="text-lg font-bold text-slate-900 leading-none">€ {{ number_format($character->cash, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center shadow-inner">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Banktegoed</p>
                        <p class="text-lg font-bold text-slate-900 leading-none">€ {{ number_format($character->bank, 0, ',', '.') }}</p>
                    </div>
                </div>
                @if($allowanceDetails['eligible'] ?? false)
                    <div class="p-4 flex items-center justify-between gap-3 col-span-2 sm:col-span-1">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shadow-inner">
                                <i class="fa-solid fa-hand-holding-dollar"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">TBS Uitkering</p>
                                <p class="text-lg font-bold text-emerald-600 leading-none">€1.000 <span class="text-xs font-semibold text-slate-400">/ 15m</span></p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Actief</span>
                    </div>
                @else
                    <div class="p-4 flex items-center justify-between gap-3 col-span-2 sm:col-span-1">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center shadow-inner">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Bende Lid</p>
                                <p class="text-base font-bold text-purple-700 leading-none truncate max-w-[120px]">{{ $character->gang?->name ?? 'Gang' }}</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 border border-purple-200">Bende Loon</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- TBS Uitkering Banner for inmates without a gang -->
        @if($allowanceDetails['eligible'] ?? false)
            <div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50 border border-emerald-200 rounded-xl p-4 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <i class="fa-solid fa-hand-holding-dollar text-xl"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-extrabold text-slate-900">TBS Patiënten Uitkering</h3>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">Iedereen zonder bende</span>
                        </div>
                        <p class="text-xs text-slate-600 mt-0.5">
                            Omdat je niet in een bende zit, ontvang je automatisch <strong class="text-emerald-700 font-extrabold">€1.000 per kwartier (15 min)</strong> direct op je <a href="{{ route('bank.index') }}" class="underline font-bold hover:text-emerald-800">bankrekening</a>!
                        </p>
                    </div>
                </div>
                <div class="shrink-0 flex items-center gap-2 bg-white px-3.5 py-2 rounded-lg border border-emerald-200 shadow-xs self-end sm:self-center"
                     x-data="{ 
                         secondsLeft: {{ $allowanceDetails['seconds_until_next'] ?? 900 }},
                         init() {
                             setInterval(() => {
                                 if (this.secondsLeft > 0) {
                                     this.secondsLeft--;
                                 } else {
                                     this.secondsLeft = 900;
                                 }
                             }, 1000);
                         },
                         formatTime() {
                             const m = Math.floor(this.secondsLeft / 60);
                             const s = this.secondsLeft % 60;
                             return `${m}m ${s < 10 ? '0' : ''}${s}s`;
                         }
                     }">
                    <i class="fa-solid fa-hourglass-half text-emerald-600 text-xs"></i>
                    <span class="text-xs text-slate-500 font-medium">Volgende storting:</span>
                    <span class="text-xs font-extrabold text-emerald-700 font-mono" x-text="formatTime()">{{ $allowanceDetails['next_human'] ?? '15m 00s' }}</span>
                </div>
            </div>
        @endif

        <!-- Character Attributes -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="grid grid-cols-3 md:grid-cols-6 gap-4 text-center">
                <div class="flex flex-col items-center justify-center p-3 rounded-lg hover:bg-slate-50 transition">
                    <i class="fa-solid fa-hand-fist text-rose-500 text-xl mb-2"></i>
                    <span class="text-xs font-bold text-slate-500">Kracht</span>
                    <span class="text-lg font-bold text-slate-900">{{ $character->strength }}</span>
                </div>
                <div class="flex flex-col items-center justify-center p-3 rounded-lg hover:bg-slate-50 transition">
                    <i class="fa-solid fa-shield-halved text-indigo-500 text-xl mb-2"></i>
                    <span class="text-xs font-bold text-slate-500">Verdediging</span>
                    <span class="text-lg font-bold text-slate-900">{{ $character->defense }}</span>
                </div>
                <div class="flex flex-col items-center justify-center p-3 rounded-lg hover:bg-slate-50 transition">
                    <i class="fa-solid fa-person-running text-emerald-500 text-xl mb-2"></i>
                    <span class="text-xs font-bold text-slate-500">Conditie</span>
                    <span class="text-lg font-bold text-slate-900">{{ $character->stamina }}</span>
                </div>
                <div class="flex flex-col items-center justify-center p-3 rounded-lg hover:bg-slate-50 transition">
                    <i class="fa-solid fa-brain text-purple-500 text-xl mb-2"></i>
                    <span class="text-xs font-bold text-slate-500">Intelligentie</span>
                    <span class="text-lg font-bold text-slate-900">{{ $character->intelligence ?? 25 }}</span>
                </div>
                <div class="flex flex-col items-center justify-center p-3 rounded-lg hover:bg-slate-50 transition">
                    <i class="fa-solid fa-face-smile-wink text-amber-500 text-xl mb-2"></i>
                    <span class="text-xs font-bold text-slate-500">Charisma</span>
                    <span class="text-lg font-bold text-slate-900">{{ $character->charisma ?? 20 }}</span>
                </div>
                <div class="flex flex-col items-center justify-center p-3 rounded-lg hover:bg-slate-50 transition">
                    <i class="fa-solid fa-scale-balanced text-sky-500 text-xl mb-2"></i>
                    <span class="text-xs font-bold text-slate-500">Discipline</span>
                    <span class="text-lg font-bold text-slate-900">{{ $character->discipline ?? 18 }}</span>
                </div>
            </div>
        </div>

        <!-- Recente activiteiten -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900">Recente activiteiten</h3>
            </div>
            <div class="p-0">
                <div class="divide-y divide-slate-100">
                    <div class="p-4 hover:bg-slate-50 transition flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-check text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-slate-700">Je hebt de missie 'Koffie en Kaas' voltooid!</p>
                            <p class="text-xs font-bold text-emerald-600 mt-1">+ €450 &nbsp; + 120 XP</p>
                        </div>
                        <div class="text-xs text-slate-400 font-semibold whitespace-nowrap">2 uur geleden</div>
                    </div>
                    <div class="p-4 hover:bg-slate-50 transition flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-dumbbell text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-slate-700">Je hebt 1 energie verbruikt bij Training.</p>
                            <p class="text-xs font-bold text-purple-600 mt-1">+ 75 XP</p>
                        </div>
                        <div class="text-xs text-slate-400 font-semibold whitespace-nowrap">3 uur geleden</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Right Sidebar -->
    <div class="xl:col-span-4 space-y-6">

        <!-- Laatste Nieuws Widget (TBS Kliniek Nieuws) -->
        <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 overflow-hidden">
            <div class="px-5 py-3.5 bg-slate-50/70 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs font-black tracking-wide text-slate-900">
                    <span class="w-5 h-5 rounded-md bg-purple-100 text-purple-700 flex items-center justify-center text-[10px] border border-purple-200">
                        <i class="fa-solid fa-newspaper"></i>
                    </span>
                    <span>Laatste nieuws</span>
                </div>
                <span class="text-[10px] font-bold text-purple-700 bg-purple-50 px-2.5 py-0.5 rounded-full border border-purple-200">
                    Live Updates
                </span>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($latestNews as $newsItem)
                <a href="{{ route('news.show', $newsItem->slug) }}" class="p-3.5 px-5 hover:bg-slate-50/80 transition block group">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="text-sm shrink-0 group-hover:scale-110 transition-transform">{{ $newsItem->emoji }}</span>
                            <span class="text-xs font-bold text-slate-800 group-hover:text-purple-700 transition truncate">
                                {{ $newsItem->title }}
                            </span>
                        </div>
                        @if($newsItem->is_pinned)
                            <span class="text-[9px] font-black uppercase text-amber-800 bg-amber-100 px-1.5 py-0.5 rounded-md border border-amber-300 shrink-0">Pin</span>
                        @endif
                    </div>
                    <div class="text-[10px] font-semibold text-slate-400 mt-1 pl-6">
                        {{ $newsItem->created_at->diffForHumans() }}
                    </div>
                </a>
                @empty
                <div class="p-5 text-center text-xs text-slate-400 font-medium">
                    Nog geen nieuwsberichten geplaatst.
                </div>
                @endforelse
            </div>

            <a href="{{ route('news.index') }}" class="block w-full text-center py-2.5 bg-slate-50/70 hover:bg-slate-100 text-purple-700 hover:text-purple-900 font-extrabold text-xs transition border-t border-slate-100 flex items-center justify-center gap-1.5">
                <span>Meer nieuws</span>
                <i class="fa-solid fa-angles-right text-[10px] text-purple-500"></i>
            </a>
        </div>
        
        <!-- Gang Card -->
        <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 overflow-hidden relative group">
            @if($character->gang)
                <div class="p-6 flex flex-col items-center text-center">
                    <div class="w-16 h-16 mb-3 rounded-2xl bg-slate-900 text-amber-400 flex items-center justify-center text-2xl shadow-sm group-hover:scale-105 transition-transform">
                        <i class="fa-solid {{ $character->gang->emblem ?: 'fa-crown' }}"></i>
                    </div>
                    <div class="flex items-center gap-1.5 justify-center flex-wrap">
                        <h3 class="text-base font-bold text-slate-900 mb-0.5">{{ $character->gang->name }}</h3>
                        <span class="text-[10px] font-black uppercase px-1.5 py-0.5 rounded bg-purple-100 text-purple-700">[{{ $character->gang->tag }}]</span>
                    </div>
                    <span class="text-xs font-semibold text-slate-400 mb-4">{{ ucfirst($character->gang_role ?: 'Lid') }}</span>
                    <div class="w-full border-t border-slate-100 pt-3 mb-4 space-y-2 text-xs font-bold">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Leden:</span>
                            <span class="text-slate-900">{{ $character->gang->members()->count() }} actief</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Bendekas:</span>
                            <span class="text-emerald-600 font-extrabold">€{{ number_format($character->gang->treasury, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <a href="{{ route('gangs.index') }}" class="block w-full text-center bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 rounded-xl transition text-xs shadow-xs">
                        Bekijk Mijn Bende <i class="fa-solid fa-chevron-right text-[10px] ml-1"></i>
                    </a>
                </div>
            @else
                <div class="p-6 flex flex-col items-center text-center">
                    <div class="w-16 h-16 mb-3 rounded-2xl bg-purple-50 text-purple-600 border border-purple-100 flex items-center justify-center text-2xl shadow-2xs group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mb-0.5">Maak Gang of word lid van een gang</h3>
                    <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                        Alleen sta je zwak in de TBS kliniek. Sluit je aan bij een factie of richt je eigen bende op om samen te heersen!
                    </p>
                    <div class="w-full border-t border-slate-100 pt-3 mb-4 flex justify-between items-center text-xs font-bold">
                        <span class="text-slate-500">Status:</span>
                        <span class="text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">Geen Bende</span>
                    </div>
                    <a href="{{ route('gangs.index') }}" class="block w-full text-center bg-purple-600 hover:bg-purple-700 text-white font-bold py-2.5 rounded-xl transition text-xs shadow-xs">
                        Maak Gang of Word Lid <i class="fa-solid fa-chevron-right text-[10px] ml-1"></i>
                    </a>
                </div>
            @endif
        </div>

        <!-- Actieve Missies -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900">Actieve Missies</h3>
            </div>
            <div class="p-0">
                <div class="divide-y divide-slate-100">
                    <div class="p-5 flex items-center gap-4 hover:bg-slate-50 transition">
                        <img src="{{ asset('assets/characters/henkie.jpg') }}" class="w-12 h-12 rounded-lg object-cover shadow-sm">
                        <div class="flex-1">
                            <h4 class="text-sm font-bold text-slate-800">Koffie en Kaas</h4>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wide">0/500</p>
                        </div>
                        <a href="{{ route('missions.index') }}" class="bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold px-4 py-2 rounded-lg transition shadow-sm whitespace-nowrap">
                            Naar missie
                        </a>
                    </div>
                    <div class="p-5 flex items-center gap-4 hover:bg-slate-50 transition">
                        <img src="{{ asset('assets/login-bg.jpg') }}" class="w-12 h-12 rounded-lg object-cover shadow-sm">
                        <div class="flex-1">
                            <h4 class="text-sm font-bold text-slate-800">De Eerste Diefstal</h4>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wide">0/250</p>
                        </div>
                        <a href="{{ route('missions.index') }}" class="bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold px-4 py-2 rounded-lg transition shadow-sm whitespace-nowrap">
                            Naar missie
                        </a>
                    </div>
                    <div class="p-5 flex items-center gap-4 hover:bg-slate-50 transition">
                        <img src="{{ asset('assets/daily_training.jpg') }}" alt="Dagelijkse Training" class="w-12 h-12 rounded-lg object-cover shadow-sm">
                        <div class="flex-1">
                            <h4 class="text-sm font-bold text-slate-800">Dagelijkse Training</h4>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wide">1/2</p>
                        </div>
                        <a href="{{ route('training.index') }}" class="bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold px-4 py-2 rounded-lg transition shadow-sm whitespace-nowrap">
                            Naar missie
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
