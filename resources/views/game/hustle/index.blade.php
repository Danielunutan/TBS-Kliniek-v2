@extends('layouts.app')

@section('title', 'Hosselen & Klussen - TBS Kliniek')

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
    
    <!-- Left column: Hosselen -->
    <div class="xl:col-span-8 space-y-6">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/80 dark:border-slate-800 overflow-hidden">
            
            <!-- Clean TBS Kliniek Title Banner -->
            <div class="p-6 sm:p-7 text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-950/60 border border-purple-100 dark:border-purple-800/60 flex items-center justify-center text-xl text-purple-600 dark:text-purple-400 shadow-2xs shrink-0">
                            <i class="fa-solid fa-comments-dollar"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-[10px] font-black uppercase tracking-widest text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/80 px-2 py-0.5 rounded-md border border-purple-200 dark:border-purple-800/60">TBS Kliniek Illegaal</span>
                            </div>
                            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">Hosselen & Klussen</h1>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Scharrel wat bij tussen de corridors van de kliniek voor directe zakcenten en respect.</p>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $hustleCooldown = $character->cooldownRemaining('hustle');
                $hustleSkipCost = app(\App\Services\CooldownService::class)->getSkipCost('hustle');
            @endphp

            @if($hustleCooldown > 0)
                <div class="m-6 p-4 rounded-xl bg-amber-500/10 dark:bg-amber-950/30 border border-amber-300 dark:border-amber-700/60 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-lg font-black shrink-0 shadow-xs">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-black text-amber-950 dark:text-amber-200">Hossel Cooldown Actief</h4>
                            <p class="text-[11px] text-amber-800 dark:text-amber-300">Wacht nog <strong>{{ gmdate('i:s', $hustleCooldown) }}</strong> voordat je opnieuw kunt klussen in de gangen.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" onclick="window.tbsOpenSponsorAd('cooldown')" class="h-9 px-3.5 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white text-xs font-black transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer whitespace-nowrap">
                            <i class="fa-solid fa-tv text-[11px]"></i>
                            <span>Kijk Ad (Reset Cooldown)</span>
                        </button>
                        <form action="{{ route('cooldown.skip') }}" method="POST" class="m-0 p-0 inline-flex">
                            @csrf
                            <input type="hidden" name="action" value="hustle">
                            <button type="submit" class="h-9 px-3.5 rounded-xl bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-xs font-black transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer whitespace-nowrap">
                                <i class="fa-solid fa-star text-[11px]"></i>
                                <span>{{ $hustleSkipCost }} Sterren ⭐</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="p-6">
                <form action="{{ route('hustle.attempt') }}" method="POST">
                    @csrf
                    <div class="space-y-3 mb-6">
                        @foreach($hustles as $h)
                        <label class="flex items-center gap-4 p-4 border rounded-xl cursor-pointer transition group border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/90 hover:bg-slate-50 dark:hover:bg-slate-800/80 has-[:checked]:border-purple-600 dark:has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50/70 dark:has-[:checked]:bg-purple-950/50 has-[:checked]:ring-2 has-[:checked]:ring-purple-500/20 {{ $hustleCooldown > 0 ? 'opacity-60 cursor-not-allowed' : '' }}">
                            <input type="radio" name="hustle_id" value="{{ $h['id'] }}" class="w-4 h-4 text-purple-600 focus:ring-purple-500 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 shrink-0" {{ $loop->first ? 'checked' : '' }} {{ $hustleCooldown > 0 ? 'disabled' : '' }} required>
                            <div class="flex-1 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div>
                                    <span class="font-bold text-xs text-slate-800 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 block transition">{{ $h['name'] }}</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400">{{ $h['description'] }}</span>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">{{ $h['cost'] }} Energie</span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">€{{ $h['cash_min'] }}-€{{ $h['cash_max'] }}</span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300">{{ $h['rate'] }}% kans</span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300" title="Cooldown wachttijd na uitvoering">⏳ {{ gmdate('i:s', $h['cooldown']) }}</span>
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 inline-flex items-center gap-1.5"><i class="fa-solid fa-stopwatch text-amber-500"></i> Cooldown wachttijd is afhankelijk van de slagingskans (1 tot 6 minuten).</span>
                        <button type="submit" 
                                {{ $hustleCooldown > 0 ? 'disabled' : '' }}
                                class="bg-purple-600 hover:bg-purple-700 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed text-white font-black text-xs px-8 py-3 rounded-xl transition shadow-xs flex items-center gap-2 cursor-pointer">
                            <span>Actie Uitvoeren</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Right Sidebar -->
    <div class="xl:col-span-4 space-y-6">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/80 dark:border-slate-800 overflow-hidden">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
                <i class="fa-solid fa-chart-line text-purple-600 dark:text-purple-400 text-sm"></i>
                <h2 class="text-slate-900 dark:text-white font-bold text-sm">Jouw Hosselstatus</h2>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                <div class="flex justify-between items-center p-4 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                    <span class="font-semibold text-slate-500 dark:text-slate-400">Huidige Energie</span>
                    <span class="font-extrabold text-emerald-700 dark:text-emerald-400">{{ $character->energy }} / {{ $character->max_energy }}</span>
                </div>
                <div class="flex justify-between items-center p-4 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                    <span class="font-semibold text-slate-500 dark:text-slate-400">Contant op Zak</span>
                    <span class="font-extrabold text-amber-600 dark:text-amber-400">€{{ number_format($character->cash, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center p-4 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                    <span class="font-semibold text-slate-500 dark:text-slate-400">Huidig Respect</span>
                    <span class="font-extrabold text-purple-700 dark:text-purple-400">{{ number_format($character->respect, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
