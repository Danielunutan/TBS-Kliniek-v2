@extends('layouts.app')

@section('content')
<div class="w-full mt-4" x-data="{ tab: 'vandaag' }">
    
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xs border border-slate-200 dark:border-slate-800 overflow-hidden">
        
        <!-- Header -->
        <div class="p-6 flex items-center justify-between border-b border-slate-200 dark:border-slate-800">
            <h2 class="font-black text-xl text-slate-900 dark:text-white flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-base border border-rose-200 dark:border-rose-900/50 shadow-2xs">
                    <i class="fa-solid fa-skull"></i>
                </span>
                <span>Geweldsstatistieken</span>
            </h2>
        </div>

        <!-- Tabs -->
        <div class="flex border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
            <button type="button" @click="tab = 'vandaag'" 
                    :class="tab === 'vandaag' ? 'border-b-2 border-purple-600 text-purple-700 dark:text-purple-300 bg-white dark:bg-slate-900 font-black' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 font-bold'"
                    class="flex-1 py-3.5 text-xs sm:text-sm transition cursor-pointer flex items-center justify-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                <span>Meeste Geweld (Vandaag)</span>
            </button>
            <button type="button" @click="tab = 'week'" 
                    :class="tab === 'week' ? 'border-b-2 border-purple-600 text-purple-700 dark:text-purple-300 bg-white dark:bg-slate-900 font-black' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 font-bold'"
                    class="flex-1 py-3.5 text-xs sm:text-sm transition cursor-pointer flex items-center justify-center gap-2">
                <i class="fa-solid fa-calendar-week text-xs"></i>
                <span>Meeste Geweld (7 Dagen)</span>
            </button>
        </div>

        <!-- Content: VANDAAG -->
        <div x-show="tab === 'vandaag'" class="space-y-4 p-4 sm:p-6">
            
            <!-- Patiënten Section -->
            <div class="bg-purple-50/60 dark:bg-purple-950/40 p-3 rounded-xl border border-purple-200/80 dark:border-purple-900/50 flex items-center gap-2 text-purple-900 dark:text-purple-300 font-extrabold text-xs uppercase tracking-wider">
                <i class="fa-solid fa-user-injured text-purple-600 dark:text-purple-400"></i>
                <span>Patiënten Ranglijst</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Meeste Geweld Totaal -->
                <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden bg-white dark:bg-slate-900 shadow-2xs">
                    <div class="bg-slate-50 dark:bg-slate-800/80 text-[10px] uppercase font-black tracking-widest text-slate-500 dark:text-slate-400 py-2.5 px-4 border-b border-slate-200 dark:border-slate-800">
                        Meeste Geweld (Totaal Vandaag)
                    </div>
                    <div class="p-4 space-y-2.5">
                        @forelse($topPatientsToday as $p)
                        <div class="flex justify-between items-center py-1 border-b border-slate-100 dark:border-slate-800/60 last:border-b-0">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('profile.show', $p->name) }}" class="font-extrabold text-xs text-purple-700 dark:text-purple-400 hover:underline">
                                    {{ $p->name }}
                                </a>
                                @if($p->gang)
                                <span class="text-[9px] px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-bold">
                                    {{ $p->gang }}
                                </span>
                                @endif
                            </div>
                            <span class="font-mono font-bold text-xs text-slate-700 dark:text-slate-300">
                                {{ number_format($p->kills, 0, ',', '.') }} {{ $p->kills === 1 ? 'slachtoffer' : 'slachtoffers' }}
                            </span>
                        </div>
                        @empty
                        <div class="py-6 text-center text-xs text-slate-400 italic">
                            Nog geen geweldplegingen geregistreerd vandaag.
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Meeste Online Geweld -->
                <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden bg-white dark:bg-slate-900 shadow-2xs">
                    <div class="bg-slate-50 dark:bg-slate-800/80 text-[10px] uppercase font-black tracking-widest text-slate-500 dark:text-slate-400 py-2.5 px-4 border-b border-slate-200 dark:border-slate-800">
                        Meeste Online Geweld (Actieve Patiënten)
                    </div>
                    <div class="p-4 space-y-2.5">
                        @forelse($topOnlinePatientsToday as $p)
                        <div class="flex justify-between items-center py-1 border-b border-slate-100 dark:border-slate-800/60 last:border-b-0">
                            <div class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <a href="{{ route('profile.show', $p->name) }}" class="font-extrabold text-xs text-purple-700 dark:text-purple-400 hover:underline">
                                    {{ $p->name }}
                                </a>
                            </div>
                            <span class="font-mono font-bold text-xs text-slate-700 dark:text-slate-300">
                                {{ number_format($p->kills, 0, ',', '.') }} slachtoffers
                            </span>
                        </div>
                        @empty
                        <div class="py-6 text-center text-xs text-slate-400 italic">
                            Momenteel geen geweldplegers online.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Bendes Section -->
            <div class="bg-purple-50/60 dark:bg-purple-950/40 p-3 rounded-xl border border-purple-200/80 dark:border-purple-900/50 flex items-center gap-2 text-purple-900 dark:text-purple-300 font-extrabold text-xs uppercase tracking-wider mt-6">
                <i class="fa-solid fa-users text-purple-600 dark:text-purple-400"></i>
                <span>Bendes & Gangs Ranglijst</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Meeste Geweld Gangs -->
                <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden bg-white dark:bg-slate-900 shadow-2xs">
                    <div class="bg-slate-50 dark:bg-slate-800/80 text-[10px] uppercase font-black tracking-widest text-slate-500 dark:text-slate-400 py-2.5 px-4 border-b border-slate-200 dark:border-slate-800">
                        Meeste Geweld (Gangs Totaal)
                    </div>
                    <div class="p-4 space-y-2.5">
                        @forelse($topGangsToday as $g)
                        <div class="flex justify-between items-center py-1 border-b border-slate-100 dark:border-slate-800/60 last:border-b-0">
                            <span class="font-extrabold text-xs text-indigo-700 dark:text-indigo-400">
                                {{ $g->name }}
                            </span>
                            <span class="font-mono font-bold text-xs text-slate-700 dark:text-slate-300">
                                {{ number_format($g->kills, 0, ',', '.') }} {{ $g->kills === 1 ? 'slachtoffer' : 'slachtoffers' }}
                            </span>
                        </div>
                        @empty
                        <div class="py-6 text-center text-xs text-slate-400 italic">
                            Nog geen bendegeweld geregistreerd.
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Meeste Online Geweld Gangs -->
                <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden bg-white dark:bg-slate-900 shadow-2xs">
                    <div class="bg-slate-50 dark:bg-slate-800/80 text-[10px] uppercase font-black tracking-widest text-slate-500 dark:text-slate-400 py-2.5 px-4 border-b border-slate-200 dark:border-slate-800">
                        Meeste Online Geweld (Gangs met Actieve Leden)
                    </div>
                    <div class="p-4 space-y-2.5">
                        @forelse($topOnlineGangsToday as $g)
                        <div class="flex justify-between items-center py-1 border-b border-slate-100 dark:border-slate-800/60 last:border-b-0">
                            <div class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="font-extrabold text-xs text-indigo-700 dark:text-indigo-400">
                                    {{ $g->name }}
                                </span>
                            </div>
                            <span class="font-mono font-bold text-xs text-slate-700 dark:text-slate-300">
                                {{ number_format($g->kills, 0, ',', '.') }} slachtoffers
                            </span>
                        </div>
                        @empty
                        <div class="py-6 text-center text-xs text-slate-400 italic">
                            Momenteel geen bendeleden online.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        <!-- Content: 7 DAGEN -->
        <div x-show="tab === 'week'" x-cloak class="space-y-4 p-4 sm:p-6">
            
            <!-- Patiënten Section 7 Dagen -->
            <div class="bg-purple-50/60 dark:bg-purple-950/40 p-3 rounded-xl border border-purple-200/80 dark:border-purple-900/50 flex items-center gap-2 text-purple-900 dark:text-purple-300 font-extrabold text-xs uppercase tracking-wider">
                <i class="fa-solid fa-chart-line text-purple-600 dark:text-purple-400"></i>
                <span>Patiënten Ranglijst (Afgelopen 7 Dagen)</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden bg-white dark:bg-slate-900 shadow-2xs">
                    <div class="bg-slate-50 dark:bg-slate-800/80 text-[10px] uppercase font-black tracking-widest text-slate-500 dark:text-slate-400 py-2.5 px-4 border-b border-slate-200 dark:border-slate-800">
                        Meeste Geweld (7 Dagen Totaal)
                    </div>
                    <div class="p-4 space-y-2.5">
                        @forelse($topPatientsWeek as $p)
                        <div class="flex justify-between items-center py-1 border-b border-slate-100 dark:border-slate-800/60 last:border-b-0">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('profile.show', $p->name) }}" class="font-extrabold text-xs text-purple-700 dark:text-purple-400 hover:underline">
                                    {{ $p->name }}
                                </a>
                                @if($p->gang)
                                <span class="text-[9px] px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-bold">
                                    {{ $p->gang }}
                                </span>
                                @endif
                            </div>
                            <span class="font-mono font-bold text-xs text-slate-700 dark:text-slate-300">
                                {{ number_format($p->kills, 0, ',', '.') }} slachtoffers
                            </span>
                        </div>
                        @empty
                        <div class="py-6 text-center text-xs text-slate-400 italic">
                            Onvoldoende data voor de afgelopen 7 dagen.
                        </div>
                        @endforelse
                    </div>
                </div>

                <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden bg-white dark:bg-slate-900 shadow-2xs">
                    <div class="bg-slate-50 dark:bg-slate-800/80 text-[10px] uppercase font-black tracking-widest text-slate-500 dark:text-slate-400 py-2.5 px-4 border-b border-slate-200 dark:border-slate-800">
                        Meeste Online Geweld (7 Dagen)
                    </div>
                    <div class="p-4 space-y-2.5">
                        @forelse($topOnlinePatientsWeek as $p)
                        <div class="flex justify-between items-center py-1 border-b border-slate-100 dark:border-slate-800/60 last:border-b-0">
                            <div class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <a href="{{ route('profile.show', $p->name) }}" class="font-extrabold text-xs text-purple-700 dark:text-purple-400 hover:underline">
                                    {{ $p->name }}
                                </a>
                            </div>
                            <span class="font-mono font-bold text-xs text-slate-700 dark:text-slate-300">
                                {{ number_format($p->kills, 0, ',', '.') }} slachtoffers
                            </span>
                        </div>
                        @empty
                        <div class="py-6 text-center text-xs text-slate-400 italic">
                            Momenteel geen geweldplegers online.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Bendes 7 Dagen -->
            <div class="bg-purple-50/60 dark:bg-purple-950/40 p-3 rounded-xl border border-purple-200/80 dark:border-purple-900/50 flex items-center gap-2 text-purple-900 dark:text-purple-300 font-extrabold text-xs uppercase tracking-wider mt-6">
                <i class="fa-solid fa-users text-purple-600 dark:text-purple-400"></i>
                <span>Bendes & Gangs (Afgelopen 7 Dagen)</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden bg-white dark:bg-slate-900 shadow-2xs">
                    <div class="bg-slate-50 dark:bg-slate-800/80 text-[10px] uppercase font-black tracking-widest text-slate-500 dark:text-slate-400 py-2.5 px-4 border-b border-slate-200 dark:border-slate-800">
                        Meeste Geweld (Gangs 7 Dagen)
                    </div>
                    <div class="p-4 space-y-2.5">
                        @forelse($topGangsWeek as $g)
                        <div class="flex justify-between items-center py-1 border-b border-slate-100 dark:border-slate-800/60 last:border-b-0">
                            <span class="font-extrabold text-xs text-indigo-700 dark:text-indigo-400">
                                {{ $g->name }}
                            </span>
                            <span class="font-mono font-bold text-xs text-slate-700 dark:text-slate-300">
                                {{ number_format($g->kills, 0, ',', '.') }} slachtoffers
                            </span>
                        </div>
                        @empty
                        <div class="py-6 text-center text-xs text-slate-400 italic">
                            Geen bendegeweld data gevonden.
                        </div>
                        @endforelse
                    </div>
                </div>

                <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden bg-white dark:bg-slate-900 shadow-2xs">
                    <div class="bg-slate-50 dark:bg-slate-800/80 text-[10px] uppercase font-black tracking-widest text-slate-500 dark:text-slate-400 py-2.5 px-4 border-b border-slate-200 dark:border-slate-800">
                        Gangs met Online Leden (7 Dagen)
                    </div>
                    <div class="p-4 space-y-2.5">
                        @forelse($topOnlineGangsWeek as $g)
                        <div class="flex justify-between items-center py-1 border-b border-slate-100 dark:border-slate-800/60 last:border-b-0">
                            <div class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="font-extrabold text-xs text-indigo-700 dark:text-indigo-400">
                                    {{ $g->name }}
                                </span>
                            </div>
                            <span class="font-mono font-bold text-xs text-slate-700 dark:text-slate-300">
                                {{ number_format($g->kills, 0, ',', '.') }} slachtoffers
                            </span>
                        </div>
                        @empty
                        <div class="py-6 text-center text-xs text-slate-400 italic">
                            Momenteel geen bendeleden online.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
