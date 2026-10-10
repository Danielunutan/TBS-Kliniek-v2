@extends('layouts.app')

@section('content')
<div class="space-y-6 mt-4">
    
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-50 p-5 border-b border-slate-100 flex items-center gap-3">
            <i class="fa-solid fa-map-location-dot text-indigo-500 text-lg"></i>
            <h2 class="text-[#1e0b38] font-bold text-lg">Verplaatsen (Reizen)</h2>
        </div>
        
        <div class="p-6 sm:p-8">
            <div class="text-center mb-8">
                <p class="text-sm font-bold text-slate-700 mb-2">
                    Hier kun je je verplaatsen naar een andere afdeling binnen de kliniek.
                </p>
                <p class="text-xs font-bold text-rose-600">
                    De verplaatsingskosten (bewakers omkopen) zijn € 78.408.
                </p>
            </div>

            <form action="#" method="POST" class="max-w-xl mx-auto">
                @csrf
                
                <div class="space-y-2 mb-8" x-data="{ selected: null }">
                    
                    @php
                        $locations = [
                            'Paviljoen 1 (Gesloten)',
                            'Paviljoen 2 (Half-open)',
                            'De Buitenplaats (Resocialisatie)',
                            'Het Kookhuis (Keuken)',
                            'De Luchtplaats',
                            'Administratie',
                            'Bezoekerscentrum',
                            'Recreatiezaal'
                        ];
                    @endphp

                    @foreach($locations as $loc)
                        <label class="flex items-center gap-4 p-4 rounded-lg border border-slate-200 cursor-pointer transition"
                               :class="selected === '{{ Str::slug($loc) }}' ? 'bg-purple-50 border-purple-300 shadow-sm' : 'hover:bg-slate-50'">
                            <input type="radio" name="location" value="{{ Str::slug($loc) }}" class="w-5 h-5 text-purple-600 border-slate-300 focus:ring-purple-500" @click="selected = '{{ Str::slug($loc) }}'">
                            <span class="text-sm font-bold text-slate-700">{{ $loc }}</span>
                        </label>
                    @endforeach

                </div>

                <div class="text-center">
                    <button type="submit" class="bg-[#4c3182] hover:bg-purple-800 text-white font-bold text-sm uppercase px-12 py-4 rounded-lg transition shadow-md w-full sm:w-auto">
                        Verplaatsen
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
