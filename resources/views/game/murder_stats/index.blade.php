@extends('layouts.app')

@section('content')
<div class="w-full mt-4" x-data="{ tab: 'vandaag' }">
    
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        
        <!-- Header -->
        <div class="p-6 flex items-center justify-between border-b border-slate-200">
            <h2 class="font-black text-xl text-slate-900 flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-sm border border-rose-200">
                    <i class="fa-solid fa-skull"></i>
                </span>
                <span>Geweldsstatistieken</span>
            </h2>
        </div>

        <!-- Tabs -->
        <div class="flex border-b border-slate-200">
            <button @click="tab = 'vandaag'" 
                    :class="tab === 'vandaag' ? 'border-b-2 border-purple-600 text-purple-800 bg-purple-50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'"
                    class="flex-1 py-4 text-sm font-bold transition">
                Meeste Geweld (vandaag)
            </button>
            <button @click="tab = 'week'" 
                    :class="tab === 'week' ? 'border-b-2 border-purple-600 text-purple-800 bg-purple-50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'"
                    class="flex-1 py-4 text-sm font-bold transition">
                Meeste Geweld (7 dagen)
            </button>
        </div>

        <!-- Content Vandaag -->
        <div x-show="tab === 'vandaag'">
            
            <!-- Patiënten Section -->
            <div class="bg-purple-50/50 p-3 border-b border-slate-200 flex items-center gap-2 text-purple-900 font-bold text-sm">
                <i class="fa-solid fa-user-injured text-purple-600"></i> Patiënten
            </div>

            <div class="grid grid-cols-2 divide-x divide-slate-200 text-center">
                <!-- Meeste Geweld -->
                <div>
                    <div class="bg-slate-50 text-[10px] uppercase font-bold tracking-widest text-slate-500 py-3 border-b border-slate-200">Meeste Geweld (Totaal)</div>
                    <div class="p-4 space-y-3 text-sm">
                        <div class="flex justify-between items-center"><span class="font-bold text-purple-700">Veyra</span> <span class="font-bold text-slate-600">6 slachtoffers</span></div>
                        <div class="flex justify-between items-center"><span class="font-bold text-purple-700">Zaphira</span> <span class="font-bold text-slate-600">3 slachtoffers</span></div>
                        <div class="flex justify-between items-center"><span class="font-bold text-purple-700">Sylvaine</span> <span class="font-bold text-slate-600">3 slachtoffers</span></div>
                        <div class="flex justify-between items-center"><span class="font-bold text-purple-700">Cassia</span> <span class="font-bold text-slate-600">2 slachtoffers</span></div>
                        <div class="flex justify-between items-center"><span class="font-bold text-purple-700">PoisonIvy</span> <span class="font-bold text-slate-600">1 slachtoffer</span></div>
                    </div>
                </div>
                <!-- Meeste Online Geweld -->
                <div>
                    <div class="bg-slate-50 text-[10px] uppercase font-bold tracking-widest text-slate-500 py-3 border-b border-slate-200">Meeste Online Geweld</div>
                    <div class="p-4 flex items-center justify-center h-full">
                        <span class="text-sm font-bold text-slate-400">Nog geen online slachtoffers</span>
                    </div>
                </div>
            </div>

            <!-- Bendes Section -->
            <div class="bg-purple-50/50 p-3 border-t border-b border-slate-200 flex items-center gap-2 text-purple-900 font-bold text-sm mt-4">
                <i class="fa-solid fa-users text-purple-600"></i> Bendes
            </div>

            <div class="grid grid-cols-2 divide-x divide-slate-200 text-center">
                <!-- Meeste Geweld -->
                <div>
                    <div class="bg-slate-50 text-[10px] uppercase font-bold tracking-widest text-slate-500 py-3 border-b border-slate-200">Meeste Geweld (Totaal)</div>
                    <div class="p-4 space-y-3 text-sm pb-8">
                        <div class="flex justify-between items-center"><span class="font-bold text-indigo-700">InnerCircle</span> <span class="font-bold text-slate-600">14 slachtoffers</span></div>
                        <div class="flex justify-between items-center"><span class="font-bold text-indigo-700">NoMercy</span> <span class="font-bold text-slate-600">2 slachtoffers</span></div>
                    </div>
                </div>
                <!-- Meeste Online Geweld -->
                <div>
                    <div class="bg-slate-50 text-[10px] uppercase font-bold tracking-widest text-slate-500 py-3 border-b border-slate-200">Meeste Online Geweld</div>
                    <div class="p-4 flex items-center justify-center h-full pb-8">
                        <span class="text-sm font-bold text-slate-400">Nog geen online slachtoffers</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Content Week (Hidden by default) -->
        <div x-show="tab === 'week'" style="display: none;" class="p-16 text-center">
            <i class="fa-solid fa-chart-bar text-4xl text-slate-200 mb-4"></i>
            <p class="text-sm font-bold text-slate-400">Onvoldoende data voor de afgelopen 7 dagen.</p>
        </div>

    </div>

</div>
@endsection
