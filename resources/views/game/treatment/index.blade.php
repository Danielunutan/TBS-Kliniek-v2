@extends('layouts.app')

@section('title', 'Behandelaarsgesprek & Verlofregeling - TBS Kliniek')

@section('content')
<div class="space-y-6">

    @if($character->isImprisoned())
    <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-2xl p-4 text-xs font-bold text-rose-700 dark:text-rose-300 flex items-center gap-3">
        <i class="fa-solid fa-lock text-rose-500 text-base shrink-0"></i>
        <span>Je zit momenteel in de isoleercel. Behandelaarsgesprekken en verlofactiviteiten zijn tijdelijk opgeschort.</span>
    </div>
    @elseif($character->isComatose() || $character->needsReviveSerum())
    <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-2xl p-4 text-xs font-bold text-rose-700 dark:text-rose-300 flex items-center gap-3">
        <i class="fa-solid fa-heart-pulse text-rose-500 text-base shrink-0"></i>
        <span>Je ligt momenteel op de Intensive Care en kunt niet deelnemen aan behandelaarsgesprekken.</span>
    </div>
    @endif

    <!-- Header Banner in TBS Kliniek Styling -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 text-purple-700 dark:text-purple-300 flex items-center justify-center text-2xl shrink-0 shadow-xs">
                <i class="fa-solid fa-brain"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white uppercase tracking-wide">Behandelaarsgesprek & Verlof</h1>
                    <span class="text-[10px] font-black px-2.5 py-0.5 rounded-full bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70 uppercase">
                        Paviljoen 7
                    </span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 max-w-2xl">
                    Doorloop de periodieke psychologische evaluatie bij <strong>Dr. H. van Beek</strong> of <strong>Yvonne</strong>. Scoor een positief behandeladvies om <strong>3 uur Begeleid Verlof buiten de hekken</strong> vrij te spelen!
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3 self-start sm:self-auto shrink-0">
            <div class="px-4 py-2 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-right">
                <span class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 block tracking-wider">Meewerkendheid</span>
                <span class="text-lg font-bold font-mono {{ ($character->treatment_score ?? 50) >= 60 ? 'text-emerald-600 dark:text-emerald-400' : (($character->treatment_score ?? 50) >= 35 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400') }}">
                    {{ $character->treatment_score ?? 50 }}%
                </span>
            </div>
            <div class="px-4 py-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/60 text-right">
                <span class="text-[10px] font-bold uppercase text-amber-800 dark:text-amber-300 block tracking-wider">Verloftrips</span>
                <span class="text-lg font-bold text-slate-900 dark:text-white font-mono">
                    {{ $character->leave_trips_count ?? 0 }}x
                </span>
            </div>
        </div>
    </div>

    <!-- Active Status Alerts -->
    @if($character->isOnLeave())
    <div class="p-5 rounded-2xl bg-gradient-to-r from-emerald-50 to-white dark:from-emerald-950/40 dark:to-slate-900 border border-emerald-300/80 dark:border-emerald-800/70 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-xl shrink-0 shadow-xs animate-bounce">
                <i class="fa-solid fa-person-walking-arrow-right"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-black uppercase text-emerald-900 dark:text-emerald-200 tracking-wide">Begeleid Verlof Actief!</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-200 dark:bg-emerald-900/60 text-emerald-900 dark:text-emerald-300 animate-pulse">
                        Buiten de Hekken
                    </span>
                </div>
                <p class="text-xs text-emerald-800 dark:text-emerald-300 mt-0.5">
                    Je mag je tijdelijk onder toezicht buiten de kliniek begeven. Voer hieronder exclusieve buitenactiviteiten uit voor cash en sterren!
                </p>
            </div>
        </div>
        <div class="text-right shrink-0">
            <span class="text-[10px] font-bold uppercase text-emerald-700 dark:text-emerald-400 block">Resterende Verloftijd</span>
            <span class="text-base font-black text-emerald-950 dark:text-emerald-100 font-mono">
                {{ gmdate('H:i:s', max(0, (int) $character->getLeaveRemainingSeconds())) }}
            </span>
        </div>
    </div>
    @elseif($character->isSedated())
    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-300/80 dark:border-rose-800/70 shadow-xs flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <i class="fa-solid fa-syringe text-rose-600 dark:text-rose-400 text-2xl"></i>
            <div>
                <span class="text-xs font-bold uppercase text-rose-900 dark:text-rose-200 block">Verplichte Kalmering Actief (Sedatie)</span>
                <span class="text-xs text-rose-800 dark:text-rose-300">Je bent versuft door verplichte Haldol-medicatie wegens agressie tijdens je laatste gesprek.</span>
            </div>
        </div>
        <span class="text-xs font-mono font-bold text-rose-900 dark:text-rose-200 bg-rose-100 dark:bg-rose-900/50 px-3 py-1 rounded-md border border-rose-200 dark:border-rose-700">
            {{ gmdate('i:s', max(0, (int) $character->getSedatedRemainingSeconds())) }}
        </span>
    </div>
    @endif

    <!-- Grid: Spreekkamer + Buitenactiviteiten -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Linker Kolom (2/3): Het Behandelaarsgesprek -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Arts Dossier & 365-Dagen Casus van Vandaag -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-5 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 px-2.5 py-0.5 rounded-full border border-purple-200/80 dark:border-purple-800/70 flex items-center gap-1.5">
                            <i class="fa-solid fa-calendar-day text-purple-600 dark:text-purple-400"></i>
                            <span>Dag {{ $dailyCase['day_of_year'] }} / 365 &bull; {{ $dailyCase['date_label'] }}</span>
                        </span>
                        <span class="text-xs font-mono font-bold text-slate-500 dark:text-slate-400">{{ $dailyCase['case_code'] }}</span>
                    </div>
                    <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                        <i class="fa-solid fa-location-dot text-rose-500"></i>
                        <span>{{ $dailyCase['location'] }}</span>
                    </span>
                </div>

                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-full bg-purple-50 dark:bg-purple-950/60 border-2 border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-purple-700 dark:text-purple-300 text-2xl shrink-0 shadow-xs">
                        <i class="fa-solid {{ $dailyCase['doctor']['avatar_icon'] }}"></i>
                    </div>
                    <div class="space-y-1 flex-1">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ $dailyCase['doctor']['name'] }}</h3>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">{{ $dailyCase['doctor']['role'] }} &bull; {{ $dailyCase['doctor']['big'] }}</span>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700 self-start sm:self-auto">
                                Dossier #{{ $character->id + 1042 }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-300 italic bg-slate-50/90 dark:bg-slate-800/60 p-3 rounded-xl border border-slate-200/80 dark:border-slate-700/70 mt-2">
                            &ldquo;{{ $dailyCase['doctor']['quote'] }}&rdquo;
                        </p>
                    </div>
                </div>

                <!-- Dagelijkse Incident Rapportage Box -->
                <div class="bg-amber-500/10 dark:bg-amber-950/50 border border-amber-300/80 dark:border-amber-700/60 rounded-xl p-4 text-xs flex items-start gap-3.5 shadow-2xs">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/20 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300 flex items-center justify-center shrink-0 text-sm mt-0.5">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div class="space-y-1 flex-1">
                        <strong class="font-black text-amber-900 dark:text-amber-300 block uppercase tracking-wider text-[11px]">
                            Incident van Vandaag: {{ $dailyCase['title'] }}
                        </strong>
                        <p class="leading-relaxed text-amber-950 dark:text-amber-100 font-medium text-xs">
                            {{ $dailyCase['incident'] }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Als op cooldown staat -->
            @if($isOnCooldown)
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-6 text-center space-y-4">
                <div class="w-12 h-12 mx-auto rounded-full bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-200/80 dark:border-amber-800/70 flex items-center justify-center text-xl shadow-xs">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Behandelaarsgesprek Afgerond</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-md mx-auto">
                        Het behandelteam heeft je recente evaluatie verwerkt in het dossier. Je volgende officiële consultatie is beschikbaar over:
                    </p>
                    <div class="text-2xl font-black font-mono text-purple-700 dark:text-purple-400 mt-2">
                        {{ gmdate('H:i:s', max(0, (int) $remainingCooldown)) }}
                    </div>
                </div>

                <!-- Cooldown Reset via Sterren -->
                <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                    <form method="POST" action="{{ route('cooldown.skip') }}" class="inline-block">
                        @csrf
                        <input type="hidden" name="action" value="treatment_session">
                        <button type="submit" 
                                @disabled($character->isImprisoned() || $character->isComatose() || ($character->stars ?? 0) < ($treatmentSkipCost ?? 35))
                                class="btn-star-gold inline-flex items-center gap-2 px-5 py-2.5 rounded-xl active:scale-95 text-slate-900 dark:text-white text-xs font-black transition-all duration-150 shadow-xs cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fa-solid fa-star text-[11px]"></i>
                            <span>Omzeilen ({{ $treatmentSkipCost ?? 35 }} Sterren)</span>
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
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-5 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-full bg-purple-600 text-white text-xs font-black flex items-center justify-center">
                                {{ $step++ }}
                            </span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wide">
                                {{ $q['theme'] }}
                            </span>
                        </div>
                        <span class="text-[11px] font-bold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 px-2 py-0.5 rounded-lg border border-purple-200/80 dark:border-purple-800/70">
                            {{ $dailyCase['doctor']['name'] }}
                        </span>
                    </div>

                    <p class="text-sm font-bold text-slate-900 dark:text-white leading-snug">
                        {{ $q['question'] }}
                    </p>

                    <!-- Opties A, B, C, D -->
                    <div class="space-y-2.5">
                        @foreach($q['options'] as $key => $opt)
                        <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-purple-300 dark:hover:border-purple-500 hover:bg-purple-50/40 dark:hover:bg-purple-950/40 cursor-pointer transition-all duration-150 group has-checked:border-purple-600 dark:has-checked:border-purple-500 has-checked:bg-purple-50/60 dark:has-checked:bg-purple-950/60 has-checked:ring-1 has-checked:ring-purple-600">
                            <input type="radio" 
                                   name="answers[{{ $qId }}]" 
                                   value="{{ $key }}" 
                                   required 
                                   class="mt-0.5 text-purple-600 focus:ring-purple-500">
                            <div class="text-xs text-slate-700 dark:text-slate-200 font-medium group-hover:text-slate-900 dark:group-hover:text-white">
                                <span class="font-bold text-purple-800 dark:text-purple-400 uppercase mr-1">{{ strtoupper($key) }}.</span>
                                {{ $opt['text'] }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach

                <!-- Submit Button -->
                <button type="submit" 
                        @disabled($character->isImprisoned() || $character->isComatose())
                        class="w-full py-4 px-6 rounded-xl font-black text-sm uppercase tracking-wider text-white bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 transition-all duration-150 shadow-xs flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-file-signature text-base"></i>
                    <span>Dossier & Antwoorden Indienen bij Behandelteam</span>
                </button>
            </form>
            @endif

        </div>

        <!-- Rechter Kolom (1/3): Buitenactiviteiten & Recent Dossier -->
        <div class="space-y-6">

            <!-- Verlof Activiteiten (Buiten de hekken) -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wide text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-tree-city text-emerald-600 dark:text-emerald-400"></i>
                        <span>Verlof Activiteiten</span>
                    </h3>
                    @if($character->isOnLeave())
                    <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded-full border border-emerald-200/80 dark:border-emerald-800/70">
                        Toegankelijk
                    </span>
                    @else
                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full border border-slate-200/80 dark:border-slate-700">
                        Vergrendeld
                    </span>
                    @endif
                </div>

                @if(! $character->isOnLeave())
                <div class="p-4 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-center space-y-2">
                    <i class="fa-solid fa-lock text-slate-400 dark:text-slate-500 text-xl"></i>
                    <p class="text-xs text-slate-600 dark:text-slate-400 font-medium">
                        Deze activiteiten vinden plaats buiten de muren van de kliniek. Slaag eerst voor je Behandelaarsgesprek om 3 uur verlof te verdienen!
                    </p>
                </div>
                @endif

                <div class="space-y-3">
                    @foreach($leaveActions as $actionId => $act)
                    <div class="p-4 rounded-xl border {{ $character->isOnLeave() ? 'border-slate-200/80 dark:border-slate-700/70 bg-slate-50/90 dark:bg-slate-800/60 hover:border-purple-300 dark:hover:border-purple-600' : 'border-slate-200/60 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 opacity-75' }} transition-all duration-150 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <i class="fa-solid {{ $act['icon'] }} text-purple-700 dark:text-purple-400"></i>
                                {{ $act['name'] }}
                            </span>
                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">
                                {{ $act['energy'] }} Energie
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                            {{ $act['desc'] }}
                        </p>

                        <div class="pt-1 flex items-center justify-between">
                            <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400">
                                +{{ $act['xp'] }} XP &bull; +{{ $act['respect'] }} Respect
                            </span>

                            @if($character->isOnLeave())
                            <form action="{{ route('treatment.leave-action') }}" method="POST">
                                @csrf
                                <input type="hidden" name="action_id" value="{{ $actionId }}">
                                <button type="submit" 
                                        @disabled($isLeaveActionOnCooldown || $character->isImprisoned() || $character->isComatose())
                                        class="px-3 py-1.5 rounded-xl text-xs font-black text-white bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 transition-all duration-150 shadow-xs cursor-pointer flex items-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fa-solid fa-play text-[10px]"></i>
                                    <span>Uitvoeren</span>
                                </button>
                            </form>
                            @else
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 font-bold">
                                Vereist Verlof
                            </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                @if($isLeaveActionOnCooldown && $character->isOnLeave())
                <div class="text-center text-xs font-bold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/50 p-2.5 rounded-xl border border-amber-200 dark:border-amber-800">
                    ⏳ Begeleider houdt je in de gaten! Wacht nog {{ $remainingLeaveCooldown }}s.
                </div>
                @endif
            </div>

            <!-- Laatste Psychologisch Beoordelingsverslag -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-5 space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wide text-slate-900 dark:text-white flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-2.5">
                    <i class="fa-solid fa-clipboard-check text-purple-700 dark:text-purple-400"></i>
                    <span>Laatste Evaluatieverslag</span>
                </h3>

                @if(! empty($character->treatment_history))
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs font-bold">
                        <span class="{{ ($character->treatment_history['outcome'] ?? '') === 'positive' ? 'text-emerald-700 dark:text-emerald-400' : (($character->treatment_history['outcome'] ?? '') === 'neutral' ? 'text-amber-700 dark:text-amber-400' : 'text-rose-700 dark:text-rose-400') }}">
                            {{ $character->treatment_history['title'] ?? 'Evaluatie' }}
                        </span>
                        <span class="text-slate-400 dark:text-slate-500 text-[10px] font-mono">
                            Score: {{ $character->treatment_history['score'] ?? 50 }}%
                        </span>
                    </div>

                    @if(! empty($character->treatment_history['notes']))
                    <div class="bg-slate-50/90 dark:bg-slate-800/60 p-3 rounded-xl border border-slate-200/80 dark:border-slate-700/70 text-[11px] text-slate-600 dark:text-slate-300 space-y-1 font-mono">
                        @foreach($character->treatment_history['notes'] as $note)
                        <div>{{ $note }}</div>
                        @endforeach
                    </div>
                    @endif

                    <span class="text-[10px] text-slate-400 dark:text-slate-500 block text-right">
                        Datum: {{ $character->treatment_history['date'] ?? '-' }}
                    </span>
                </div>
                @else
                <p class="text-xs text-slate-500 dark:text-slate-400 italic">
                    Nog geen formeel gespreksverslag vastgelegd in dit dossier. Voer je eerste gesprek hiernaast uit!
                </p>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection
