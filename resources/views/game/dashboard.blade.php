@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

    <!-- Main Content (Left) -->
    <div class="xl:col-span-8 space-y-6">
        
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
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">KliniekCash</p>
                        <p class="text-lg font-bold text-slate-900 leading-none">{{ number_format($character->cash, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center shadow-inner">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Banktegoed</p>
                        <p class="text-lg font-bold text-slate-900 leading-none">{{ number_format($character->bank, 0, ',', '.') }}</p>
                    </div>
                </div>
                <!-- You can add a third stat here or leave it empty/span -->
            </div>
        </div>

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
                    <span class="text-lg font-bold text-slate-900">25</span>
                </div>
                <div class="flex flex-col items-center justify-center p-3 rounded-lg hover:bg-slate-50 transition">
                    <i class="fa-solid fa-face-smile-wink text-amber-500 text-xl mb-2"></i>
                    <span class="text-xs font-bold text-slate-500">Charisma</span>
                    <span class="text-lg font-bold text-slate-900">20</span>
                </div>
                <div class="flex flex-col items-center justify-center p-3 rounded-lg hover:bg-slate-50 transition">
                    <i class="fa-solid fa-scale-balanced text-sky-500 text-xl mb-2"></i>
                    <span class="text-xs font-bold text-slate-500">Discipline</span>
                    <span class="text-lg font-bold text-slate-900">18</span>
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
                            <p class="text-xs font-bold text-emerald-600 mt-1">+ 450 KC &nbsp; + 120 XP</p>
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
        
        <!-- Gang Card -->
        <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 overflow-hidden relative group">
            <div class="p-6 flex flex-col items-center text-center">
                <div class="w-16 h-16 mb-3 rounded-2xl bg-slate-900 text-amber-400 flex items-center justify-center text-2xl shadow-sm group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-crown"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-0.5">De Kantinebende</h3>
                <span class="text-xs font-semibold text-slate-400 mb-4">Officiële Gang</span>
                <div class="w-full border-t border-slate-100 pt-3 mb-4 flex justify-between items-center text-xs font-bold">
                    <span class="text-slate-500">Leden:</span>
                    <span class="text-slate-900">12 actief</span>
                </div>
                <a href="{{ route('gangs.index') }}" class="block w-full text-center bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 rounded-xl transition text-xs shadow-xs">
                    Bekijk Bende <i class="fa-solid fa-chevron-right text-[10px] ml-1"></i>
                </a>
            </div>
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
                        <div class="w-12 h-12 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center text-xl shadow-sm">
                            <i class="fa-solid fa-dumbbell"></i>
                        </div>
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
