@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6" x-data="{ search: '', activeTab: 'all' }">

    <!-- Left Column: Search & Filter Tabs -->
    <div class="lg:col-span-3 space-y-4">
        <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 overflow-hidden p-3 space-y-2">
            <button @click="activeTab = 'all'" 
                :class="activeTab === 'all' ? 'bg-slate-900 text-white font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'" 
                class="w-full px-3.5 py-2.5 rounded-lg text-xs font-semibold flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="fa-solid fa-trophy text-amber-500"></i>
                    <span>Algemene Ranglijst</span>
                </span>
                <span class="text-[10px] font-bold opacity-80">{{ count($leaderboard ?? $topCharacters ?? []) }}</span>
            </button>

            <button @click="activeTab = 'top10'" 
                :class="activeTab === 'top10' ? 'bg-slate-900 text-white font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'" 
                class="w-full px-3.5 py-2.5 rounded-lg text-xs font-semibold flex items-center justify-between transition">
                <span class="flex items-center gap-2.5">
                    <i class="fa-solid fa-crown text-amber-500"></i>
                    <span>Top 10 Spelers</span>
                </span>
                <span class="text-[10px] font-bold opacity-80">10</span>
            </button>

            <!-- Search Box -->
            <div class="pt-2 border-t border-slate-100">
                <div class="relative">
                    <input type="text" x-model="search" placeholder="Zoek op naam..." class="w-full bg-slate-50 text-slate-800 text-xs px-3 py-2 pl-8 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                    <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 p-4">
            <h4 class="text-xs font-bold text-slate-900 mb-1">Jouw Positie</h4>
            <p class="text-xs text-slate-500 mb-3">Verdien XP en respect door missies, gevechten en hosselen om te stijgen.</p>
            <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-lg border border-slate-100">
                <span class="text-xs font-bold text-slate-700">{{ $character->name }}</span>
                <span class="text-xs font-extrabold text-indigo-600">{{ number_format($character->respect, 0, ',', '.') }} Resp.</span>
            </div>
        </div>
    </div>

    <!-- Right Column: The Leaderboard -->
    <div class="lg:col-span-9">
        <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 overflow-hidden">
            
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-bold text-slate-900">Patiënten Ranglijst</h1>
                    <p class="text-xs text-slate-500">De meest gevreesde en invloedrijke patiënten van de TBS kliniek.</p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm border border-amber-100">
                    <i class="fa-solid fa-trophy"></i>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-[10px] uppercase tracking-wider font-bold text-slate-400 border-b border-slate-100">
                            <th class="p-3.5 w-14 text-center">Positie</th>
                            <th class="p-3.5">Patiënt</th>
                            <th class="p-3.5">Bende</th>
                            <th class="p-3.5 text-right">Respect</th>
                            <th class="p-3.5 w-24 text-right">Actie</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @foreach(($leaderboard ?? $topCharacters ?? []) as $index => $player)
                            <tr x-show="(!search || '{{ strtolower($player->name) }}'.includes(search.toLowerCase())) && (activeTab === 'all' || {{ $index }} < 10)" 
                                class="hover:bg-slate-50/80 transition-colors {{ $player->id === $character->id ? 'bg-indigo-50/40 font-semibold' : '' }}">
                                
                                <td class="p-3.5 text-center font-bold">
                                    @if($index === 0)
                                        <span class="w-7 h-7 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center mx-auto text-xs font-black shadow-2xs">
                                            <i class="fa-solid fa-crown text-[10px]"></i>
                                        </span>
                                    @elseif($index === 1)
                                        <span class="w-7 h-7 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center mx-auto text-xs font-black">
                                            2
                                        </span>
                                    @elseif($index === 2)
                                        <span class="w-7 h-7 rounded-full bg-amber-50 text-amber-800 border border-amber-200 flex items-center justify-center mx-auto text-xs font-black">
                                            3
                                        </span>
                                    @else
                                        <span class="text-slate-400 font-bold">{{ $index + 1 }}</span>
                                    @endif
                                </td>

                                <td class="p-3.5">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $player->avatar_url }}" alt="{{ $player->name }}" class="w-8 h-8 rounded-lg object-cover border border-slate-200">
                                        <div>
                                            <a href="{{ route('profile.show', $player->name) }}" class="font-bold text-slate-900 hover:text-indigo-600 transition block leading-tight">
                                                {{ $player->name }}
                                            </a>
                                            <span class="text-[10px] font-semibold text-slate-400">Level {{ $player->level }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="p-3.5">
                                    @if($player->gang)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            <i class="fa-solid fa-users text-[10px] text-slate-400"></i>
                                            {{ $player->gang->name }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-[11px] italic">Geen bende</span>
                                    @endif
                                </td>

                                <td class="p-3.5 text-right font-extrabold text-slate-900">
                                    {{ number_format($player->respect, 0, ',', '.') }}
                                </td>

                                <td class="p-3.5 text-right">
                                    <a href="{{ route('profile.show', $player->name) }}" class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition inline-block">
                                        Bekijk
                                    </a>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>
@endsection
