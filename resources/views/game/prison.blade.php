@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Isolation Cell Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-slate-900 via-purple-950 to-slate-900 p-6 text-white text-center border-b-2 border-red-700">
            <div class="w-16 h-16 rounded-full bg-red-950/80 border-2 border-red-500 text-red-400 mx-auto flex items-center justify-center text-2xl mb-3 shadow-inner">
                <i class="fa-solid fa-lock"></i>
            </div>
            <h1 class="text-2xl font-black uppercase tracking-wider text-white">
                De Isoleercel (Paviljoen 0)
            </h1>
            <p class="text-xs text-slate-300 mt-1 max-w-lg mx-auto">
                Geen frikandellen. Geen bezoek. Alleen een koude betonnen vloer en het gezoem van een tl-buis.
            </p>
        </div>

        <div class="p-6 space-y-6">
            @if($isImprisoned)
            <!-- Incarcerated Status -->
            <div class="bg-red-50 border-2 border-red-300 rounded-xl p-5 text-center space-y-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-200 text-red-900 font-black text-xs uppercase tracking-wider">
                    <i class="fa-solid fa-triangle-exclamation"></i> Actieve Straf
                </span>
                <div>
                    <p class="text-xs font-semibold text-slate-600">Reden van opsluiting:</p>
                    <p class="text-sm font-bold text-slate-900 italic mt-0.5">"{{ $character->prison_reason }}"</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-600">Resterende Straftijd:</p>
                    <p class="text-3xl font-black text-red-700">{{ $remainingMinutes }} <span class="text-base font-bold text-slate-700">minuten</span></p>
                    <p class="text-[11px] text-slate-500 mt-1">Automatische vrijlating zodra de timer afloopt.</p>
                </div>
            </div>

            <!-- Escape Action -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 space-y-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide flex items-center gap-2">
                            <i class="fa-solid fa-person-running text-amber-600"></i>
                            <span>Ontsnappingspoging Wagen</span>
                        </h2>
                        <p class="text-xs text-slate-600 mt-1">
                            Kruip via het ventilatierooster richting de wasruimte. Vereist <strong class="text-yellow-700">25 Energie</strong>. Slagingskans hangt af van je Intelligentie en Conditie.
                        </p>
                    </div>
                    <span class="text-xs font-bold text-amber-800 bg-amber-100 px-2 py-1 rounded border border-amber-200 shrink-0">
                        25 Energie
                    </span>
                </div>

                <div class="bg-amber-50 p-3 rounded border border-amber-200 text-xs text-amber-900 flex items-start gap-2">
                    <i class="fa-solid fa-circle-exclamation text-amber-600 mt-0.5"></i>
                    <div>
                        <strong>Risico:</strong> Mislukt de ontsnapping? Dan deelt Bewaker Barry een strafverlenging van <strong>+5 minuten</strong> uit en verlies je 3 respect!
                    </div>
                </div>

                <form method="POST" action="{{ route('prison.escape') }}">
                    @csrf
                    <button type="submit" @if($character->energy < 25) disabled @endif class="w-full bg-amber-500 hover:bg-amber-400 disabled:opacity-40 disabled:cursor-not-allowed text-purple-950 font-black py-3 px-4 rounded-lg text-sm shadow transition flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-door-open"></i>
                        <span>Ontsnappen Proberen (Waag de Grote Sprong)</span>
                    </button>
                </form>
            </div>
            @else
            <!-- Free Status -->
            <div class="bg-emerald-50 border-2 border-emerald-300 rounded-xl p-6 text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 mx-auto flex items-center justify-center text-xl">
                    <i class="fa-solid fa-check"></i>
                </div>
                <h2 class="text-lg font-black text-emerald-900">Je bent op vrije voeten!</h2>
                <p class="text-xs text-slate-600 max-w-md mx-auto">
                    Je zit momenteel niet vast in de isoleercel. Hou je gedeisd of zorg dat de bewaking je niet te pakken krijgt bij je volgende diefstal.
                </p>
                <div class="pt-2">
                    <a href="{{ route('dashboard') }}" class="inline-block bg-purple-900 hover:bg-purple-800 text-white font-bold text-xs px-5 py-2.5 rounded shadow">
                        Terug naar Hoofdkwartier
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
