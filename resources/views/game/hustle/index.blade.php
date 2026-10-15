@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
    
    <!-- Left column: Hosselen -->
    <div class="xl:col-span-8 space-y-6">
        <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 overflow-hidden">
            
            <!-- Clean TBS Kliniek Title Banner -->
            <div class="p-6 sm:p-7 text-slate-900 border-b border-slate-100 bg-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-purple-50 border border-purple-100 flex items-center justify-center text-xl text-purple-600 shadow-2xs shrink-0">
                            <i class="fa-solid fa-comments-dollar"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-[10px] font-black uppercase tracking-widest text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md border border-purple-200">TBS Kliniek Illegaal</span>
                            </div>
                            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Hosselen & Klussen</h1>
                            <p class="text-xs text-slate-500 mt-0.5">Scharrel wat bij tussen de corridors van de kliniek voor directe zakcenten en respect.</p>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $hustleCooldown = $character->cooldownRemaining('hustle');
            @endphp

            @if($hustleCooldown > 0)
                <div class="m-6 p-4 rounded-xl bg-amber-500/10 border border-amber-300 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-lg font-black shrink-0 shadow-xs">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-black text-amber-950">Hossel Cooldown Actief</h4>
                            <p class="text-[11px] text-amber-800">Wacht nog <strong>{{ gmdate('i:s', $hustleCooldown) }}</strong> voordat je opnieuw kunt klussen in de gangen.</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" onclick="window.tbsOpenSponsorAd('cooldown')" class="bg-purple-600 hover:bg-purple-700 active:scale-95 text-white text-xs font-black px-4 py-2 rounded-xl transition shadow-xs flex items-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-tv text-white text-[11px]"></i>
                            <span>Kijk Ad (Reset Cooldown)</span>
                        </button>
                        <form action="{{ route('cooldown.skip') }}" method="POST">
                            @csrf
                            <input type="hidden" name="action" value="hustle">
                            <button type="submit" class="bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-xs font-black px-3.5 py-2 rounded-xl transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-star text-white text-[11px]"></i>
                                <span>1 Ster ⭐</span>
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
                        <label class="flex items-center gap-4 p-4 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition group {{ $hustleCooldown > 0 ? 'opacity-60 cursor-not-allowed' : '' }}">
                            <input type="radio" name="hustle_id" value="{{ $h['id'] }}" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-slate-300" {{ $loop->first ? 'checked' : '' }} {{ $hustleCooldown > 0 ? 'disabled' : '' }} required>
                            <div class="flex-1 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div>
                                    <span class="font-bold text-xs text-slate-800 group-hover:text-slate-900 block">{{ $h['name'] }}</span>
                                    <span class="text-[11px] text-slate-500">{{ $h['description'] }}</span>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-600">{{ $h['cost'] }} Energie</span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700">€{{ $h['cash_min'] }}-€{{ $h['cash_max'] }}</span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-purple-50 text-purple-700">{{ $h['rate'] }}% kans</span>
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2 border-t border-slate-100">
                        <span class="text-[11px] font-bold text-slate-400 inline-flex items-center gap-1.5"><i class="fa-solid fa-stopwatch text-amber-500"></i> Elke hosselbeurt activeert een cooldown van 3 minuten.</span>
                        <button type="submit" 
                                {{ $hustleCooldown > 0 ? 'disabled' : '' }}
                                class="bg-slate-900 hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-xs px-8 py-3 rounded-xl transition shadow-xs flex items-center gap-2 cursor-pointer">
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
        <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center gap-3">
                <i class="fa-solid fa-chart-line text-indigo-600 text-sm"></i>
                <h2 class="text-slate-900 font-bold text-sm">Jouw Hosselstatus</h2>
            </div>
            <div class="divide-y divide-slate-100 text-xs">
                <div class="flex justify-between items-center p-4 hover:bg-slate-50 transition">
                    <span class="font-semibold text-slate-500">Huidige Energie</span>
                    <span class="font-extrabold text-emerald-700">{{ $character->energy }} / {{ $character->max_energy }}</span>
                </div>
                <div class="flex justify-between items-center p-4 hover:bg-slate-50 transition">
                    <span class="font-semibold text-slate-500">Contant op Zak</span>
                    <span class="font-extrabold text-amber-600">€{{ number_format($character->cash, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center p-4 hover:bg-slate-50 transition">
                    <span class="font-semibold text-slate-500">Huidig Respect</span>
                    <span class="font-extrabold text-indigo-700">{{ number_format($character->respect, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
