@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- TOP PRISON BANNER (Exact match to bottom-center-left mockup) -->
    <div class="bg-[#1c0b38] rounded-2xl border border-purple-900 shadow-lg p-5 sm:p-6 text-white overflow-hidden relative">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center relative z-10">
            <!-- Left: Inmate behind bars portrait -->
            <div class="md:col-span-3 flex justify-center md:justify-start">
                <div class="w-32 h-32 rounded-2xl overflow-hidden border-2 border-red-500/80 shadow-md relative shrink-0 bg-slate-900">
                    <img src="{{ asset('assets/illustrations/prison_cell.jpg') }}" alt="Isoleercel" class="w-full h-full object-cover">
                </div>
            </div>

            <!-- Middle: Sentence Status -->
            <div class="md:col-span-6 space-y-2 text-center md:text-left">
                <span class="inline-block px-2.5 py-0.5 rounded-full bg-red-900/80 text-red-200 text-[10px] font-black uppercase tracking-wider border border-red-700">
                    <i class="fa-solid fa-lock mr-1"></i> Isoleercel &bull; Beveiligingsregime
                </span>
                <h1 class="text-2xl font-black text-white tracking-tight">
                    Je zit vast in Paviljoen 13
                </h1>
                <p class="text-xs text-purple-200">
                    Resterende straf: <strong class="text-amber-400 text-sm">{{ $isImprisoned ? $remainingMinutes : '0' }} minuten</strong>
                </p>
                @if($character->prison_reason)
                <p class="text-[11px] text-purple-300 italic">Reden: {{ $character->prison_reason }}</p>
                @endif
            </div>

            <!-- Right: Action Buttons -->
            <div class="md:col-span-3 flex flex-col gap-2.5 justify-center">
                <form method="POST" action="{{ route('prison.escape') }}">
                    @csrf
                    <button type="submit" @if(! $isImprisoned || $character->energy < 25) disabled @endif class="w-full bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 disabled:opacity-40 disabled:cursor-not-allowed text-white font-black py-2.5 px-4 rounded-xl text-xs shadow-md transition cursor-pointer">
                        Ontsnappen
                    </button>
                </form>
                <a href="{{ route('shop.index') }}" class="w-full text-center bg-[#6d28d9] hover:bg-[#5b21b6] text-white font-black py-2.5 px-4 rounded-xl text-xs shadow transition">
                    Gevangeniswinkel
                </a>
            </div>
        </div>
    </div>

    <!-- BOTTOM SECTION: GEVANGENISMISSIES (Matches bottom of prison card in mockup) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide">
                Gevangenismissies
            </h2>
            <span class="text-xs font-bold text-slate-400">Verdien vrijheid en extra KC</span>
        </div>

        <div class="space-y-3">
            <!-- Mission 1: Bouwstenen -->
            <div class="p-3.5 bg-slate-50/90 rounded-xl border border-slate-100 flex items-center justify-between gap-4 text-xs">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-purple-100 text-purple-900 flex items-center justify-center text-sm font-black shrink-0">
                        <i class="fa-solid fa-cubes"></i>
                    </span>
                    <div>
                        <h3 class="font-black text-slate-900 text-xs">Bouwstenen</h3>
                        <p class="text-[11px] text-slate-500">Verdien 200 KC door stenen te bikken in de werkplaats.</p>
                        <span class="text-[10px] font-bold text-purple-700 mt-0.5 block">+ 50 XP</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('missions.execute', 1) }}">
                    @csrf
                    <button type="submit" class="bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-black text-xs py-2 px-4 rounded-xl shadow-xs transition cursor-pointer">
                        Start
                    </button>
                </form>
            </div>

            <!-- Mission 2: Klusjesman -->
            <div class="p-3.5 bg-slate-50/90 rounded-xl border border-slate-100 flex items-center justify-between gap-4 text-xs">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-purple-100 text-purple-900 flex items-center justify-center text-sm font-black shrink-0">
                        <i class="fa-solid fa-wrench"></i>
                    </span>
                    <div>
                        <h3 class="font-black text-slate-900 text-xs">Klusjesman</h3>
                        <p class="text-[11px] text-slate-500">Repareer 5 voorwerpen in de onderhoudskelder.</p>
                        <span class="text-[10px] font-bold text-purple-700 mt-0.5 block">+ 75 XP</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('missions.execute', 2) }}">
                    @csrf
                    <button type="submit" class="bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-black text-xs py-2 px-4 rounded-xl shadow-xs transition cursor-pointer">
                        Start
                    </button>
                </form>
            </div>

            <!-- Mission 3: Dilemma -->
            <div class="p-3.5 bg-slate-50/90 rounded-xl border border-slate-100 flex items-center justify-between gap-4 text-xs">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-purple-100 text-purple-900 flex items-center justify-center text-sm font-black shrink-0">
                        <i class="fa-solid fa-scale-balanced"></i>
                    </span>
                    <div>
                        <h3 class="font-black text-slate-900 text-xs">Dilemma</h3>
                        <p class="text-[11px] text-slate-500">Kies het juiste antwoord tijdens de hoorzitting bij Dokter Debbie.</p>
                        <span class="text-[10px] font-bold text-purple-700 mt-0.5 block">+ 100 XP</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('missions.execute', 3) }}">
                    @csrf
                    <button type="submit" class="bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-black text-xs py-2 px-4 rounded-xl shadow-xs transition cursor-pointer">
                        Start
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
