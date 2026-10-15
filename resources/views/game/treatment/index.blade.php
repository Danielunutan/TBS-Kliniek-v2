@extends('layouts.app')

@section('title', 'Behandelaarsgesprek & Verlofregeling - TBS Kliniek')

@section('content')
<div class="space-y-6">

    <!-- Header Banner in TBS Kliniek Styling -->
    <div class="bg-white p-6 rounded-lg border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 border border-purple-200 text-purple-700 flex items-center justify-center text-2xl shrink-0 shadow-xs">
                <i class="fa-solid fa-brain"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-slate-900 uppercase tracking-wide">Behandelaarsgesprek & Verlof</h1>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-purple-100 text-purple-800 border border-purple-200 uppercase">
                        Paviljoen 7
                    </span>
                </div>
                <p class="text-xs text-slate-600 mt-1 max-w-2xl">
                    Doorloop de periodieke psychologische evaluatie bij <strong>Dr. H. van Beek</strong> of <strong>Yvonne</strong>. Scoor een positief behandeladvies om <strong>3 uur Begeleid Verlof buiten de hekken</strong> vrij te spelen!
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3 self-start sm:self-auto shrink-0">
            <div class="px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-right">
                <span class="text-[10px] font-bold uppercase text-slate-500 block tracking-wider">Meewerkendheid</span>
                <span class="text-lg font-bold font-mono {{ ($character->treatment_score ?? 50) >= 60 ? 'text-emerald-600' : (($character->treatment_score ?? 50) >= 35 ? 'text-amber-600' : 'text-rose-600') }}">
                    {{ $character->treatment_score ?? 50 }}%
                </span>
            </div>
            <div class="px-4 py-2 rounded-lg bg-amber-50 border border-amber-200 text-right">
                <span class="text-[10px] font-bold uppercase text-amber-800 block tracking-wider">Verloftrips</span>
                <span class="text-lg font-bold text-slate-900 font-mono">
                    {{ $character->leave_trips_count ?? 0 }}x
                </span>
            </div>
        </div>
    </div>

    <!-- Active Status Alerts -->
    @if($character->isOnLeave())
    <div class="p-5 rounded-lg bg-gradient-to-r from-emerald-50 to-white border border-emerald-300 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xl shrink-0 shadow-xs animate-bounce">
                <i class="fa-solid fa-person-walking-arrow-right"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-black uppercase text-emerald-900 tracking-wide">Begeleid Verlof Actief!</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-200 text-emerald-900 animate-pulse">
                        Buiten de Hekken
                    </span>
                </div>
                <p class="text-xs text-emerald-800 mt-0.5">
                    Je mag je tijdelijk onder toezicht buiten de kliniek begeven. Voer hieronder exclusieve buitenactiviteiten uit voor cash en sterren!
                </p>
            </div>
        </div>
        <div class="text-right shrink-0">
            <span class="text-[10px] font-bold uppercase text-emerald-700 block">Resterende Verloftijd</span>
            <span class="text-base font-black text-emerald-950 font-mono">
                {{ gmdate('H:i:s', $character->getLeaveRemainingSeconds()) }}
            </span>
        </div>
    </div>
    @elseif($character->isSedated())
    <div class="p-4 rounded-lg bg-rose-50 border border-rose-300 shadow-sm flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <i class="fa-solid fa-syringe text-rose-600 text-2xl"></i>
            <div>
                <span class="text-xs font-bold uppercase text-rose-900 block">Verplichte Kalmering Actief (Sedatie)</span>
                <span class="text-xs text-rose-800">Je bent versuft door verplichte Haldol-medicatie wegens agressie tijdens je laatste gesprek.</span>
            </div>
        </div>
        <span class="text-xs font-mono font-bold text-rose-900 bg-rose-100 px-3 py-1 rounded-md border border-rose-200">
            {{ gmdate('i:s', $character->getSedatedRemainingSeconds()) }}
        </span>
    </div>
    @endif

    <!-- Grid: Spreekkamer + Buitenactiviteiten -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Linker Kolom (2/3): Het Behandelaarsgesprek -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Arts Dossier & 365-Dagen Casus van Vandaag -->
            <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-5 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-purple-800 bg-purple-50 px-2.5 py-0.5 rounded-full border border-purple-200 flex items-center gap-1.5">
                            <i class="fa-solid fa-calendar-day text-purple-600"></i>
                            <span>Dag {{ $dailyCase['day_of_year'] }} / 365 &bull; {{ $dailyCase['date_label'] }}</span>
                        </span>
                        <span class="text-xs font-mono font-bold text-slate-500">{{ $dailyCase['case_code'] }}</span>
                    </div>
                    <span class="text-[11px] font-bold text-slate-600 flex items-center gap-1.5">
                        <i class="fa-solid fa-location-dot text-rose-500"></i>
                        <span>{{ $dailyCase['location'] }}</span>
                    </span>
                </div>

                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-full bg-purple-50 border-2 border-purple-200 flex items-center justify-center text-purple-700 text-2xl shrink-0 shadow-xs">
                        <i class="fa-solid {{ $dailyCase['doctor']['avatar_icon'] }}"></i>
                    </div>
                    <div class="space-y-1 flex-1">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">{{ $dailyCase['doctor']['name'] }}</h3>
                                <span class="text-[11px] text-slate-500 font-medium">{{ $dailyCase['doctor']['role'] }} &bull; {{ $dailyCase['doctor']['big'] }}</span>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200 self-start sm:self-auto">
                                Dossier #{{ $character->id + 1042 }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 italic bg-slate-50 p-3 rounded-lg border border-slate-200/80 mt-2">
                            &ldquo;{{ $dailyCase['doctor']['quote'] }}&rdquo;
                        </p>
                    </div>
                </div>

                <!-- Dagelijkse Incident Rapportage Box -->
                <div class="bg-amber-50/80 border border-amber-200/90 rounded-lg p-3.5 text-xs text-amber-950 font-medium flex items-start gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-amber-600 text-base mt-0.5 shrink-0"></i>
                    <div>
                        <strong class="font-bold text-amber-950 block mb-0.5 uppercase tracking-wide text-[10px]">
                            Incident van Vandaag: {{ $dailyCase['title'] }}
                        </strong>
                        <span class="leading-relaxed text-amber-900">{{ $dailyCase['incident'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Als op cooldown staat -->
            @if($isOnCooldown)
            <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-6 text-center space-y-4">
                <div class="w-12 h-12 mx-auto rounded-full bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center text-xl shadow-xs">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Behandelaarsgesprek Afgerond</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                        Het behandelteam heeft je recente evaluatie verwerkt in het dossier. Je volgende officiële consultatie is beschikbaar over:
                    </p>
                    <div class="text-2xl font-black font-mono text-purple-700 mt-2">
                        {{ gmdate('H:i:s', $remainingCooldown) }}
                    </div>
                </div>

                <!-- Cooldown Skip via Ad or Stars -->
                <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                    <button type="button" 
                            onclick="window.tbsOpenSponsorAd('cooldown')" 
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-purple-600 hover:bg-purple-700 active:scale-95 text-white text-xs font-black transition shadow-xs cursor-pointer">
                        <i class="fa-solid fa-tv text-white"></i>
                        <span>Kijk Ad (Reset Wachttijd)</span>
                    </button>
                    <form action="{{ route('cooldown.skip') }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="treatment_session">
                        <button type="submit" 
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-xs font-bold transition shadow-xs cursor-pointer">
                            <i class="fa-solid fa-bolt text-amber-500"></i>
                            <span>Sla Over voor 2 Sterren ⭐</span>
                        </button>
                    </form>
                </div>
            </div>
            @else
            <!-- Formulier: De 3 Psychologische Dilemma's -->
            <form action="{{ route('treatment.submit') }}" method="POST" class="space-y-6">
                @csrf

                @php $step = 1; @endphp
                @foreach($activeQuestions as $qId => $q)
                <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-5 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-full bg-purple-700 text-white text-xs font-bold flex items-center justify-center">
                                {{ $step++ }}
                            </span>
                            <span class="text-xs font-bold text-slate-800 uppercase tracking-wide">
                                {{ $q['theme'] }}
                            </span>
                        </div>
                        <span class="text-[11px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-200">
                            {{ $dailyCase['doctor']['name'] }}
                        </span>
                    </div>

                    <p class="text-sm font-bold text-slate-900 leading-snug">
                        {{ $q['question'] }}
                    </p>

                    <!-- Opties A, B, C, D -->
                    <div class="space-y-2.5">
                        @foreach($q['options'] as $key => $opt)
                        <label class="flex items-start gap-3 p-3.5 rounded-lg border border-slate-200 hover:border-purple-300 hover:bg-purple-50/40 cursor-pointer transition group has-checked:border-purple-600 has-checked:bg-purple-50/60 has-checked:ring-1 has-checked:ring-purple-600">
                            <input type="radio" 
                                   name="answers[{{ $qId }}]" 
                                   value="{{ $key }}" 
                                   required 
                                   class="mt-0.5 text-purple-600 focus:ring-purple-500">
                            <div class="text-xs text-slate-700 font-medium group-hover:text-slate-900">
                                <span class="font-bold text-purple-800 uppercase mr-1">{{ strtoupper($key) }}.</span>
                                {{ $opt['text'] }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-4 px-6 rounded-lg font-bold text-base text-white bg-purple-700 hover:bg-purple-800 active:scale-98 transition shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-file-signature text-base"></i>
                    <span>Dossier & Antwoorden Indienen bij Behandelteam</span>
                </button>
            </form>
            @endif

        </div>

        <!-- Rechter Kolom (1/3): Buitenactiviteiten & Recent Dossier -->
        <div class="space-y-6">

            <!-- Verlof Activiteiten (Buiten de hekken) -->
            <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wide text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-tree-city text-emerald-600"></i>
                        <span>Verlof Activiteiten</span>
                    </h3>
                    @if($character->isOnLeave())
                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                        Toegankelijk
                    </span>
                    @else
                    <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                        Vergrendeld
                    </span>
                    @endif
                </div>

                @if(! $character->isOnLeave())
                <div class="p-4 rounded-lg bg-slate-50 border border-slate-200 text-center space-y-2">
                    <i class="fa-solid fa-lock text-slate-400 text-xl"></i>
                    <p class="text-xs text-slate-600 font-medium">
                        Deze activiteiten vinden plaats buiten de muren van de kliniek. Slaag eerst voor je Behandelaarsgesprek om 3 uur verlof te verdienen!
                    </p>
                </div>
                @endif

                <div class="space-y-3">
                    @foreach($leaveActions as $actionId => $act)
                    <div class="p-4 rounded-lg border {{ $character->isOnLeave() ? 'border-slate-200 bg-white hover:border-purple-300' : 'border-slate-100 bg-slate-50/60 opacity-75' }} transition space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid {{ $act['icon'] }} text-purple-700"></i>
                                {{ $act['name'] }}
                            </span>
                            <span class="text-[10px] font-bold text-slate-500">
                                {{ $act['energy'] }} Energie
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            {{ $act['desc'] }}
                        </p>

                        <div class="pt-1 flex items-center justify-between">
                            <span class="text-[10px] font-bold text-emerald-700">
                                +{{ $act['xp'] }} XP &bull; +{{ $act['respect'] }} Respect
                            </span>

                            @if($character->isOnLeave())
                            <form action="{{ route('treatment.leave-action') }}" method="POST">
                                @csrf
                                <input type="hidden" name="action_id" value="{{ $actionId }}">
                                <button type="submit" 
                                        :disabled="{{ $isLeaveActionOnCooldown ? 'true' : 'false' }}"
                                        class="px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-purple-700 hover:bg-purple-800 transition shadow-xs cursor-pointer flex items-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fa-solid fa-play text-[10px]"></i>
                                    <span>Uitvoeren</span>
                                </button>
                            </form>
                            @else
                            <span class="text-[10px] text-slate-400 font-bold">
                                Vereist Verlof
                            </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                @if($isLeaveActionOnCooldown && $character->isOnLeave())
                <div class="text-center text-xs font-bold text-amber-700 bg-amber-50 p-2.5 rounded-lg border border-amber-200">
                    ⏳ Begeleider houdt je in de gaten! Wacht nog {{ $remainingLeaveCooldown }}s.
                </div>
                @endif
            </div>

            <!-- Laatste Psychologisch Beoordelingsverslag -->
            <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-5 space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wide text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-2.5">
                    <i class="fa-solid fa-clipboard-check text-purple-700"></i>
                    <span>Laatste Evaluatieverslag</span>
                </h3>

                @if(! empty($character->treatment_history))
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs font-bold">
                        <span class="{{ $character->treatment_history['outcome'] === 'positive' ? 'text-emerald-700' : ($character->treatment_history['outcome'] === 'neutral' ? 'text-amber-700' : 'text-rose-700') }}">
                            {{ $character->treatment_history['title'] ?? 'Evaluatie' }}
                        </span>
                        <span class="text-slate-400 text-[10px] font-mono">
                            Score: {{ $character->treatment_history['score'] ?? 50 }}%
                        </span>
                    </div>

                    @if(! empty($character->treatment_history['notes']))
                    <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 text-[11px] text-slate-600 space-y-1 font-mono">
                        @foreach($character->treatment_history['notes'] as $note)
                        <div>{{ $note }}</div>
                        @endforeach
                    </div>
                    @endif

                    <span class="text-[10px] text-slate-400 block text-right">
                        Datum: {{ $character->treatment_history['date'] ?? '-' }}
                    </span>
                </div>
                @else
                <p class="text-xs text-slate-500 italic">
                    Nog geen formeel gespreksverslag vastgelegd in dit dossier. Voer je eerste gesprek hiernaast uit!
                </p>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection
