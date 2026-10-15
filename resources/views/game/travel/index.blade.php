@extends('layouts.app')

@section('content')
<div class="space-y-6 mt-4">

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 text-sm font-bold flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-emerald-500 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 text-rose-800 dark:text-rose-300 text-sm font-bold flex items-center gap-3">
            <i class="fa-solid fa-triangle-exclamation text-rose-500 text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xs border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="bg-slate-50 dark:bg-slate-800/60 p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-teal-50 dark:bg-teal-950/60 border border-teal-200 dark:border-teal-800/60 flex items-center justify-center text-teal-600 dark:text-teal-400">
                    <i class="fa-solid fa-map-location-dot text-lg"></i>
                </div>
                <div>
                    <h2 class="text-slate-900 dark:text-white font-black text-lg">Verplaatsen (Afdelingen Reizen)</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Beweeg je discreet door het kliniekcomplex door bewakers om te kopen.</p>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Huidige locatie:</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/60">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    {{ $character->current_location ?? 'Paviljoen 1 (Gesloten)' }}
                </span>
            </div>
        </div>

        <div class="p-6 sm:p-8">
            <div class="max-w-2xl mx-auto text-center mb-8">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold mb-3 border border-slate-200 dark:border-slate-700">
                    <i class="fa-solid fa-hand-holding-dollar text-amber-500"></i>
                    <span>Omkoopkosten bewakers: <strong class="text-purple-600 dark:text-purple-400">€{{ number_format($travelCost, 0, ',', '.') }}</strong> contant</span>
                    @if($character->isVip())
                        <span class="ml-1 text-[10px] font-black uppercase text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/50 px-1.5 py-0.5 rounded border border-amber-300 dark:border-amber-800/60">50% VIP KORTING</span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Selecteer een afdeling om naar toe te reizen. Elke succesvolle verplaatsing levert bovendien <strong>+35 XP</strong> op.
                </p>
            </div>

            <form action="{{ route('travel.move') }}" method="POST" class="max-w-2xl mx-auto" x-data="{ selected: null }">
                @csrf

                <div class="space-y-3 mb-8">
                    @foreach($locations as $key => $loc)
                        @php
                            $isCurrent = ($character->current_location ?? 'Paviljoen 1 (Gesloten)') === $loc['name'];
                        @endphp
                        <label class="flex items-center justify-between p-4 rounded-xl border transition cursor-pointer select-none
                            {{ $isCurrent 
                                ? 'bg-slate-50/70 dark:bg-slate-800/40 border-slate-200 dark:border-slate-800 opacity-60 cursor-not-allowed' 
                                : 'border-slate-200 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700 hover:bg-purple-50/30 dark:hover:bg-purple-950/20' }}"
                            :class="selected === '{{ $key }}' ? 'ring-2 ring-purple-500 border-purple-500 bg-purple-50/50 dark:bg-purple-950/30' : ''">
                            
                            <div class="flex items-center gap-3.5">
                                <input type="radio" 
                                       name="location" 
                                       value="{{ $key }}" 
                                       class="w-4 h-4 text-purple-600 border-slate-300 dark:border-slate-700 focus:ring-purple-500" 
                                       @disabled($isCurrent)
                                       @click="selected = '{{ $key }}'">
                                <div class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 shrink-0">
                                    <i class="fa-solid {{ $loc['icon'] }}"></i>
                                </div>
                                <div class="text-left">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-black text-slate-800 dark:text-slate-100">{{ $loc['name'] }}</span>
                                        @if($isCurrent)
                                            <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                                                Hier ben je nu
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $loc['desc'] }}</p>
                                </div>
                            </div>

                            <span class="hidden sm:inline-block text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 shrink-0 ml-2">
                                {{ $loc['badge'] }}
                            </span>
                        </label>
                    @endforeach
                </div>

                <div class="text-center">
                    <button type="submit" 
                            :disabled="!selected"
                            class="inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl bg-purple-600 hover:bg-purple-500 disabled:opacity-40 disabled:cursor-not-allowed text-white font-black text-sm uppercase tracking-wider transition shadow-sm w-full sm:w-auto">
                        <i class="fa-solid fa-person-walking-arrow-right"></i>
                        <span>Verplaatsen naar afdeling</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
