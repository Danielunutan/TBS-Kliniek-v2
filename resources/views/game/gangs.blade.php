@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-slate-900 uppercase tracking-wide flex items-center gap-2">
                <i class="fa-solid fa-users text-purple-700"></i>
                <span>Gangs & Bendes van de Kliniek</span>
            </h1>
            <p class="text-xs text-slate-600 mt-1">
                Alleen red je het niet in de kliniek. Sluit je aan bij een rivaliserende bende, stort geld in de gezamenlijke gangkas en domineer de afdelingen.
            </p>
        </div>
        @if($myGang)
        <div class="bg-purple-950 text-white px-4 py-2 rounded-lg text-xs font-semibold border border-purple-800">
            <span>Jouw Gang: <strong class="text-amber-400">[{{ $myGang->tag }}] {{ $myGang->name }}</strong></span>
        </div>
        @endif
    </div>

    <!-- Gangs Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($gangs as $gang)
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
            <div class="p-5 space-y-3">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-purple-900 text-amber-300 flex items-center justify-center text-xl shrink-0 shadow-inner">
                            <i class="fa-solid {{ $gang->emblem }}"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-black text-slate-900">[{{ $gang->tag }}] {{ $gang->name }}</h2>
                            <span class="inline-block text-[10px] uppercase font-bold px-1.5 py-0.2 rounded bg-purple-100 text-purple-900">
                                {{ $gang->specialty }}
                            </span>
                        </div>
                    </div>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-2.5 rounded border border-slate-100 min-h-[50px]">
                    {{ $gang->description }}
                </p>

                <div class="space-y-1.5 pt-1 text-xs">
                    <div class="flex justify-between items-center text-slate-600">
                        <span>Leider:</span>
                        <strong class="text-slate-900">{{ $gang->leader?->name ?? 'Onbekend' }}</strong>
                    </div>
                    <div class="flex justify-between items-center text-slate-600">
                        <span>Aantal Leden:</span>
                        <strong class="text-purple-900 font-black">{{ $gang->members_count }} bewoners</strong>
                    </div>
                    <div class="flex justify-between items-center text-slate-600">
                        <span>Gangkas:</span>
                        <strong class="text-emerald-700 font-black">{{ number_format($gang->treasury, 0, ',', '.') }} KC</strong>
                    </div>
                </div>
            </div>

            <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 text-center">
                @if($character->gang_id === $gang->id)
                    <span class="inline-block text-xs font-black text-emerald-700">
                        <i class="fa-solid fa-circle-check mr-1"></i> Je bent lid van deze gang
                    </span>
                @else
                    <span class="inline-block text-xs font-bold text-slate-400">
                        <i class="fa-solid fa-lock mr-1"></i> Toelating via Gangleider
                    </span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
