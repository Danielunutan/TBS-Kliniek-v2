@extends('layouts.app')

@section('title', 'True Crime Dossiers met Caroline - TBS Kliniek')

@section('content')
<div class="space-y-6" x-data="tbsTrueCrime({{ (int) ($cooldownRemaining ?? 0) }}, {{ $character->isImprisoned() ? 'true' : 'false' }}, {{ ($character->isComatose() || $character->needsReviveSerum()) ? 'true' : 'false' }})">

    

    <!-- Header Banner -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 p-6 sm:p-8 text-slate-800 dark:text-slate-100 shadow-xs border border-slate-200/90 dark:border-slate-800 relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-16 h-16 rounded-2xl overflow-hidden border-2 border-purple-300 dark:border-purple-700 shadow-md shrink-0 bg-purple-50 dark:bg-purple-950/70">
                    <img src="{{ asset('assets/characters/caroline.jpg') }}?v=2" alt="Caroline" class="w-full h-full object-cover">
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/80 px-2.5 py-0.5 rounded-md border border-purple-200/80 dark:border-purple-800/70">
                            Forensisch Dossierbeheer
                        </span>
                        <span class="text-[10px] font-bold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/80 px-2 py-0.5 rounded-md border border-amber-200/80 dark:border-amber-800/70">
                            500 Unieke Cold Cases &bull; 24u Roulatie
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">
                        True Crime Dossiers met Caroline
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 max-w-2xl leading-relaxed">
                        Welkom in het archief van de forensische recherche! Samen met <strong>Caroline</strong> ontrafel jij de meest mysterieuze moorden, vergiftigingen en complotten in en rond de inrichting. Analyseer het sporenonderzoek, ondervraag de verdachten en ontmasker de ware dader!
                    </p>
                </div>
            </div>

            <!-- Detective Stats Counters -->
            <div class="flex flex-wrap items-center gap-2 text-xs font-bold shrink-0">
                <span class="bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 px-3.5 py-2 rounded-xl border border-purple-200/80 dark:border-purple-800/70 shadow-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-stamp text-purple-600 dark:text-purple-400"></i>
                    <span>{{ $state->cases_solved_count }} / {{ $totalCasesCount }} Opgelost</span>
                </span>
                <span class="bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 px-3.5 py-2 rounded-xl border border-emerald-200/80 dark:border-emerald-800/70 shadow-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-sack-dollar text-emerald-600 dark:text-emerald-400"></i>
                    <span>€ {{ number_format($state->total_rewards_cash, 0, ',', '.') }} Beloning</span>
                </span>
            </div>
        </div>
    </div>

    <!-- MAIN INVESTIGATION LAYOUT -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- LEFT COLUMN: ACTIVE CASE DOSSIER (8 COLS) -->
        <div class="lg:col-span-8 space-y-6">

            <!-- COOLDOWN SCREEN (IF 24H COOLDOWN ACTIVE) -->
            <div x-show="cooldownSeconds > 0" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-8 text-center space-y-6">
                <div class="w-24 h-24 mx-auto rounded-2xl overflow-hidden border-2 border-purple-400 shadow-md">
                    <img src="{{ asset('assets/characters/caroline_transparent.png') }}?v=2" alt="Caroline" class="w-full h-full object-contain bg-slate-50 dark:bg-slate-800">
                </div>
                <div class="space-y-2 max-w-md mx-auto">
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">Caroline archiveert het dossier!</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Jouw onderzoek voor dit etmaal is afgerond. Over <strong class="text-purple-600 dark:text-purple-400" x-text="formatTime(cooldownSeconds)"></strong> ontgrendelt de recherche het volgende cold case dossier!
                    </p>
                </div>

                <!-- AD SKIP BUTTON (5 ADS REQUIRED TO BYPASS 24H) -->
                <div class="p-5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 max-w-md mx-auto space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-xs font-black text-amber-900 dark:text-amber-200">
                            <i class="fa-solid fa-bolt text-amber-500 animate-pulse"></i>
                            <span>24 uur wachttijd omzeilen?</span>
                        </div>
                        <span class="text-[11px] font-black px-2.5 py-0.5 rounded-full bg-purple-50 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-700">
                            {{ $adsWatched }}/5 Ads Bekeken
                        </span>
                    </div>

                    <!-- Progress Bar & Segments -->
                    @if(\App\Http\Controllers\SponsorAdController::areAdsEnabled())
                    <button type="button"
                            onclick="window.tbsOpenSponsorAd('cooldown_true_crime')"
                            @disabled($character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum())
                            class="w-full py-3.5 px-6 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white font-black text-xs transition-all duration-150 cursor-pointer shadow-xs flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-play text-[12px]"></i>
                        <span>Bekijk Sponsor-Ad ({{ $adsWatched }}/5 Bekeken)</span>
                    </button>
                    @endif
                    <form action="{{ route('true_crime.skip_cooldown_stars') }}" method="POST" class="w-full">
                        @csrf
                        <button type="submit" 
                                class="btn-star-gold w-full py-3.5 px-6 rounded-xl active:scale-95 text-slate-900 font-black text-xs transition-all duration-150 cursor-pointer shadow-xs flex items-center justify-center gap-2">
                            <i class="fa-solid fa-star text-[12px]"></i>
                            <span>Wachttijd Direct Overslaan (15 Sterren ⭐)</span>
                        </button>
                    </form>
                    
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed text-center">
                        Geen zin om 24 uur te wachten? Sla de wachttijd direct over voor <strong>15 Sterren ⭐</strong> en start onmiddellijk het volgende dossier!
                    </p>
                </div>
            </div>

            <!-- ACTIVE CASE FOLDER (WHEN AVAILABLE) -->
            @if($activeCase)
            <div x-show="cooldownSeconds <= 0" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-6 sm:p-8 space-y-6 relative overflow-hidden">
                
                <!-- Vintage Folder Tab Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/80 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg border border-purple-200/80 dark:border-purple-800/70">
                            <i class="fa-solid fa-fingerprint"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70">
                                    {{ $activeCase->category }}
                                </span>
                                <span class="text-[9px] font-bold text-slate-400">
                                    Moeilijkheid: {{ $activeCase->difficulty }}
                                </span>
                            </div>
                            <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white mt-0.5">
                                {{ $activeCase->title }}
                            </h2>
                        </div>
                    </div>

                    <!-- Potential Reward Pill -->
                    <div class="text-right shrink-0">
                        <span class="text-[9px] font-black uppercase text-slate-400 block">Oplosbeloning</span>
                        <span class="text-xs font-black text-emerald-600 dark:text-emerald-400">
                            € {{ number_format($activeCase->reward_cash, 0, ',', '.') }} &bull; +{{ $activeCase->reward_xp }} XP
                        </span>
                    </div>
                </div>

                <!-- Incident Report Story Card -->
                <div class="p-4 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 space-y-2">
                    <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        <i class="fa-solid fa-file-lines text-purple-600"></i>
                        <span>Plaats Delict Rapportage</span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-serif">
                        {{ $activeCase->incident_report }}
                    </p>
                </div>

                <!-- 4 Forensic Clues (Sporenonderzoek) -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white">
                        <i class="fa-solid fa-microscope text-purple-600"></i>
                        <span>Veiliggestelde Forensische Sporen (4 Bewijsstukken)</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($activeCase->clues as $clue)
                        <div class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 shadow-xs space-y-1.5 hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-150">
                            <div class="flex items-center gap-2 text-xs font-black text-purple-700 dark:text-purple-300">
                                <i class="fa-solid {{ $clue['icon'] ?? 'fa-magnifying-glass' }} text-purple-500"></i>
                                <span>{{ $clue['title'] }}</span>
                            </div>
                            <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed">
                                {{ $clue['detail'] }}
                            </p>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- 4 Suspects Section -->
                <div class="space-y-3 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white">
                            <i class="fa-solid fa-users text-purple-600"></i>
                            <span>De 4 Hoofdverdachten &bull; Wijs de dader aan</span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-bold">Kies zorgvuldig</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($activeCase->suspects as $idx => $suspect)
                        <div class="p-4 rounded-xl border transition-all duration-150 relative flex flex-col justify-between space-y-3 cursor-pointer"
                             :class="selectedSuspect === {{ $idx }} ? 'bg-purple-50/80 dark:bg-purple-950/60 border-purple-600 dark:border-purple-500 ring-2 ring-purple-500/20' : 'bg-slate-50/90 dark:bg-slate-800/60 border-slate-200/80 dark:border-slate-700/70 hover:border-purple-300 dark:hover:border-purple-700/80'"
                             @click="selectedSuspect = {{ $idx }}">
                            
                            <div class="space-y-2">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-xs font-black text-slate-700 dark:text-slate-200 shrink-0">
                                            #{{ $idx + 1 }}
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-black text-slate-900 dark:text-white">{{ $suspect['name'] }}</h4>
                                            <span class="text-[10px] text-purple-600 dark:text-purple-400 font-bold block">{{ $suspect['role'] }}</span>
                                        </div>
                                    </div>
                                    <input type="radio" name="suspect" :checked="selectedSuspect === {{ $idx }}" class="text-purple-600 focus:ring-purple-500 mt-1 pointer-events-none">
                                </div>

                                <div class="text-[11px] space-y-1 text-slate-600 dark:text-slate-300">
                                    <p><strong>Motief:</strong> {{ $suspect['motive'] }}</p>
                                    <p><strong>Alibi:</strong> {{ $suspect['alibi'] }}</p>
                                </div>

                                <div class="p-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/70 text-[10px] italic text-slate-500 dark:text-slate-400">
                                    &ldquo;{{ $suspect['statement'] }}&rdquo;
                                </div>
                            </div>

                            <button type="button" 
                                    @click.stop="submitAccusation({{ $activeCase->id }}, {{ $idx }})"
                                    :disabled="isImprisoned || isComatose"
                                    class="w-full py-2.5 rounded-xl text-xs font-black transition-all duration-150 cursor-pointer shadow-xs flex items-center justify-center gap-1.5 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed"
                                    :class="selectedSuspect === {{ $idx }} ? 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white' : 'bg-white dark:bg-slate-800 hover:bg-purple-50 dark:hover:bg-purple-950/40 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700'">
                                <i class="fa-solid fa-handcuffs text-[11px]"></i>
                                <span>Aanklagen als Dader</span>
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>
            @endif

        </div>

        <!-- RIGHT COLUMN: CAROLINE FORENSIC PROFILE (4 COLS) -->
        <div class="lg:col-span-4 space-y-6">

            <!-- CAROLINE DIALOGUE PROFILE -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-6 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-16 h-16 rounded-2xl overflow-hidden border-2 border-purple-300 dark:border-purple-700 shadow-sm shrink-0 bg-purple-50 dark:bg-purple-950">
                        <img src="{{ asset('assets/characters/caroline_transparent.png') }}?v=2" alt="Caroline" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                            <span class="text-[10px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-300">Dossierbeheerder</span>
                        </div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white">Caroline</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 font-bold">Forensisch Analist & Speurder</p>
                    </div>
                </div>

                <!-- Speech Bubble -->
                <div class="p-3.5 rounded-xl bg-purple-50/80 dark:bg-slate-800/80 border border-purple-200/80 dark:border-purple-800/60 relative">
                    <div class="absolute -top-2 left-6 w-3 h-3 bg-purple-50 dark:bg-slate-800 border-t border-l border-purple-200/80 dark:border-purple-800/60 rotate-45"></div>
                    <p class="text-xs text-purple-950 dark:text-purple-200 font-medium leading-relaxed italic">
                        &ldquo;Iedere dader laat sporen na! Vergelijk de schoenmaten, cameratijden en laboratoriumuitslagen met de verklaringen van de verdachten. Wie heeft er een lek alibi?&rdquo;
                    </p>
                </div>

                <!-- Archive Stats -->
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="p-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-center">
                        <span class="text-[9px] font-bold uppercase text-slate-400 block">Zaken Opgelost</span>
                        <span class="text-lg font-black text-emerald-600 dark:text-emerald-400">{{ $state->cases_solved_count }}</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-center">
                        <span class="text-[9px] font-bold uppercase text-slate-400 block">Zaken Gefaald</span>
                        <span class="text-lg font-black text-rose-500 dark:text-rose-400">{{ $state->cases_failed_count }}</span>
                    </div>
                </div>
            </div>

            <!-- FORENSIC INVESTIGATION TIPS -->
            <div class="rounded-2xl p-5 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-xs space-y-3 text-slate-800 dark:text-slate-200">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-lightbulb text-purple-600 dark:text-purple-400"></i>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white">Caroline's Speurtips</h4>
                </div>
                <ul class="text-xs text-slate-600 dark:text-slate-300 space-y-2 leading-relaxed">
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-check text-purple-600 text-[10px] mt-1 shrink-0"></i>
                        <span>Kijk goed naar de tijdstippen in de CCTV logs en vergelijk ze met het alibi.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-check text-purple-600 text-[10px] mt-1 shrink-0"></i>
                        <span>Schoenmaten en kledingvezels liegen nooit.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-check text-purple-600 text-[10px] mt-1 shrink-0"></i>
                        <span>Elke 24 uur ontgrendelt een nieuw dossier van de 500 unieke zaken!</span>
                    </li>
                </ul>
            </div>

        </div>

    </div>

    <!-- VERDICT / SOLUTION MODAL -->
    <template x-if="verdictModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 pb-24 lg:pb-4 bg-slate-950/80 backdrop-blur-xs select-none">
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl max-w-lg w-full max-h-[85dvh] overflow-y-auto p-6 sm:p-8 shadow-2xl space-y-5 animate-in fade-in zoom-in-95 duration-200">
                <div class="w-16 h-16 mx-auto rounded-2xl flex items-center justify-center text-3xl shadow-xs"
                     :class="verdictModal.is_correct ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/70' : 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/70'">
                    <i class="fa-solid" :class="verdictModal.is_correct ? 'fa-circle-check' : 'fa-circle-xmark'"></i>
                </div>
                
                <div class="text-center space-y-1">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white" x-text="verdictModal.title"></h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed" x-text="verdictModal.message"></p>
                </div>

                <!-- Explanation Box -->
                <div class="p-4 rounded-xl bg-purple-50/80 dark:bg-slate-800/60 border border-purple-200/80 dark:border-purple-800/70 text-xs text-slate-700 dark:text-slate-200 leading-relaxed space-y-1 text-left">
                    <span class="font-black text-purple-900 dark:text-purple-300 block uppercase text-[10px] tracking-wider">Forensische Conclusie:</span>
                    <p x-text="verdictModal.explanation"></p>
                </div>

                <div class="flex items-center justify-between text-xs font-black p-3 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70">
                    <span class="text-slate-500 dark:text-slate-400">Ontvangen Beloning:</span>
                    <span class="text-emerald-600 dark:text-emerald-400" x-text="'+€ ' + Number(verdictModal.reward_cash).toLocaleString('nl-NL') + ' &bull; +' + verdictModal.reward_xp + ' XP &bull; +' + verdictModal.reward_respect + ' Respect'"></span>
                </div>

                <!-- AD SKIP BUTTON (TO UNLOCK NEXT CASE IMMEDIATELY) -->
                @if(\App\Http\Controllers\SponsorAdController::areAdsEnabled())
                <button type="button" onclick="window.tbsOpenSponsorAd('cooldown_true_crime')" @disabled($character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum()) class="w-full py-3 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs transition-all duration-150 active:scale-95 cursor-pointer shadow-xs flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-folder-open"></i>
                    <span>Wachttijd Verkorten via Sponsor-Ad ({{ $adsWatched }}/5 Bekeken)</span>
                </button>
                @endif

                <button type="button" @click="closeModalAndReload()" class="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-xs transition-all duration-150 active:scale-95 cursor-pointer">
                    Sluiten & 24u Afwachten
                </button>
            </div>
        </div>
    </template>

</div>

<script>
function tbsTrueCrime(initialCooldown, isImprisoned = false, isComatose = false) {
    return {
        cooldownSeconds: initialCooldown,
        cdInterval: null,
        selectedSuspect: 0,
        verdictModal: null,
        isImprisoned: isImprisoned,
        isComatose: isComatose,

        init() {
            if (this.cooldownSeconds > 0) {
                this.startCooldownTimer();
            }
        },

        startCooldownTimer() {
            if (this.cdInterval) clearInterval(this.cdInterval);
            this.cdInterval = setInterval(() => {
                this.cooldownSeconds--;
                if (this.cooldownSeconds <= 0) {
                    clearInterval(this.cdInterval);
                    this.cooldownSeconds = 0;
                    window.location.reload();
                }
            }, 1000);
        },

        formatTime(seconds) {
            const h = Math.floor(seconds / 3600);
            const m = Math.floor((seconds % 3600) / 60);
            const s = seconds % 60;
            if (h > 0) return `${h} uur en ${m} minuten`;
            return `${m}m ${s}s`;
        },

        submitAccusation(caseId, suspectIdx) {
            if (this.isImprisoned) {
                alert('Vanuit de isoleercel kun je geen politiedossiers onderzoeken!');
                return;
            }
            if (this.isComatose) {
                alert('Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een Revive Serum toe.');
                return;
            }

            fetch('{{ route('true_crime.solve') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    case_id: caseId,
                    suspect_index: suspectIdx
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.verdictModal = data;
                    this.cooldownSeconds = data.cooldown;
                    this.startCooldownTimer();
                } else {
                    alert(data.message || 'Er is een fout opgetreden.');
                }
            })
            .catch(() => {
                alert('Fout bij communicatie met de rechtbank server.');
            });
        },

        closeModalAndReload() {
            this.verdictModal = null;
            window.location.reload();
        }
    };
}
</script>
@endsection
