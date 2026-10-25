@extends('layouts.app')

@section('content')
<div x-data="tbsDashboardCustomizer()" 
     x-init="init()"
     class="space-y-6"
     :class="{
         'theme-ebi': theme === 'ebi',
         'theme-vip': theme === 'vip',
         'theme-cyber': theme === 'cyber',
         'theme-orange': theme === 'orange'
     }">

    <!-- Top Dashboard Header with Customizer Trigger -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pb-1">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-base shadow-xs"
                 :class="{
                     'bg-purple-600': theme === 'clinical',
                     'bg-rose-600 shadow-rose-500/20': theme === 'ebi',
                     'bg-amber-500 text-slate-950 shadow-amber-500/20': theme === 'vip',
                     'bg-cyan-500 shadow-cyan-500/20': theme === 'cyber',
                     'bg-orange-600 shadow-orange-500/20': theme === 'orange'
                 }">
                <i class="fa-solid fa-gauge-high"></i>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                    <span>Mijn Dashboard</span>
                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full border"
                          :class="{
                              'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-800': theme === 'clinical',
                              'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800': theme === 'ebi',
                              'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800': theme === 'vip',
                              'bg-cyan-50 text-cyan-700 border-cyan-200 dark:bg-cyan-950/60 dark:text-cyan-300 dark:border-cyan-800': theme === 'cyber',
                              'bg-orange-50 text-orange-800 border-orange-200 dark:bg-orange-950/60 dark:text-orange-300 dark:border-orange-800': theme === 'orange'
                          }"
                          x-text="currentThemeName()"></span>
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Beheer jouw patiëntencel, dagtaken, buit en kliniekactiviteiten.</p>
            </div>
        </div>

        <!-- Customizer Button -->
        <button type="button" 
                @click="showCustomizer = true"
                class="px-4 py-2 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 hover:border-purple-400 dark:hover:border-purple-500 text-slate-700 dark:text-slate-200 hover:text-purple-600 dark:hover:text-purple-300 font-bold text-xs transition shadow-2xs flex items-center gap-2 cursor-pointer active:scale-95">
            <i class="fa-solid fa-sliders text-purple-600 dark:text-purple-400"></i>
            <span>Dashboard Inrichten</span>
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
        </button>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

        <!-- Main Content (Left) -->
        <div class="xl:col-span-8 space-y-6">
            
            <!-- Open Beta Notice Card (Toggleable) -->
            <div x-show="widgets.betaNotice" 
                 x-transition
                 class="bg-white dark:bg-[#111827] border border-purple-200/90 dark:border-purple-900/60 rounded-2xl p-4 sm:p-5 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 relative">
                <button type="button" 
                        @click="toggleWidget('betaNotice')" 
                        class="absolute top-2 right-2 text-slate-400 hover:text-slate-600 text-xs w-6 h-6 flex items-center justify-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800"
                        title="Verberg deze melding">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <div class="flex items-start gap-3.5 pr-6 sm:pr-0">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200 dark:border-purple-800 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg shrink-0 mt-0.5 sm:mt-0 shadow-2xs">
                        <i class="fa-solid fa-flask-vial"></i>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-100 dark:bg-purple-950 text-purple-800 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-600 animate-pulse"></span>
                                Open Beta Testfase
                            </span>
                            <span class="text-xs font-black text-slate-900 dark:text-white">TBS Kliniek de Game is momenteel in Open Beta!</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium leading-relaxed">
                            We bouwen elke dag aan nieuwe gameplay en updates. Mocht je fouten, bugs of suggesties hebben? Maak eenvoudig een ticket aan onder <strong>Helptool</strong>.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto shrink-0">
                    <a href="{{ route('help.tickets.create') }}" 
                       class="flex-1 sm:flex-none px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-extrabold text-xs transition shadow-xs cursor-pointer inline-flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-plus text-[10px]"></i>
                        <span>Ticket Aanmaken</span>
                    </a>
                </div>
            </div>

            <!-- Roadmap & Updates Teaser Card (Toggleable) -->
            <div x-show="widgets.roadmap" 
                 x-transition
                 class="bg-white dark:bg-[#111827] rounded-2xl p-4 sm:p-5 shadow-xs border border-purple-200/90 dark:border-purple-900/60 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 relative">
                <button type="button" 
                        @click="toggleWidget('roadmap')" 
                        class="absolute top-2 right-2 text-slate-400 hover:text-slate-600 text-xs w-6 h-6 flex items-center justify-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800"
                        title="Verberg deze melding">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <div class="flex items-start gap-3.5 pr-6 sm:pr-0">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200 dark:border-purple-800 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg shrink-0 mt-0.5 sm:mt-0 shadow-2xs">
                        <i class="fa-solid fa-compass"></i>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-100 dark:bg-purple-950 text-purple-800 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                10 Grote Updates
                            </span>
                            <span class="text-xs font-black text-slate-900 dark:text-white">Toekomst van de Kliniek: Stem mee op nieuwe functies!</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium leading-relaxed">
                            Van de <strong>Medicijnkluis Gang Heist</strong> en <strong>Armpjedruk Arena</strong> tot het <strong>Behandelaarsgesprek</strong> en de <strong>Peukeneconomie</strong>.
                        </p>
                    </div>
                </div>

                <a href="{{ route('roadmap.index') }}" 
                   class="w-full sm:w-auto px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 active:scale-95 text-white font-extrabold text-xs transition shadow-xs inline-flex items-center justify-center gap-2 shrink-0">
                    <i class="fa-solid fa-thumbs-up text-amber-400 text-xs"></i>
                    <span>Bekijk & Stem Nu</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <!-- Welcome Hero Card (Themed) -->
            <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-xs border transition-all duration-300 overflow-hidden relative"
                 :class="{
                     'border-slate-200/90 dark:border-slate-800': theme === 'clinical',
                     'border-rose-300/80 dark:border-rose-900/60 shadow-rose-950/10': theme === 'ebi',
                     'border-amber-300/80 dark:border-amber-800/60 shadow-amber-950/10': theme === 'vip',
                     'border-cyan-300/80 dark:border-cyan-900/60 shadow-cyan-950/10': theme === 'cyber',
                     'border-orange-300/80 dark:border-orange-900/60 shadow-orange-950/10': theme === 'orange'
                 }">
                
                <div class="relative p-6 sm:p-7 flex flex-col sm:flex-row gap-6 items-center sm:items-start">
                    <!-- Large Avatar with level -->
                    <div class="relative shrink-0">
                        <img src="{{ $character->avatar_url }}" alt="{{ $character->name }}" class="w-28 h-28 sm:w-32 sm:h-32 rounded-2xl object-cover shadow-md border-4 border-white dark:border-slate-800 bg-slate-100 dark:bg-slate-800">
                        <div class="absolute -bottom-2.5 -right-2.5 bg-white dark:bg-slate-800 p-1 rounded-xl shadow-xs border border-slate-100 dark:border-slate-700">
                            <div class="text-white font-black text-xs px-2.5 py-1 rounded-lg flex flex-col items-center leading-none"
                                 :class="{
                                     'bg-purple-600': theme === 'clinical',
                                     'bg-rose-600': theme === 'ebi',
                                     'bg-amber-500 text-slate-950': theme === 'vip',
                                     'bg-cyan-600': theme === 'cyber',
                                     'bg-orange-600': theme === 'orange'
                                 }">
                                <span class="text-[8px] font-black uppercase opacity-90">Level</span>
                                <span class="text-base">{{ $character->level }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 w-full text-center sm:text-left">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-1">
                            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Welkom terug, {{ $character->name }}!</h2>
                            <span class="text-xs font-bold text-slate-400">Patiënt #{{ $character->id }}</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mb-5">
                            De kliniek is streng beveiligd, maar jij bouwt hier stap voor stap jouw eigen imperium op.
                        </p>

                        <!-- XP Bar -->
                        @php
                            $lvlService = app(\App\Services\LevelService::class);
                            $reqXp = $lvlService->getXpRequiredForNextLevel($character->level);
                            $pctXp = min(100, max(0, round(($character->xp / max(1, $reqXp)) * 100)));
                        @endphp
                        <div class="space-y-1.5 mb-5">
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fa-solid fa-shield-halved text-purple-600 dark:text-purple-400 text-[11px]"></i>
                                    <span>Level {{ $character->level }} Voortgang</span>
                                </span>
                                <span class="font-mono font-bold text-slate-800 dark:text-slate-200">
                                    {{ number_format($character->xp, 0, ',', '.') }} / {{ number_format($reqXp, 0, ',', '.') }} XP ({{ $pctXp }}%)
                                </span>
                            </div>
                            <div class="h-2.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden border border-slate-200 dark:border-slate-700">
                                <div class="h-full rounded-full transition-all duration-500" 
                                     :class="{
                                         'bg-purple-600': theme === 'clinical',
                                         'bg-rose-600': theme === 'ebi',
                                         'bg-amber-500': theme === 'vip',
                                         'bg-cyan-500': theme === 'cyber',
                                         'bg-orange-500': theme === 'orange'
                                     }"
                                     style="width: {{ $pctXp }}%"></div>
                            </div>
                        </div>

                        <!-- Saldo & Gang Info Directly Inside Hero Card -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-left">
                            <!-- Contant Saldo -->
                            <a href="{{ route('bank.index') }}" class="bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl p-3 hover:border-purple-400 dark:hover:border-purple-500 transition group block shadow-2xs">
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="w-6 h-6 rounded-md bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-[10px]">
                                        <i class="fa-solid fa-coins"></i>
                                    </div>
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-400">Contant</span>
                                </div>
                                <div class="text-sm sm:text-base font-black text-slate-900 dark:text-white truncate">
                                    €{{ number_format($character->cash, 0, ',', '.') }}
                                </div>
                            </a>

                            <!-- Bank Saldo -->
                            <a href="{{ route('bank.index') }}" class="bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl p-3 hover:border-indigo-400 dark:hover:border-indigo-500 transition group block shadow-2xs">
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="w-6 h-6 rounded-md bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-[10px]">
                                        <i class="fa-solid fa-building-columns"></i>
                                    </div>
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-400">Bank</span>
                                </div>
                                <div class="text-sm sm:text-base font-black text-slate-900 dark:text-white truncate">
                                    €{{ number_format($character->bank, 0, ',', '.') }}
                                </div>
                            </a>

                            <!-- Bende / Gang Info -->
                            <a href="{{ route('gangs.index') }}" class="bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl p-3 hover:border-purple-400 dark:hover:border-purple-500 transition group block shadow-2xs">
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="w-6 h-6 rounded-md bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-[10px]">
                                        <i class="fa-solid fa-users"></i>
                                    </div>
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-400">Bende</span>
                                </div>
                                <div class="text-sm font-black text-purple-700 dark:text-purple-300 truncate">
                                    {{ $character->gang ? $character->gang->name : 'Geen Bende' }}
                                </div>
                            </a>

                            <!-- TBS Uitkering -->
                            <div class="bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl p-3 shadow-2xs">
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="w-6 h-6 rounded-md bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-[10px]">
                                        <i class="fa-solid fa-hand-holding-dollar"></i>
                                    </div>
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-400">Uitkering</span>
                                </div>
                                <div class="text-sm sm:text-base font-black text-emerald-600 dark:text-emerald-400 truncate">
                                    €1.000 <span class="text-[10px] font-bold text-slate-400">/ 15m</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAVORITE QUICK ACTIONS TOOLBAR (Customizable by player) -->
            <div x-show="widgets.quickToolbar" 
                 x-transition
                 class="bg-white dark:bg-[#111827] rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-200/90 dark:border-slate-800">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        <i class="fa-solid fa-bolt-lightning text-amber-500"></i>
                        <span>Mijn Snelle Bajes Acties</span>
                    </div>
                    <button type="button" @click="activeCustomizerTab = 'actions'; showCustomizer = true" class="text-[11px] font-bold text-purple-600 dark:text-purple-400 hover:underline cursor-pointer">
                        Aanpassen (6 gekozen) &rarr;
                    </button>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2.5">
                    <template x-for="act in activeActionItems()" :key="act.id">
                        <a :href="act.url" 
                           class="p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700 bg-slate-50/70 dark:bg-slate-800/60 hover:bg-white dark:hover:bg-slate-800 transition flex flex-col items-center text-center gap-2 group shadow-2xs">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm shadow-xs transition-transform group-hover:scale-110"
                                 :class="act.color">
                                <i class="fa-solid" :class="act.icon"></i>
                            </div>
                            <span class="text-[11px] font-bold text-slate-800 dark:text-slate-200 truncate w-full" x-text="act.label"></span>
                        </a>
                    </template>
                </div>
            </div>

            <!-- IN-DASHBOARD RADIO KLINIEK FM WIDGET (Toggleable) -->
            <div x-show="widgets.radio" 
                 x-transition
                 class="bg-white dark:bg-[#111827] rounded-2xl p-5 shadow-xs border border-purple-200/90 dark:border-purple-900/60 relative overflow-hidden"
                 x-data="{
                     playStation(stId) {
                         if (window.tbsRadio) {
                             window.tbsRadio.changeStation(stId, true);
                         } else {
                             window.dispatchEvent(new CustomEvent('tbs-play-station', { detail: { id: stId } }));
                         }
                     }
                 }">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200 dark:border-purple-800 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl shrink-0 shadow-2xs">
                            <i class="fa-solid fa-radio"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 animate-pulse">
                                    Live Audio Stream
                                </span>
                                <span class="text-xs font-black text-slate-900 dark:text-white">Radio Kliniek FM</span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Luister live naar je favoriete zenders tijdens het spelen op de hele website!
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('radio.index') }}" class="text-xs font-bold text-purple-600 dark:text-purple-400 hover:underline inline-flex items-center gap-1.5 self-start sm:self-center">
                        <i class="fa-solid fa-microphone-lines"></i>
                        <span>Shoutout Sturen (€150)</span>
                    </a>
                </div>

                <!-- Station Quick Pick Buttons -->
                <div class="pt-4 grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-2">
                    <button type="button" @click="playStation('funx')" class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-rose-400 bg-slate-50 dark:bg-slate-800/80 text-left transition cursor-pointer group">
                        <span class="w-5 h-5 rounded-md bg-rose-500 text-white text-[9px] font-black flex items-center justify-center mb-1">FX</span>
                        <span class="text-xs font-black text-slate-900 dark:text-white block truncate">FunX</span>
                        <span class="text-[9px] text-slate-400 block truncate">Hip Hop & Urban</span>
                    </button>

                    <button type="button" @click="playStation('slam')" class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-orange-400 bg-slate-50 dark:bg-slate-800/80 text-left transition cursor-pointer group">
                        <span class="w-5 h-5 rounded-md bg-orange-500 text-white text-[9px] font-black flex items-center justify-center mb-1">SL</span>
                        <span class="text-xs font-black text-slate-900 dark:text-white block truncate">SLAM!</span>
                        <span class="text-[9px] text-slate-400 block truncate">Club & Beats</span>
                    </button>

                    <button type="button" @click="playStation('slam00s')" class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-amber-400 bg-slate-50 dark:bg-slate-800/80 text-left transition cursor-pointer group">
                        <span class="w-5 h-5 rounded-md bg-amber-500 text-white text-[9px] font-black flex items-center justify-center mb-1">00s</span>
                        <span class="text-xs font-black text-slate-900 dark:text-white block truncate">SLAM! 00s</span>
                        <span class="text-[9px] text-slate-400 block truncate">Throwbacks</span>
                    </button>

                    <button type="button" @click="playStation('radio2')" class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-red-400 bg-slate-50 dark:bg-slate-800/80 text-left transition cursor-pointer group">
                        <span class="w-5 h-5 rounded-md bg-red-600 text-white text-[9px] font-black flex items-center justify-center mb-1">R2</span>
                        <span class="text-xs font-black text-slate-900 dark:text-white block truncate">Radio 2</span>
                        <span class="text-[9px] text-slate-400 block truncate">Pop & Rock</span>
                    </button>

                    <button type="button" @click="playStation('3fm')" class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-emerald-400 bg-slate-50 dark:bg-slate-800/80 text-left transition cursor-pointer group">
                        <span class="w-5 h-5 rounded-md bg-emerald-600 text-white text-[9px] font-black flex items-center justify-center mb-1">3FM</span>
                        <span class="text-xs font-black text-slate-900 dark:text-white block truncate">3FM</span>
                        <span class="text-[9px] text-slate-400 block truncate">Hits & Nieuw</span>
                    </button>

                    <button type="button" @click="playStation('skyradio')" class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-sky-400 bg-slate-50 dark:bg-slate-800/80 text-left transition cursor-pointer group">
                        <span class="w-5 h-5 rounded-md bg-sky-500 text-white text-[9px] font-black flex items-center justify-center mb-1">SKY</span>
                        <span class="text-xs font-black text-slate-900 dark:text-white block truncate">Sky Radio</span>
                        <span class="text-[9px] text-slate-400 block truncate">Non-stop</span>
                    </button>

                    <button type="button" @click="playStation('radio538')" class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-emerald-400 bg-slate-50 dark:bg-slate-800/80 text-left transition cursor-pointer group">
                        <span class="w-5 h-5 rounded-md bg-emerald-500 text-white text-[9px] font-black flex items-center justify-center mb-1">538</span>
                        <span class="text-xs font-black text-slate-900 dark:text-white block truncate">Radio 538</span>
                        <span class="text-[9px] text-slate-400 block truncate">Top 40</span>
                    </button>
                </div>
            </div>

            <!-- AFDELINGSLOTERIJ JACKPOT PROMO WIDGET (Toggleable) -->
            @if(isset($lotteryRound))
            <div x-show="widgets.lottery" 
                 x-transition
                 class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-5 sm:p-6 text-slate-900 dark:text-white shadow-xs relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-5">
                <div class="space-y-2 max-w-xl">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-700/60">
                            <i class="fa-solid fa-crown text-amber-500"></i> Afdelingsloterij Ronde #{{ $lotteryRound->round_number }}
                        </span>
                        <span class="text-[10px] font-bold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full border border-slate-200 dark:border-slate-700">
                            1x per 14 dagen
                        </span>
                    </div>
                    <h2 class="text-lg sm:text-xl font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                        Grijp de Jackpot: <span class="text-amber-600 dark:text-amber-400 font-mono font-black">€{{ number_format($lotteryRound->jackpot, 0, ',', '.') }}</span>
                    </h2>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-medium">
                        Iedere cent opnamebelasting van de bank stroomt rechtstreeks in deze pot. Koop loten met contant geld of sterren en maak kans op de volledige pot bij de trekking!
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 shrink-0 self-stretch md:self-center">
                    @php
                        $dashSecondsLeft = max(0, (int) round(now()->diffInSeconds($lotteryRound->draws_at, false)));
                    @endphp
                    <div class="w-full sm:w-auto p-3 rounded-xl bg-amber-50/70 dark:bg-slate-800 border border-amber-200 dark:border-slate-700 text-center shrink-0"
                         x-data="{
                             left: {{ $dashSecondsLeft }},
                             format() {
                                 if (this.left <= 0) return 'Trekking!';
                                 const t = Math.floor(this.left);
                                 const d = Math.floor(t / 86400);
                                 const h = Math.floor((t % 86400) / 3600);
                                 const m = Math.floor((t % 3600) / 60);
                                 const s = Math.floor(t % 60);
                                 return d > 0 ? `${d}d ${h}u ${m}m ${s < 10 ? '0' : ''}${s}s` : `${h}u ${m}m ${s < 10 ? '0' : ''}${s}s`;
                             },
                             init() { setInterval(() => { if (this.left > 0) this.left--; }, 1000); }
                         }">
                        <span class="text-[9px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Tijd tot trekking</span>
                        <span class="text-xs font-mono font-black text-slate-900 dark:text-amber-400 mt-0.5 block" x-text="format()">
                            {{ now()->diffForHumans($lotteryRound->draws_at, true) }}
                        </span>
                    </div>

                    <a href="{{ route('lottery.index') }}" 
                       class="w-full sm:w-auto px-5 py-3 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-black text-xs uppercase tracking-wider transition text-center shadow-xs flex items-center justify-center gap-2 shrink-0">
                        <i class="fa-solid fa-ticket"></i>
                        <span>Koop een Lot</span>
                    </a>
                </div>
            </div>
            @endif

            <!-- PERSOONLIJKE REFERRAL & GRATIS VIP CARD (Toggleable) -->
            <div x-show="widgets.referral" 
                 x-transition
                 class="bg-white dark:bg-[#111827] rounded-2xl p-5 sm:p-6 shadow-xs border border-purple-200/90 dark:border-purple-900/60 relative overflow-hidden" 
                 x-data="{ 
                     copied: false,
                     copyLink() {
                         navigator.clipboard.writeText('{{ $referralStats['link'] }}');
                         this.copied = true;
                         setTimeout(() => this.copied = false, 2500);
                     }
                 }">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-5 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-start gap-3.5">
                        <div class="w-11 h-11 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200 dark:border-purple-800 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl shrink-0 mt-0.5 shadow-2xs">
                            <i class="fa-solid fa-crown text-amber-500"></i>
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                    <i class="fa-solid fa-crown text-amber-500 text-[10px]"></i>
                                    Gratis VIP Actie
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 dark:bg-purple-950 text-purple-800 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                    3 Spelers = 1 Maand VIP
                                </span>
                            </div>
                            <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight">
                                Nodig Vrienden Uit & Krijg Gratis VIP
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-xl leading-relaxed">
                                Deel jouw unieke link. Zodra een vriend level 3 bereikt, telt dit mee voor jouw gratis VIP lidmaatschap en exclusieve bonussen!
                            </p>
                        </div>
                    </div>

                    <button type="button" 
                            @click="copyLink()"
                            class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs transition shadow-xs flex items-center justify-center gap-2 shrink-0 cursor-pointer active:scale-95">
                        <i class="fa-solid" :class="copied ? 'fa-check' : 'fa-link'"></i>
                        <span x-text="copied ? 'Gekopieerd!' : 'Kopieer Link'"></span>
                    </button>
                </div>
            </div>

            <!-- DAGELIJKSE INLOG BONUS & STREAK (Toggleable) -->
            <div x-show="widgets.loginBonus" 
                 x-transition
                 class="bg-white dark:bg-[#111827] rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-200/90 dark:border-slate-800"
                 x-data="{
                     active: {{ $isLoginBonusActive ? 'true' : 'false' }},
                     claimed: {{ $hasClaimedStreakToday ? 'true' : 'false' }},
                     secUntilStart: {{ $secondsUntilLoginBonus }},
                     secRemaining: {{ $secondsRemainingInLoginBonus }},
                     formatTimer(s) {
                         const m = Math.floor(s / 60);
                         const sec = s % 60;
                         return `${m}m ${sec < 10 ? '0' : ''}${sec}s`;
                     },
                     init() {
                         setInterval(() => {
                             if (this.secUntilStart > 0) this.secUntilStart--;
                             if (this.secRemaining > 0) this.secRemaining--;
                         }, 1000);
                     }
                 }">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg shrink-0 shadow-2xs {{ $isLoginBonusActive ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                            <i class="fa-solid fa-gift"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white">Avond Inlog Bonus (22:00 - 22:30)</h3>
                                <template x-if="active && !claimed">
                                    <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded bg-amber-400 text-slate-950 animate-pulse">Nu Open</span>
                                </template>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Log tussen 22:00 en 22:30 in voor 3 gratis kluiscodes en jouw dagelijkse login streak!
                            </p>
                        </div>
                    </div>

                    <div class="shrink-0 flex items-center gap-2">
                        <template x-if="active && !claimed">
                            <form action="{{ route('daily.claim-streak') }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs uppercase tracking-wider shadow-xs transition">
                                    Claim Dag {{ $nextStreakDay }}
                                </button>
                            </form>
                        </template>
                        <template x-if="!active || claimed">
                            <a href="{{ route('daily.index') }}" class="px-3.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs">
                                Bekijk Kalender
                            </a>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Karakter Eigenschappen & Actieve Stats (Toggleable) -->
            <div x-show="widgets.attributes" 
                 x-transition
                 class="bg-white dark:bg-[#111827] rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 p-6">
                <div class="flex items-center justify-between mb-4 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Karakter Eigenschappen & Stats</h3>
                    </div>
                    <a href="{{ route('training.index') }}" class="text-xs font-bold text-indigo-600 dark:text-purple-400 hover:underline flex items-center gap-1 transition">
                        <i class="fa-solid fa-dumbbell text-xs"></i> Trainen in Sportschool &rarr;
                    </a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 text-center">
                    @php
                        $attrList = [
                            ['label' => 'Kracht', 'effective' => $character->getEffectiveStrength(), 'icon' => 'fa-hand-fist', 'color' => 'text-rose-500'],
                            ['label' => 'Verdediging', 'effective' => $character->getEffectiveDefense(), 'icon' => 'fa-shield-halved', 'color' => 'text-indigo-500'],
                            ['label' => 'Conditie', 'effective' => $character->getEffectiveStamina(), 'icon' => 'fa-person-running', 'color' => 'text-emerald-500'],
                            ['label' => 'Intelligentie', 'effective' => $character->getEffectiveIntelligence(), 'icon' => 'fa-brain', 'color' => 'text-purple-500'],
                            ['label' => 'Charisma', 'effective' => $character->getEffectiveCharisma(), 'icon' => 'fa-face-smile-wink', 'color' => 'text-amber-500'],
                            ['label' => 'Discipline', 'effective' => $character->getEffectiveDiscipline(), 'icon' => 'fa-scale-balanced', 'color' => 'text-blue-500'],
                        ];
                    @endphp
                    @foreach($attrList as $attr)
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-100 dark:border-slate-800">
                            <i class="fa-solid {{ $attr['icon'] }} {{ $attr['color'] }} text-base mb-1 block"></i>
                            <span class="text-[10px] font-bold text-slate-400 block">{{ $attr['label'] }}</span>
                            <span class="text-sm font-black text-slate-900 dark:text-white">{{ number_format($attr['effective'], 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Recente Activiteiten Feed (Toggleable) -->
            <div x-show="widgets.activities" 
                 x-transition
                 class="bg-white dark:bg-[#111827] rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </span>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">Recente activiteiten & buit</h3>
                    </div>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($recentActivities as $activity)
                        <div class="p-4 hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition flex items-start gap-4">
                            <div class="w-8 h-8 rounded-full {{ $activity['icon_bg'] }} flex items-center justify-center shrink-0 shadow-2xs">
                                <i class="fa-solid {{ $activity['icon'] }} text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-slate-800 dark:text-slate-100 truncate">{{ $activity['title'] }}</p>
                                <p class="text-xs font-bold {{ $activity['badge_color'] }} mt-0.5">{{ $activity['badge'] }}</p>
                            </div>
                            <div class="text-xs text-slate-400 font-semibold whitespace-nowrap shrink-0">
                                {{ \Carbon\Carbon::parse($activity['time'])->diffForHumans() }}
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-xs text-slate-400 font-medium">
                            <i class="fa-solid fa-clock-rotate-left text-2xl text-slate-300 dark:text-slate-600 mb-2 block"></i>
                            Nog geen recente activiteiten. Start een missie of hossel!
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Right Sidebar (Col-Span-4) -->
        <div class="xl:col-span-4 space-y-6">

            <!-- 1. WEERSTATION & LUCHTPLAATS STATUS WIDGET (Toggleable) -->
            <div x-show="widgets.weather" 
                 x-transition
                 class="bg-white dark:bg-[#111827] rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 overflow-hidden">
                <div class="p-4 sm:p-5 bg-gradient-to-br from-slate-900 to-slate-800 text-white flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-amber-400 block">Kliniek Weerstation</span>
                        <h4 class="text-sm font-bold text-slate-100 truncate">{{ $clinicWeather['location'] }}</h4>
                    </div>
                    <div class="text-2xl text-amber-400">
                        <i class="fa-solid {{ $clinicWeather['icon'] }}"></i>
                    </div>
                </div>

                <div class="p-4 sm:p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-3xl font-black text-slate-900 dark:text-white leading-none font-mono">{{ $clinicWeather['temp'] }}°C</span>
                            <span class="text-[11px] text-slate-400 block mt-1">Gevoelstemperatuur: {{ $clinicWeather['feels_like'] }}°C</span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-200 block">{{ $clinicWeather['condition'] }}</span>
                            <span class="text-[10px] text-slate-400 block">{{ $clinicWeather['wind'] }}</span>
                        </div>
                    </div>

                    <!-- Luchtplaats status bar -->
                    <div class="p-3 rounded-xl border text-xs font-bold flex items-center gap-2.5"
                         :class="'{{ $clinicWeather['yard_open'] }}' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 border-rose-200 dark:border-rose-800'">
                        <span class="w-2 h-2 rounded-full shrink-0"
                              :class="'{{ $clinicWeather['yard_open'] }}' ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'"></span>
                        <div class="truncate">
                            <span class="block truncate">{{ $clinicWeather['yard_status'] }}</span>
                        </div>
                    </div>

                    <a href="{{ route('fight.index') }}" class="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-100 font-extrabold text-xs transition flex items-center justify-center gap-1.5 shadow-2xs">
                        <i class="fa-solid fa-hand-fist text-rose-500"></i>
                        <span>Ga naar de Luchtplaats</span>
                    </a>
                </div>
            </div>

            <!-- 2. LIVE NU.NL NIEUWS WIDGET (Toggleable) -->
            <div x-show="widgets.nunl" 
                 x-transition
                 class="bg-white dark:bg-[#111827] rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-50/70 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2 text-xs font-black tracking-wide text-slate-900 dark:text-white">
                        <span class="w-6 h-5 rounded bg-red-600 text-white font-black text-[10px] flex items-center justify-center tracking-tighter">
                            NU
                        </span>
                        <span>Laatste Nieuws (NU.nl)</span>
                    </div>
                    <span class="text-[9px] font-bold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-950/60 px-2 py-0.5 rounded-full border border-red-200 dark:border-red-800 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-ping"></span> Live Feed
                    </span>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($nuNlNews as $item)
                        <a href="{{ $item['link'] }}" target="_blank" rel="noopener noreferrer" class="p-3.5 px-5 hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition block group">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 mb-1">
                                        <span class="text-[9px] font-black uppercase tracking-wider text-red-600 dark:text-red-400">{{ $item['category'] }}</span>
                                        <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                                        <span class="text-[10px] text-slate-400">{{ $item['time_ago'] }}</span>
                                    </div>
                                    <h4 class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-red-600 dark:group-hover:text-red-400 transition leading-snug line-clamp-2">
                                        {{ $item['title'] }}
                                    </h4>
                                </div>
                                @if(!empty($item['image']))
                                    <img src="{{ $item['image'] }}" alt="Nieuws thumbnail" class="w-12 h-12 rounded-lg object-cover shrink-0 border border-slate-200 dark:border-slate-700 shadow-2xs">
                                @else
                                    <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                    </div>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="p-6 text-center text-xs text-slate-400">
                            Geen extern nieuws beschikbaar.
                        </div>
                    @endforelse
                </div>

                <a href="https://www.nu.nl" target="_blank" rel="noopener" class="block w-full text-center py-2 bg-slate-50/70 dark:bg-slate-800/40 text-[11px] font-bold text-slate-500 hover:text-slate-800 dark:hover:text-white border-t border-slate-100 dark:border-slate-800">
                    Lees meer op NU.nl &rarr;
                </a>
            </div>

            <!-- 3. LAATSTE KLINIEK NIEUWS (Toggleable) -->
            <div x-show="widgets.clinicNews" 
                 x-transition
                 class="bg-white dark:bg-[#111827] rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-50/70 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2 text-xs font-black tracking-wide text-slate-900 dark:text-white">
                        <span class="w-5 h-5 rounded-md bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 flex items-center justify-center text-[10px] border border-purple-200 dark:border-purple-800">
                            <i class="fa-solid fa-newspaper"></i>
                        </span>
                        <span>TBS Kliniek Berichten</span>
                    </div>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($latestNews as $newsItem)
                        <a href="{{ route('news.show', $newsItem->slug) }}" class="p-3.5 px-5 hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition block group">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition truncate">
                                    {{ $newsItem->title }}
                                </h4>
                                <span class="text-[10px] text-slate-400 shrink-0">{{ $newsItem->created_at->diffForHumans() }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="p-6 text-center text-xs text-slate-400">
                            Nog geen berichten geplaatst.
                        </div>
                    @endforelse
                </div>

                <a href="{{ route('news.index') }}" class="block w-full text-center py-2 bg-slate-50/70 dark:bg-slate-800/40 text-[11px] font-bold text-purple-700 dark:text-purple-300 hover:underline border-t border-slate-100 dark:border-slate-800">
                    Alle berichten bekijken &rarr;
                </a>
            </div>

            <!-- 4. BENDE KAART (Toggleable) -->
            <div x-show="widgets.gang" 
                 x-transition
                 class="bg-white dark:bg-[#111827] rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 overflow-hidden">
                @if($character->gang)
                    <div class="p-6 flex flex-col items-center text-center">
                        <div class="w-16 h-16 mb-3 rounded-2xl bg-slate-900 text-amber-400 flex items-center justify-center text-2xl shadow-xs overflow-hidden border border-slate-200 dark:border-slate-700">
                            @if($character->gang->avatar)
                                <img src="{{ $character->gang->avatar_url }}" alt="{{ $character->gang->name }}" class="w-full h-full object-cover">
                            @else
                                <i class="fa-solid {{ $character->gang->emblem ?: 'fa-crown' }}"></i>
                            @endif
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ $character->gang->name }}</h3>
                        <span class="text-xs font-semibold text-slate-400 mb-4">{{ ucfirst($character->gang_role ?: 'Lid') }}</span>
                        <div class="w-full border-t border-slate-100 dark:border-slate-800 pt-3 mb-4 space-y-2 text-xs font-bold">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Leden:</span>
                                <span class="text-slate-900 dark:text-white">{{ $character->gang->members()->count() }} actief</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Bendekas:</span>
                                <span class="text-emerald-600 font-extrabold">€{{ number_format($character->gang->treasury, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <a href="{{ route('gangs.index') }}" class="block w-full text-center bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 rounded-xl transition text-xs shadow-xs">
                            Mijn Bende Bezoeken
                        </a>
                    </div>
                @else
                    <div class="p-6 flex flex-col items-center text-center">
                        <div class="w-14 h-14 mb-3 rounded-2xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-2xl shadow-xs">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Sluit je aan bij een Bende</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                            Samen sta je sterker in de kliniek. Vecht mee in heists en wars!
                        </p>
                        <a href="{{ route('gangs.index') }}" class="block w-full text-center bg-purple-600 hover:bg-purple-700 text-white font-bold py-2.5 rounded-xl transition text-xs shadow-xs">
                            Bendes Bekijken
                        </a>
                    </div>
                @endif
            </div>

            <!-- 5. ACTIEVE MISSIES SNELKOPPELING (Toggleable) -->
            <div x-show="widgets.missions" 
                 x-transition
                 class="bg-white dark:bg-[#111827] rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-50/70 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <span class="text-xs font-black text-slate-900 dark:text-white">Snelle Missies</span>
                    <a href="{{ route('missions.index') }}" class="text-[11px] font-bold text-purple-600 dark:text-purple-400 hover:underline">
                        Alle ({{ \App\Models\Mission::count() }}) &rarr;
                    </a>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($quickMissions->take(3) as $m)
                        <div class="p-3.5 flex items-center justify-between gap-3">
                            <div class="truncate">
                                <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $m->title }}</h4>
                                <span class="text-[10px] text-emerald-600 font-bold">+€{{ number_format($m->min_cash_reward, 0, ',', '.') }} &bull; +{{ $m->xp_reward }} XP</span>
                            </div>
                            <a href="{{ route('missions.index') }}" class="px-2.5 py-1.5 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold text-[11px] hover:bg-purple-100 transition shrink-0">
                                Start
                            </a>
                        </div>
                    @empty
                        <div class="p-4 text-center text-xs text-slate-400">
                            Geen missies gevonden.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

    <!-- DASHBOARD CUSTOMIZER MODAL / DRAWER -->
    <div x-show="showCustomizer" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm select-none"
         style="display: none;">
        
        <div @click.outside="showCustomizer = false"
             class="bg-white dark:bg-[#111827] rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-xl overflow-hidden flex flex-col max-h-[90vh]">
            
            <!-- Modal Header -->
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-950/70 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm shadow-xs">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Mijn Dashboard Inrichten</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Pas thema's, widgets en actiebalken aan naar wens.</p>
                    </div>
                </div>

                <button type="button" @click="showCustomizer = false" class="w-8 h-8 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-700 flex items-center justify-center transition cursor-pointer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Customizer Tab Switcher -->
            <div class="flex border-b border-slate-100 dark:border-slate-800 px-5 pt-3 gap-2 bg-slate-50/30 dark:bg-slate-800/20">
                <button type="button" 
                        @click="activeCustomizerTab = 'themes'"
                        class="pb-2.5 px-3 text-xs font-black border-b-2 transition cursor-pointer"
                        :class="activeCustomizerTab === 'themes' ? 'border-purple-600 text-purple-600 dark:text-purple-400' : 'border-transparent text-slate-500 hover:text-slate-800'">
                    <i class="fa-solid fa-palette mr-1"></i> Afdelings Thema
                </button>

                <button type="button" 
                        @click="activeCustomizerTab = 'widgets'"
                        class="pb-2.5 px-3 text-xs font-black border-b-2 transition cursor-pointer"
                        :class="activeCustomizerTab === 'widgets' ? 'border-purple-600 text-purple-600 dark:text-purple-400' : 'border-transparent text-slate-500 hover:text-slate-800'">
                    <i class="fa-solid fa-cubes mr-1"></i> Widgets Aan/Uit
                </button>

                <button type="button" 
                        @click="activeCustomizerTab = 'actions'"
                        class="pb-2.5 px-3 text-xs font-black border-b-2 transition cursor-pointer"
                        :class="activeCustomizerTab === 'actions' ? 'border-purple-600 text-purple-600 dark:text-purple-400' : 'border-transparent text-slate-500 hover:text-slate-800'">
                    <i class="fa-solid fa-bolt-lightning mr-1"></i> Snelle Actiebalk
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="p-5 overflow-y-auto space-y-4 flex-1">
                
                <!-- TAB 1: THEMES -->
                <div x-show="activeCustomizerTab === 'themes'" class="space-y-3">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-400 block">Kies jouw cel sfeer & afdelingsstijl:</span>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Theme 1: Clinical -->
                        <button type="button" 
                                @click="setTheme('clinical')"
                                class="p-3.5 rounded-2xl border text-left transition cursor-pointer flex items-center justify-between"
                                :class="theme === 'clinical' ? 'border-purple-600 bg-purple-50/50 dark:bg-purple-950/40 shadow-xs ring-2 ring-purple-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-purple-600 text-white flex items-center justify-center text-xs shadow-xs">
                                    <i class="fa-solid fa-hospital"></i>
                                </div>
                                <div>
                                    <span class="text-xs font-black text-slate-900 dark:text-white block">Kliniek Standaard</span>
                                    <span class="text-[10px] text-slate-400 block">Strak, slate grijs & paars</span>
                                </div>
                            </div>
                            <i x-show="theme === 'clinical'" class="fa-solid fa-circle-check text-purple-600 text-sm"></i>
                        </button>

                        <!-- Theme 2: EBI High Security -->
                        <button type="button" 
                                @click="setTheme('ebi')"
                                class="p-3.5 rounded-2xl border text-left transition cursor-pointer flex items-center justify-between"
                                :class="theme === 'ebi' ? 'border-rose-600 bg-rose-50/50 dark:bg-rose-950/40 shadow-xs ring-2 ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center text-xs shadow-xs">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </div>
                                <div>
                                    <span class="text-xs font-black text-slate-900 dark:text-white block">EBI Maximum Security</span>
                                    <span class="text-[10px] text-slate-400 block">Alarm rood & donker</span>
                                </div>
                            </div>
                            <i x-show="theme === 'ebi'" class="fa-solid fa-circle-check text-rose-600 text-sm"></i>
                        </button>

                        <!-- Theme 3: VIP Executive -->
                        <button type="button" 
                                @click="setTheme('vip')"
                                class="p-3.5 rounded-2xl border text-left transition cursor-pointer flex items-center justify-between"
                                :class="theme === 'vip' ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-950/40 shadow-xs ring-2 ring-amber-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center text-xs shadow-xs font-bold">
                                    <i class="fa-solid fa-crown"></i>
                                </div>
                                <div>
                                    <span class="text-xs font-black text-slate-900 dark:text-white block">Directie Kantoor (VIP)</span>
                                    <span class="text-[10px] text-slate-400 block">Luxe goud & zwart</span>
                                </div>
                            </div>
                            <i x-show="theme === 'vip'" class="fa-solid fa-circle-check text-amber-500 text-sm"></i>
                        </button>

                        <!-- Theme 4: Cyber Tunnel -->
                        <button type="button" 
                                @click="setTheme('cyber')"
                                class="p-3.5 rounded-2xl border text-left transition cursor-pointer flex items-center justify-between"
                                :class="theme === 'cyber' ? 'border-cyan-500 bg-cyan-50/50 dark:bg-cyan-950/40 shadow-xs ring-2 ring-cyan-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-cyan-500 text-white flex items-center justify-center text-xs shadow-xs">
                                    <i class="fa-solid fa-terminal"></i>
                                </div>
                                <div>
                                    <span class="text-xs font-black text-slate-900 dark:text-white block">Ondergrondse Tunnel</span>
                                    <span class="text-[10px] text-slate-400 block">Neon cyaan & violet</span>
                                </div>
                            </div>
                            <i x-show="theme === 'cyber'" class="fa-solid fa-circle-check text-cyan-500 text-sm"></i>
                        </button>

                        <!-- Theme 5: Bajes Oranje -->
                        <button type="button" 
                                @click="setTheme('orange')"
                                class="p-3.5 rounded-2xl border text-left transition cursor-pointer flex items-center justify-between sm:col-span-2"
                                :class="theme === 'orange' ? 'border-orange-500 bg-orange-50/50 dark:bg-orange-950/40 shadow-xs ring-2 ring-orange-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-orange-600 text-white flex items-center justify-center text-xs shadow-xs">
                                    <i class="fa-solid fa-shirt"></i>
                                </div>
                                <div>
                                    <span class="text-xs font-black text-slate-900 dark:text-white block">Bajes Oranje</span>
                                    <span class="text-[10px] text-slate-400 block">Klassieke gevangenis overall stijl</span>
                                </div>
                            </div>
                            <i x-show="theme === 'orange'" class="fa-solid fa-circle-check text-orange-600 text-sm"></i>
                        </button>
                    </div>
                </div>

                <!-- TAB 2: WIDGETS -->
                <div x-show="activeCustomizerTab === 'widgets'" class="space-y-2">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-400 block mb-2">Schakel widgets in of uit:</span>

                    <!-- Widget Toggle Item -->
                    <template x-for="item in widgetList" :key="item.key">
                        <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-700/80 flex items-center justify-between gap-3 bg-slate-50/50 dark:bg-slate-800/40">
                            <div class="flex items-center gap-2.5 truncate">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs" :class="item.bg">
                                    <i class="fa-solid" :class="item.icon"></i>
                                </div>
                                <div class="truncate">
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-100 block truncate" x-text="item.title"></span>
                                    <span class="text-[10px] text-slate-400 block truncate" x-text="item.desc"></span>
                                </div>
                            </div>

                            <button type="button" 
                                    @click="toggleWidget(item.key)"
                                    class="w-11 h-6 rounded-full transition-colors p-0.5 cursor-pointer shrink-0"
                                    :class="widgets[item.key] ? 'bg-purple-600' : 'bg-slate-300 dark:bg-slate-700'">
                                <div class="w-5 h-5 rounded-full bg-white transition-transform"
                                     :class="widgets[item.key] ? 'translate-x-5' : 'translate-x-0'"></div>
                            </button>
                        </div>
                    </template>
                </div>

                <!-- TAB 3: QUICK ACTIONS -->
                <div x-show="activeCustomizerTab === 'actions'" class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black uppercase tracking-wider text-slate-400">Selecteer jouw favoriete acties:</span>
                        <span class="text-xs font-bold text-purple-600" x-text="quickActions.length + '/8 gekozen'"></span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <template x-for="act in availableActions" :key="act.id">
                            <button type="button" 
                                    @click="toggleAction(act.id)"
                                    class="p-2.5 rounded-xl border text-left flex items-center justify-between gap-2 transition cursor-pointer"
                                    :class="quickActions.includes(act.id) ? 'border-purple-500 bg-purple-50/60 dark:bg-purple-950/40' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'">
                                <div class="flex items-center gap-2.5 truncate">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs" :class="act.color">
                                        <i class="fa-solid" :class="act.icon"></i>
                                    </div>
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate" x-text="act.label"></span>
                                </div>
                                <i class="fa-solid" :class="quickActions.includes(act.id) ? 'fa-square-check text-purple-600' : 'fa-square text-slate-300 dark:text-slate-600'"></i>
                            </button>
                        </template>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex items-center justify-between">
                <button type="button" 
                        @click="resetDefaults()" 
                        class="text-xs font-bold text-slate-500 hover:text-rose-600 transition cursor-pointer">
                    <i class="fa-solid fa-rotate-left mr-1"></i> Fabrieksinstellingen
                </button>

                <button type="button" 
                        @click="showCustomizer = false" 
                        class="px-5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs shadow-xs transition cursor-pointer">
                    Opslaan & Sluiten
                </button>
            </div>

        </div>
    </div>

</div>

<!-- Alpine Dashboard Customizer Script -->
<script>
function tbsDashboardCustomizer() {
    return {
        showCustomizer: false,
        activeCustomizerTab: 'themes',
        theme: 'clinical',

        widgets: {
            betaNotice: true,
            roadmap: true,
            quickToolbar: true,
            weather: true,
            radio: true,
            nunl: true,
            lottery: true,
            referral: true,
            loginBonus: true,
            attributes: true,
            activities: true,
            clinicNews: true,
            gang: true,
            missions: true,
        },

        widgetList: [
            { key: 'quickToolbar', title: 'Snelle Bajes Actiebalk', desc: 'Directe snelknoppen naar jouw favoriete locaties', icon: 'fa-bolt', bg: 'bg-amber-100 text-amber-700' },
            { key: 'radio', title: 'Radio Kliniek FM Deck', desc: 'In-dashboard speler voor FunX, SLAM!, 538, Sky & NPO', icon: 'fa-radio', bg: 'bg-rose-100 text-rose-700' },
            { key: 'weather', title: 'Kliniek Weerstation & Luchtplaats', desc: 'Actueel weer en status van de luchtplaats', icon: 'fa-cloud-sun', bg: 'bg-sky-100 text-sky-700' },
            { key: 'nunl', title: 'NU.nl Live Nieuws Feed', desc: 'Actuele krantenkoppen en buitenwereld updates', icon: 'fa-newspaper', bg: 'bg-red-100 text-red-700' },
            { key: 'lottery', title: 'Afdelingsloterij Jackpot', desc: 'De 14-daagse jackpot en trekkingsklok', icon: 'fa-ticket', bg: 'bg-amber-100 text-amber-700' },
            { key: 'referral', title: 'Gratis VIP & Referral Kaart', desc: 'Vrienden uitnodigen voor gratis VIP lidmaatschap', icon: 'fa-crown', bg: 'bg-purple-100 text-purple-700' },
            { key: 'loginBonus', title: 'Avond Inlog Bonus (22:00)', desc: '7-daagse streak beloning en gratis kluiscodes', icon: 'fa-gift', bg: 'bg-emerald-100 text-emerald-700' },
            { key: 'attributes', title: 'Karakter Eigenschappen & Stats', desc: 'Kracht, conditie, intelligentie en discipline', icon: 'fa-dumbbell', bg: 'bg-indigo-100 text-indigo-700' },
            { key: 'activities', title: 'Recente Activiteiten Feed', desc: 'Log van recente overvallen, missies en winsten', icon: 'fa-clock-rotate-left', bg: 'bg-slate-200 text-slate-700' },
            { key: 'clinicNews', title: 'TBS Kliniek Berichten', desc: 'Officiële aankondigingen van de inrichting', icon: 'fa-bullhorn', bg: 'bg-purple-100 text-purple-700' },
            { key: 'gang', title: 'Mijn Bende / Gang Kaart', desc: 'Overzicht van je factie, kas en leden', icon: 'fa-users', bg: 'bg-slate-200 text-slate-800' },
            { key: 'missions', title: 'Snelle Missies Lijst', desc: 'Overzicht van beschikbare missies voor cash & XP', icon: 'fa-list-check', bg: 'bg-purple-100 text-purple-700' },
            { key: 'betaNotice', title: 'Open Beta Melding', desc: 'Informatie over de testfase van het spel', icon: 'fa-flask-vial', bg: 'bg-purple-100 text-purple-700' },
            { key: 'roadmap', title: 'Roadmap & Toekomst Kaart', desc: 'Stemmen op nieuwe kliniek features', icon: 'fa-compass', bg: 'bg-slate-200 text-slate-700' }
        ],

        quickActions: ['crimes', 'arena', 'marketplace', 'radio', 'bank', 'daily'],

        availableActions: [
            { id: 'crimes', label: 'Zakkenrollen', icon: 'fa-mask', url: '{{ route("pickpocket.index") }}', color: 'text-amber-600 bg-amber-50 dark:bg-amber-950/60' },
            { id: 'arena', label: 'Luchtplaats', icon: 'fa-hand-fist', url: '{{ route("fight.index") }}', color: 'text-rose-600 bg-rose-50 dark:bg-rose-950/60' },
            { id: 'marketplace', label: 'Marktplaats', icon: 'fa-store', url: '{{ route("marketplace.index") }}', color: 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/60' },
            { id: 'packages', label: 'Koffers', icon: 'fa-box-open', url: '{{ route("packages.index") }}', color: 'text-purple-600 bg-purple-50 dark:bg-purple-950/60' },
            { id: 'lockers', label: 'Kluisjes Kraken', icon: 'fa-lock-open', url: '{{ route("lockers.index") }}', color: 'text-blue-600 bg-blue-50 dark:bg-blue-950/60' },
            { id: 'bank', label: 'Bank & Lening', icon: 'fa-building-columns', url: '{{ route("bank.index") }}', color: 'text-indigo-600 bg-indigo-50 dark:bg-indigo-950/60' },
            { id: 'casino', label: 'Casino', icon: 'fa-dice', url: '{{ route("casino.index") }}', color: 'text-yellow-600 bg-yellow-50 dark:bg-yellow-950/60' },
            { id: 'radio', label: 'Afdelingsradio', icon: 'fa-radio', url: '{{ route("radio.index") }}', color: 'text-pink-600 bg-pink-50 dark:bg-pink-950/60' },
            { id: 'daily', label: 'Rad van Fortuin', icon: 'fa-dharmachakra', url: '{{ route("daily.index") }}', color: 'text-purple-600 bg-purple-50 dark:bg-purple-950/60' },
            { id: 'gang', label: 'Mijn Bende', icon: 'fa-users', url: '{{ route("gangs.index") }}', color: 'text-violet-600 bg-violet-50 dark:bg-violet-950/60' }
        ],

        init() {
            const savedTheme = localStorage.getItem('tbs_dashboard_theme');
            if (savedTheme) this.theme = savedTheme;

            const savedWidgets = localStorage.getItem('tbs_dashboard_widgets');
            if (savedWidgets) {
                try {
                    this.widgets = Object.assign({}, this.widgets, JSON.parse(savedWidgets));
                } catch(e) {}
            }

            const savedActions = localStorage.getItem('tbs_dashboard_actions');
            if (savedActions) {
                try {
                    this.quickActions = JSON.parse(savedActions);
                } catch(e) {}
            }
        },

        currentThemeName() {
            switch(this.theme) {
                case 'ebi': return 'EBI Vleugel';
                case 'vip': return 'Directiekantoor';
                case 'cyber': return 'Smokkeltunnel';
                case 'orange': return 'Bajes Oranje';
                default: return 'Kliniek Standaard';
            }
        },

        setTheme(t) {
            this.theme = t;
            localStorage.setItem('tbs_dashboard_theme', t);
        },

        toggleWidget(key) {
            this.widgets[key] = !this.widgets[key];
            localStorage.setItem('tbs_dashboard_widgets', JSON.stringify(this.widgets));
        },

        toggleAction(id) {
            if (this.quickActions.includes(id)) {
                if (this.quickActions.length > 2) {
                    this.quickActions = this.quickActions.filter(x => x !== id);
                }
            } else {
                if (this.quickActions.length < 8) {
                    this.quickActions.push(id);
                }
            }
            localStorage.setItem('tbs_dashboard_actions', JSON.stringify(this.quickActions));
        },

        activeActionItems() {
            return this.availableActions.filter(a => this.quickActions.includes(a.id));
        },

        resetDefaults() {
            localStorage.removeItem('tbs_dashboard_theme');
            localStorage.removeItem('tbs_dashboard_widgets');
            localStorage.removeItem('tbs_dashboard_actions');
            this.theme = 'clinical';
            this.widgets = {
                betaNotice: true,
                roadmap: true,
                quickToolbar: true,
                weather: true,
                radio: true,
                nunl: true,
                lottery: true,
                referral: true,
                loginBonus: true,
                attributes: true,
                activities: true,
                clinicNews: true,
                gang: true,
                missions: true,
            };
            this.quickActions = ['crimes', 'arena', 'marketplace', 'radio', 'bank', 'daily'];
        }
    };
}
</script>
@endsection
