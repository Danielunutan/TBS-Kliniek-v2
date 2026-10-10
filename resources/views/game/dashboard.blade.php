@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- TOP WELCOME BANNER CARD (Exact match to top-right screen in mockup) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
            <!-- Left Character Portrait -->
            <div class="lg:col-span-2 flex justify-center lg:justify-start">
                <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-2xl overflow-hidden border-2 border-purple-600/60 shadow-md relative group shrink-0 bg-purple-950">
                    <img src="{{ asset('assets/characters/henkie.jpg') }}" alt="{{ $character->name }}" class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-300">
                </div>
            </div>

            <!-- Middle Welcome & 3 Mini Cards -->
            <div class="lg:col-span-7 space-y-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        Welkom terug, {{ $character->name }}!
                    </h1>
                    <p class="text-xs sm:text-sm font-medium text-slate-500 mt-0.5">
                        De kliniek is zwaar, maar jij bent zwaarder.
                    </p>
                </div>

                <!-- 3 Stats Row in Banner -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Level & XP -->
                    <div class="bg-slate-50/90 border border-slate-200/80 p-3 rounded-xl flex items-center gap-3">
                        <span class="w-9 h-9 rounded-lg bg-purple-100 text-purple-800 flex items-center justify-center font-black text-sm shrink-0">
                            <i class="fa-solid fa-chart-line"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Level</span>
                            <span class="font-black text-slate-900 text-sm block leading-none">{{ $character->level }}</span>
                            <div class="w-full bg-slate-200 rounded-full h-1.5 mt-1.5 overflow-hidden">
                                <div class="bg-purple-600 h-full rounded-full" style="width: {{ $xpPercentage }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- KliniekCash -->
                    <div class="bg-slate-50/90 border border-slate-200/80 p-3 rounded-xl flex items-center gap-3">
                        <span class="w-9 h-9 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-sm shrink-0">
                            <i class="fa-solid fa-coins"></i>
                        </span>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">KliniekCash</span>
                            <span class="font-black text-slate-900 text-sm block leading-none">{{ number_format($character->cash, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Banktegoed -->
                    <div class="bg-slate-50/90 border border-slate-200/80 p-3 rounded-xl flex items-center gap-3">
                        <span class="w-9 h-9 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm shrink-0">
                            <i class="fa-solid fa-building-columns"></i>
                        </span>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Banktegoed</span>
                            <span class="font-black text-slate-900 text-sm block leading-none">{{ number_format($character->bank, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Gang Card (Exact match to gang card in mockup) -->
            <div class="lg:col-span-3 bg-purple-950 text-white rounded-2xl p-4 border border-purple-900 flex flex-col justify-between h-full shadow-sm text-center relative overflow-hidden">
                <div class="relative z-10 flex flex-col items-center">
                    <img src="{{ asset('assets/gangs/kantinebende.jpg') }}" alt="De Kantinebende" class="w-14 h-14 rounded-xl object-cover border border-purple-700 shadow-sm mb-2">
                    <h3 class="font-black text-xs uppercase tracking-wider text-amber-300">De Kantinebende</h3>
                    <p class="text-[10px] font-bold text-purple-300">Gang &bull; Leden: 12</p>
                </div>
                <div class="relative z-10 mt-3">
                    <a href="{{ route('gangs.index') }}" class="block w-full bg-[#6d28d9] hover:bg-[#5b21b6] text-white font-black text-xs py-2 px-3 rounded-xl shadow transition">
                        Bekijk gang &gt;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 6 STAT BADGES ROW (Kracht, Verdediging, Conditie, Intelligentie, Charisma, Discipline) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Kracht -->
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col items-center text-center">
            <span class="w-8 h-8 rounded-full bg-purple-100 text-purple-800 flex items-center justify-center text-xs mb-1.5 font-bold">
                <i class="fa-solid fa-hand-fist"></i>
            </span>
            <span class="text-[11px] font-bold text-slate-400">Kracht</span>
            <span class="text-lg font-black text-slate-900 leading-tight">{{ $character->strength }}</span>
        </div>

        <!-- Verdediging -->
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col items-center text-center">
            <span class="w-8 h-8 rounded-full bg-purple-100 text-purple-800 flex items-center justify-center text-xs mb-1.5 font-bold">
                <i class="fa-solid fa-shield"></i>
            </span>
            <span class="text-[11px] font-bold text-slate-400">Verdediging</span>
            <span class="text-lg font-black text-slate-900 leading-tight">{{ $character->defense }}</span>
        </div>

        <!-- Conditie -->
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col items-center text-center">
            <span class="w-8 h-8 rounded-full bg-purple-100 text-purple-800 flex items-center justify-center text-xs mb-1.5 font-bold">
                <i class="fa-solid fa-person-running"></i>
            </span>
            <span class="text-[11px] font-bold text-slate-400">Conditie</span>
            <span class="text-lg font-black text-slate-900 leading-tight">{{ $character->stamina }}</span>
        </div>

        <!-- Intelligentie -->
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col items-center text-center">
            <span class="w-8 h-8 rounded-full bg-purple-100 text-purple-800 flex items-center justify-center text-xs mb-1.5 font-bold">
                <i class="fa-solid fa-brain"></i>
            </span>
            <span class="text-[11px] font-bold text-slate-400">Intelligentie</span>
            <span class="text-lg font-black text-slate-900 leading-tight">{{ $character->intelligence }}</span>
        </div>

        <!-- Charisma -->
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col items-center text-center">
            <span class="w-8 h-8 rounded-full bg-purple-100 text-purple-800 flex items-center justify-center text-xs mb-1.5 font-bold">
                <i class="fa-solid fa-masks-theater"></i>
            </span>
            <span class="text-[11px] font-bold text-slate-400">Charisma</span>
            <span class="text-lg font-black text-slate-900 leading-tight">{{ $character->charisma }}</span>
        </div>

        <!-- Discipline -->
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col items-center text-center">
            <span class="w-8 h-8 rounded-full bg-purple-100 text-purple-800 flex items-center justify-center text-xs mb-1.5 font-bold">
                <i class="fa-solid fa-clipboard-check"></i>
            </span>
            <span class="text-[11px] font-bold text-slate-400">Discipline</span>
            <span class="text-lg font-black text-slate-900 leading-tight">{{ $character->discipline }}</span>
        </div>
    </div>

    <!-- BOTTOM TWO COLUMNS (Recente activiteiten & Actieve Missies) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left: Recente Activiteiten -->
        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide">
                    Recente activiteiten
                </h2>
                <span class="text-[11px] font-bold text-slate-400">Vandaag</span>
            </div>

            <div class="space-y-3">
                <!-- Activity 1 -->
                <div class="p-3 bg-slate-50/90 rounded-xl border border-slate-100 flex items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs shrink-0 font-bold">
                            <i class="fa-solid fa-circle-check"></i>
                        </span>
                        <div>
                            <p class="font-bold text-slate-800">Je hebt de missie 'Koffie en Kaas' voltooid!</p>
                            <p class="text-[11px] font-bold text-emerald-600 mt-0.5">+ 450 KC &bull; + 120 XP</p>
                        </div>
                    </div>
                    <span class="text-[10px] font-medium text-slate-400 shrink-0">2 uur geleden</span>
                </div>

                <!-- Activity 2 -->
                <div class="p-3 bg-slate-50/90 rounded-xl border border-slate-100 flex items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs shrink-0 font-bold">
                            <i class="fa-solid fa-bolt"></i>
                        </span>
                        <div>
                            <p class="font-bold text-slate-800">Je hebt 1 energie opgebruikt bij Training.</p>
                            <p class="text-[11px] font-bold text-indigo-600 mt-0.5">+ 75 XP</p>
                        </div>
                    </div>
                    <span class="text-[10px] font-medium text-slate-400 shrink-0">3 uur geleden</span>
                </div>

                <!-- Activity 3 -->
                <div class="p-3 bg-slate-50/90 rounded-xl border border-slate-100 flex items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-xs shrink-0 font-bold">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                        </span>
                        <div>
                            <p class="font-bold text-slate-800">Geld gestort in de kluis van De Bank.</p>
                            <p class="text-[11px] font-bold text-amber-700 mt-0.5">+ 1.200 KC beveiligd</p>
                        </div>
                    </div>
                    <span class="text-[10px] font-medium text-slate-400 shrink-0">Gisteren</span>
                </div>
            </div>
        </div>

        <!-- Right: Actieve Missies (Matches mockup exactly with orange buttons) -->
        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide">
                    Actieve Missies
                </h2>
                <a href="{{ route('missions.index') }}" class="text-xs font-bold text-purple-700 hover:underline">
                    Alle missies bekijken &rarr;
                </a>
            </div>

            <div class="space-y-3">
                @foreach($quickMissions as $index => $m)
                @php
                    $progressVal = match($index) {
                        0 => '0/500',
                        1 => '0/250',
                        default => '1/2',
                    };
                @endphp
                <div class="p-3.5 bg-slate-50/90 rounded-xl border border-slate-100 flex items-center justify-between gap-4 text-xs">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-lg bg-purple-900 text-amber-300 flex items-center justify-center text-sm shrink-0">
                            <i class="fa-solid fa-bullseye"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-black text-slate-900 text-xs truncate">{{ $m->title }}</h3>
                            <p class="text-[11px] text-slate-500 truncate">{{ $m->description }}</p>
                            <span class="text-[10px] font-bold text-slate-400 mt-0.5 block">{{ $progressVal }}</span>
                        </div>
                    </div>

                    <a href="{{ route('missions.index') }}" class="shrink-0 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-black text-xs py-2 px-3.5 rounded-xl shadow-xs transition cursor-pointer">
                        Naar missie
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
