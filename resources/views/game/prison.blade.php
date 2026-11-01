@extends('layouts.app')

@section('title', 'Celblok & Isolatievleugel - TBS Kliniek')

@section('content')
@php
    $breakoutMethods = $breakoutMethods ?? \App\Http\Controllers\PrisonController::BREAKOUT_METHODS;
    $reduceActions = $reduceActions ?? \App\Http\Controllers\PrisonController::REDUCE_ACTIONS;
    $prisonActionCooldownRemaining = $prisonActionCooldownRemaining ?? 0;
    $adsRequired = $character->getRequiredPrisonAds();
    $adsProgress = min($adsRequired, (int) ($character->ad_prison_progress ?? 0));
    $adPct = $adsRequired > 0 ? min(100, round(($adsProgress / $adsRequired) * 100)) : 100;
@endphp

<div class="space-y-6" x-data="prisonPageController({
    isImprisoned: {{ $isImprisoned ? 'true' : 'false' }},
    remainingSecs: {{ $isImprisoned ? max(0, (int) $remainingSeconds) : 0 }},
    adsProgress: {{ $adsProgress }},
    adsRequired: {{ $adsRequired }},
    escapeAttempted: {{ ($character->prison_escape_attempted ?? false) ? 'true' : 'false' }},
    reduceCooldownSecs: {{ max(0, (int) $prisonActionCooldownRemaining) }}
})" x-init="startCountdown()">

    <!-- BOVENSTE TELEMETRIE BANNER -->
    <div class="relative rounded-2xl overflow-hidden border border-red-500/30 bg-[#0d111a] shadow-2xl">
        <div class="relative min-h-[210px] md:min-h-[240px] flex items-end p-5 md:p-7">
            <img src="{{ asset('images/headers/gevangenis_header.png') }}"
                 alt="Celblok & Isolatievleugel"
                 class="absolute inset-0 w-full h-full object-cover object-center opacity-35">
            <div class="absolute inset-0 bg-gradient-to-r from-[#080b11] via-[#080b11]/85 to-transparent"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#0d111a] via-transparent to-black/40"></div>

            <div class="relative z-10 w-full flex flex-col lg:flex-row lg:items-end justify-between gap-5">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-500/15 border border-red-500/40 text-red-400 text-[11px] font-black uppercase tracking-widest mb-2.5">
                        <i class="fa-solid fa-dungeon animate-pulse"></i> Gesloten Forensisch Celblok • Sector Z-04
                    </div>
                    <h1 class="text-2xl md:text-4xl font-black text-white tracking-tight uppercase drop-shadow">
                        Isolatievleugel & <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-400 via-orange-300 to-amber-400">Tactische Celkraak</span>
                    </h1>
                    <p class="text-xs md:text-sm text-gray-300 mt-1.5 leading-relaxed">
                        Wie door het interventieteam wordt overmeesterd, belandt achter de gepantserde deuren van Sector Z-04. Verkort je eigen straftijd via de stoomwasserij of omkoping, of bevrijd ingesloten medepatiënten via een <strong class="text-cyan-300">Stille Sluipuitbraak</strong> of <strong class="text-amber-300">Breekijzer & Forceerwerk</strong>.
                    </p>
                </div>

                <!-- 5 KPI Telemetrie Tegels -->
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5 shrink-0">
                    <div class="bg-black/65 backdrop-blur-md border border-white/10 rounded-xl px-3.5 py-2.5 text-center">
                        <div class="text-[10px] uppercase tracking-wider text-gray-400 font-bold">Ingesloten</div>
                        <div class="text-base md:text-lg font-black text-red-400 mt-0.5">{{ $jailList->count() }}</div>
                    </div>
                    <div class="bg-black/65 backdrop-blur-md border border-white/10 rounded-xl px-3.5 py-2.5 text-center">
                        <div class="text-[10px] uppercase tracking-wider text-gray-400 font-bold">Bevrijdingen</div>
                        <div class="text-base md:text-lg font-black text-emerald-400 mt-0.5">{{ $stats['escapes_today'] }}</div>
                    </div>
                    <div class="bg-black/65 backdrop-blur-md border border-white/10 rounded-xl px-3.5 py-2.5 text-center">
                        <div class="text-[10px] uppercase tracking-wider text-gray-400 font-bold">Slagingsratio</div>
                        <div class="text-base md:text-lg font-black text-cyan-400 mt-0.5">{{ $stats['breakout_xp'] }}</div>
                    </div>
                    <div class="bg-black/65 backdrop-blur-md border border-white/10 rounded-xl px-3.5 py-2.5 text-center">
                        <div class="text-[10px] uppercase tracking-wider text-gray-400 font-bold">Kraak-Index</div>
                        <div class="text-base md:text-lg font-black text-amber-400 mt-0.5">{{ $stats['slay'] }}</div>
                    </div>
                    <div class="col-span-2 sm:col-span-1 bg-black/65 backdrop-blur-md border {{ $isImprisoned ? 'border-red-500/40' : 'border-emerald-500/40' }} rounded-xl px-3.5 py-2.5 text-center">
                        <div class="text-[10px] uppercase tracking-wider text-gray-400 font-bold">Jouw Status</div>
                        <div class="text-xs md:text-sm font-black {{ $isImprisoned ? 'text-red-400' : 'text-emerald-400' }} mt-1 uppercase">
                            {{ $isImprisoned ? 'Ingesloten' : 'Vrije Voeten' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- OMGEKEERDE 12-KOLOMS LAYOUT: LINKS (4 KOLOMMEN) TELEMETRIE & EREGALERIJ, RECHTS (8 KOLOMMEN) CELBLOK & ACTIES -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- LINKERKOLOM (4 van 12): UITBRAAK-TELEMETRIE, EREGALERIJ & TACTISCH PROTOCOL -->
        <div class="lg:col-span-4 space-y-6 order-2 lg:order-1">

            <!--Persoonlijk Uitbraak-Dossier -->
            <div class="rounded-2xl border border-cyan-500/25 bg-[#111726] overflow-hidden shadow-xl">
                <div class="px-5 py-4 border-b border-white/10 bg-[#151d30] flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-cyan-500/15 border border-cyan-500/30 flex items-center justify-center text-cyan-400">
                            <i class="fa-solid fa-chart-pie text-sm"></i>
                        </div>
                        <div>
                            <h2 class="text-xs font-black uppercase tracking-wider text-white">Kraak-Telemetrie & Dossier</h2>
                            <p class="text-[11px] text-gray-400">Persoonlijke bevrijdingsstatistieken</p>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded bg-cyan-500/10 border border-cyan-500/30 text-[10px] font-black text-cyan-300 uppercase">
                        Actief
                    </span>
                </div>

                <div class="p-5 space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-[#0b0f19] border border-white/10 p-3">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Geslaagde Kraken</div>
                            <div class="text-xl font-black text-emerald-400 mt-1">{{ $stats['escapes_today'] }}</div>
                        </div>
                        <div class="rounded-xl bg-[#0b0f19] border border-white/10 p-3">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Totale Pogingen</div>
                            <div class="text-xl font-black text-white mt-1">{{ $stats['attempts_today'] }}</div>
                        </div>
                        <div class="rounded-xl bg-[#0b0f19] border border-white/10 p-3">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Nauwkeurigheid</div>
                            <div class="text-xl font-black text-cyan-400 mt-1">{{ $stats['breakout_xp'] }}</div>
                        </div>
                        <div class="rounded-xl bg-[#0b0f19] border border-white/10 p-3">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Kraak-Reputatie</div>
                            <div class="text-xl font-black text-amber-400 mt-1">{{ $stats['slay'] }}</div>
                        </div>
                    </div>

                    <!-- Voortgangsbalk Slagingspercentage -->
                    @php
                        $numericRate = (float) str_replace('%', '', (string) $stats['breakout_xp']);
                    @endphp
                    <div class="rounded-xl bg-[#0b0f19] border border-white/10 p-3.5">
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <span class="text-gray-400 font-bold">Effectiviteit op Slotmechanismen</span>
                            <span class="text-cyan-400 font-black">{{ $stats['breakout_xp'] }}</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-gray-800 overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-cyan-500 to-emerald-400 rounded-full transition-all duration-500"
                                 style="width: {{ min(100, max(4, $numericRate)) }}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-gray-500 mt-2">
                            <span>Recorddatum: {{ $stats['best_day_date'] }}</span>
                            <span class="text-emerald-400 font-bold">{{ $stats['best_day_count'] }} bevrijdingen</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Eregalerij van Meester-Krakers -->
            <div class="rounded-2xl border border-amber-500/25 bg-[#111726] overflow-hidden shadow-xl">
                <div class="px-5 py-4 border-b border-white/10 bg-[#151d30] flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-400">
                            <i class="fa-solid fa-key text-sm"></i>
                        </div>
                        <div>
                            <h2 class="text-xs font-black uppercase tracking-wider text-white">Meester-Celkrakers Top 5</h2>
                            <p class="text-[11px] text-gray-400">Specialisten in celblok-infiltratie</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 space-y-2.5">
                    @forelse($topBreakers as $index => $tb)
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-[#0b0f19] border border-white/5 hover:border-amber-500/30 transition">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-black {{ $index === 0 ? 'bg-amber-500 text-black' : ($index === 1 ? 'bg-gray-300 text-black' : ($index === 2 ? 'bg-amber-700 text-white' : 'bg-white/10 text-gray-400')) }}">
                                    {{ $index + 1 }}
                                </div>
                                <a href="{{ route('profile.show', $tb->name) }}" class="flex items-center gap-2.5 truncate group">
                                    <div class="{{ $tb->getAvatarBorderClasses() }} rounded-full shrink-0">
                                        <img src="{{ $tb->avatar_url }}" class="w-7 h-7 rounded-full object-cover border border-white/15">
                                    </div>
                                    <span class="font-bold text-xs group-hover:underline truncate {{ $tb->isVip() && !$tb->getCustomNameColor() ? 'text-amber-300' : 'text-white' }}" @if($tb->getCustomNameColor()) style="color: {{ $tb->getCustomNameColor() }}" @endif>{{ $tb->name }}</span>
                                    @if($tb->isVip())
                                        <i class="fa-solid fa-gem text-cyan-400 text-[10px]"></i>
                                    @endif
                                </a>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-black shrink-0">
                                {{ $tb->prison_escapes }} kraken
                            </span>
                        </div>
                    @empty
                        <div class="py-6 text-center text-xs text-gray-500">
                            Nog geen geregistreerde celkraken in dit tijdvak.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Tactisch Protocol & Risicomatrix -->
            <div class="rounded-2xl border border-white/10 bg-[#111726] p-5 space-y-3.5 shadow-xl">
                <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-gray-200">
                    <i class="fa-solid fa-compass-drafting text-red-400"></i> Tactische Uitbraak-Methodes
                </div>
                <div class="space-y-2.5 text-xs">
                    @foreach($breakoutMethods as $mKey => $mData)
                        <div class="p-3 rounded-xl bg-[#0b0f19] border {{ $mKey === 'stil' ? 'border-cyan-500/25' : 'border-amber-500/25' }}">
                            <div class="flex items-center justify-between font-black {{ $mKey === 'stil' ? 'text-cyan-400' : 'text-amber-400' }}">
                                <span><i class="fa-solid {{ $mData['icon'] }} mr-1"></i> {{ $mData['name'] }}</span>
                                <span class="text-[10px] px-2 py-0.5 rounded bg-white/5 text-gray-300">
                                    {{ $mData['chance_mod'] > 0 ? '+'.$mData['chance_mod'].'%' : $mData['chance_mod'].'%' }} kans
                                </span>
                            </div>
                            <p class="text-[11px] text-gray-400 mt-1 leading-relaxed">{{ $mData['description'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- RECHTERKOLOM (8 van 12): PERSOONLIJKE CELSTATUS, STRAFVERKORTING & GEDETINEERDEN-DOSSIERS -->
        <div class="lg:col-span-8 space-y-6 order-1 lg:order-2">

            <!-- STATUS DASHBOARD: VRIJ OF INGESLOTEN -->
            <div x-show="!isImprisoned" x-cloak class="rounded-2xl border border-emerald-500/30 bg-gradient-to-r from-emerald-950/40 via-[#111726] to-[#111726] p-5 shadow-xl">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3.5">
                        <div class="w-11 h-11 rounded-xl bg-emerald-500/15 border border-emerald-500/40 flex items-center justify-center text-emerald-400 shrink-0">
                            <i class="fa-solid fa-shield-check text-xl"></i>
                        </div>
                        <div>
                            <div class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-emerald-400">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span> Vrije Corridorstatus
                            </div>
                            <h2 class="text-base md:text-lg font-black text-white mt-0.5">Je beweegt vrij over de kliniekafdelingen</h2>
                            <p class="text-xs text-gray-300 mt-1">
                                Kies hieronder een gedetineerde en bepaal per celdossier of je kiest voor een <strong class="text-cyan-300">Stille Sluipuitbraak</strong> (hogere trefkans) of <strong class="text-amber-300">Breekijzer & Forceerwerk</strong> (dubbele Respect-beloning & kans op +1 Kracht). Elke kraakpoging kost <strong class="text-amber-400">15 Energie</strong>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ACTIEVE ISOLATIESTRAF + NIEUWE STRAFVERKORTING MODULE (STOOMWASSERIJ / CIPIER OMKOPEN) -->
            <div x-show="isImprisoned" x-cloak class="rounded-2xl border border-red-500/50 bg-gradient-to-b from-red-950/35 via-[#111726] to-[#111726] overflow-hidden shadow-2xl space-y-5 p-5 md:p-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-red-500/20 pb-4">
                    <div class="flex items-start gap-3.5">
                        <div class="w-12 h-12 rounded-xl bg-red-500/20 border border-red-500/40 flex items-center justify-center text-red-400 shrink-0">
                            <i class="fa-solid fa-lock text-xl animate-pulse"></i>
                        </div>
                        <div>
                            <span class="px-2.5 py-0.5 rounded bg-red-500/20 border border-red-500/40 text-red-300 text-[10px] font-black uppercase tracking-widest">
                                Celblok Vergrendeld • Isolatieregime Actief
                            </span>
                            <h2 class="text-lg md:text-xl font-black text-white mt-1">Je zit opgesloten in Sector Z-04</h2>
                            <p class="text-xs text-gray-300 mt-0.5">
                                Geregistreerd vergrijp: <span class="text-red-300 font-bold">"{{ $character->prison_reason ?: 'Overtreding van het kliniekreglement' }}"</span>
                            </p>
                        </div>
                    </div>

                    <!-- Live Digitale Celklok -->
                    <div class="bg-[#090d15] border border-red-500/40 rounded-xl px-4 py-3 text-center shrink-0">
                        <div class="text-[10px] uppercase tracking-widest text-gray-400 font-bold">Resterende Celstraf</div>
                        <div class="text-2xl font-black font-mono text-amber-400 mt-0.5" x-text="formatTime(remainingSecs)">
                            {{ gmdate('i:s', max(0, (int) $remainingSeconds)) }}
                        </div>
                    </div>
                </div>

                <!-- NIEUW MECHANISME: STRAFVERKORTING & CORRIDORSABOTAGE (STOOMWASSERIJ OF CIPIER OMKOPEN) -->
                <div class="rounded-xl bg-[#0b0f19] border border-amber-500/30 p-4 space-y-3.5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <div class="text-xs font-black uppercase tracking-wider text-amber-400 flex items-center gap-2">
                                <i class="fa-solid fa-stopwatch-20"></i> Strafverkorting & Corridorsabotage
                            </div>
                            <p class="text-[11px] text-gray-400 mt-0.5">
                                Verkort je lopende isolatiestraf actief door arbeid in de wasserij te verrichten of de dienstdoende nachtcipier om te kopen.
                            </p>
                        </div>
                        <template x-if="reduceCooldownSecs > 0">
                            <span class="px-3 py-1 rounded-lg bg-amber-500/15 border border-amber-500/40 text-amber-300 text-xs font-black shrink-0">
                                <i class="fa-solid fa-hourglass-half mr-1"></i> Wachtpost Cooldown: <span x-text="reduceCooldownSecs + 's'"></span>
                            </span>
                        </template>
                        <template x-if="reduceCooldownSecs <= 0">
                            <span class="px-3 py-1 rounded-lg bg-emerald-500/15 border border-emerald-500/40 text-emerald-300 text-xs font-black shrink-0">
                                <i class="fa-solid fa-circle-check mr-1"></i> Direct Beschikbaar
                            </span>
                        </template>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                        @foreach($reduceActions as $actKey => $actMeta)
                            <form action="{{ route('prison.reduce') }}" method="POST"
                                  class="rounded-xl border {{ $actKey === 'wasserij' ? 'border-cyan-500/30 bg-[#101726]' : 'border-amber-500/30 bg-[#141522]' }} p-3.5 flex flex-col justify-between gap-3">
                                @csrf
                                <input type="hidden" name="action" value="{{ $actKey }}">
                                <div>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-xs font-black uppercase tracking-wider {{ $actKey === 'wasserij' ? 'text-cyan-300' : 'text-amber-300' }}">
                                            <i class="fa-solid {{ $actMeta['icon'] }} mr-1"></i> {{ $actMeta['name'] }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded bg-black/50 border border-white/10 text-[10px] font-black text-white">
                                            Kost: {{ $actMeta['cost_label'] }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] font-bold text-emerald-400 mt-1">{{ $actMeta['subtitle'] }}</div>
                                    <p class="text-[11px] text-gray-400 mt-1 leading-relaxed">{{ $actMeta['description'] }}</p>
                                </div>

                                <button type="submit"
                                        :disabled="reduceCooldownSecs > 0"
                                        :class="reduceCooldownSecs > 0 ? 'opacity-50 cursor-not-allowed bg-gray-800 text-gray-400' : '{{ $actKey === 'wasserij' ? 'bg-cyan-600 hover:bg-cyan-500 text-white' : 'bg-amber-500 hover:bg-amber-400 text-black' }}'"
                                        class="w-full py-2 px-3 rounded-lg font-black text-xs uppercase tracking-wider transition flex items-center justify-center gap-2">
                                    <i class="fa-solid {{ $actMeta['icon'] }}"></i>
                                    <span x-text="reduceCooldownSecs > 0 ? 'Cooldown (' + reduceCooldownSecs + 's)' : 'Uitvoeren: {{ $actMeta['name'] }}'">
                                        Uitvoeren: {{ $actMeta['name'] }}
                                    </span>
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>

                <!-- DIRECTE ONTSNAPPING, VRIJKOOP MET STERREN & SPONSORVIDEO -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
                    <!-- Optie 1: Luchtschacht Ontsnapping -->
                    <div class="rounded-xl bg-[#0b0f19] border border-white/10 p-3.5 flex flex-col justify-between gap-3">
                        <div>
                            <div class="text-xs font-black text-white uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-solid fa-person-running text-amber-400"></i> Luchtschacht-Uitbraak
                            </div>
                            <p class="text-[11px] text-gray-400 mt-1">
                                Waag één ontsnappingspoging per celstraf (20 Energie). Bij succes direct vrij + 15 Respect; bij betrapping +90 sec straf.
                            </p>
                        </div>
                        <form action="{{ route('prison.escape') }}" method="POST" @submit.prevent="attemptEscapeAjax($event)">
                            @csrf
                            <button type="submit"
                                    :disabled="escapeAttempted"
                                    :class="escapeAttempted ? 'bg-gray-800 text-gray-500 cursor-not-allowed border border-gray-700' : 'bg-amber-500 hover:bg-amber-400 text-black shadow-lg'"
                                    class="w-full py-2.5 px-3 rounded-lg font-black text-xs uppercase tracking-wider transition flex items-center justify-center gap-1.5">
                                <i class="fa-solid" :class="escapeAttempted ? 'fa-ban' : 'fa-bolt'"></i>
                                <span x-text="escapeAttempted ? 'Poging Verbruikt' : 'Ontsnappen (20 Energie)'"></span>
                            </button>
                        </form>
                    </div>

                    <!-- Optie 2: Vrijkoopakte (25 Sterren) -->
                    <div class="rounded-xl bg-[#0b0f19] border border-yellow-500/30 p-3.5 flex flex-col justify-between gap-3">
                        <div>
                            <div class="text-xs font-black text-yellow-400 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-solid fa-star"></i> Directe Vrijkoopakte
                            </div>
                            <p class="text-[11px] text-gray-400 mt-1">
                                Koop de elektronische sluis direct vrij zonder risico. Vereist <strong class="text-yellow-300">25 Sterren ⭐</strong> (bezit: {{ $character->stars ?? 0 }} ⭐).
                            </p>
                        </div>
                        <form action="{{ route('prison.buyout') }}" method="POST">
                            @csrf
                            <button type="submit"
                                    @if(($character->stars ?? 0) < 25) disabled @endif
                                    class="w-full py-2.5 px-3 rounded-lg font-black text-xs uppercase tracking-wider transition flex items-center justify-center gap-1.5 {{ ($character->stars ?? 0) >= 25 ? 'bg-yellow-500 hover:bg-yellow-400 text-black shadow-lg' : 'bg-gray-800 text-gray-500 cursor-not-allowed border border-gray-700' }}">
                                <i class="fa-solid fa-unlock-keyhole"></i> Vrijkoop (25 ⭐)
                            </button>
                        </form>
                    </div>

                    <!-- Optie 3: Sponsoruitzending -->
                    <div class="rounded-xl bg-[#0b0f19] border border-emerald-500/30 p-3.5 flex flex-col justify-between gap-3">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black text-emerald-400 uppercase tracking-wider">
                                    <i class="fa-solid fa-clapperboard mr-1"></i> Sponsor-Amnestie
                                </span>
                                <span class="text-[10px] font-black text-gray-300" x-text="adsProgress + ' / ' + adsRequired + ' video\'s'">
                                    {{ $adsProgress }} / {{ $adsRequired }} video's
                                </span>
                            </div>
                            <div class="w-full h-1.5 rounded-full bg-gray-800 overflow-hidden mt-2">
                                <div class="h-full bg-emerald-500 transition-all duration-300"
                                     :style="'width: ' + Math.min(100, Math.round((adsProgress / Math.max(1, adsRequired)) * 100)) + '%'"></div>
                            </div>
                            <p class="text-[11px] text-gray-400 mt-1.5">
                                Bekijk een korte sponsoruitzending om de celdeur kosteloos te ontgrendelen.
                            </p>
                        </div>
                        <button type="button"
                                @click="watchAdForFreedom()"
                                :disabled="watchingAd"
                                class="w-full py-2.5 px-3 rounded-lg bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white font-black text-xs uppercase tracking-wider transition flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-play"></i>
                            <span x-text="watchingAd ? 'Uitzending Laden...' : 'Video Bekijken (Gratis Vrij)'"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- GEDETINEERDEN-DOSSIERS (INTERACTIEVE CELBLOK KAARTEN I.P.V. TABEL) -->
            <div class="rounded-2xl border border-white/10 bg-[#111726] overflow-hidden shadow-xl">
                <div class="px-5 py-4 border-b border-white/10 bg-[#151d30] flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-red-500/15 border border-red-500/30 flex items-center justify-center text-red-400">
                            <i class="fa-solid fa-border-all text-sm"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-black uppercase tracking-wider text-white">Actieve Celdossiers in Sector Z-04</h2>
                            <p class="text-[11px] text-gray-400">Kies per gevangene een tactische kraakmethode (15 Energie per poging)</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-red-500/10 border border-red-500/30 text-red-300 text-xs font-black">
                        {{ $jailList->count() }} {{ $jailList->count() === 1 ? 'Gedetineerde' : 'Gedetineerden' }}
                    </span>
                </div>

                <div class="p-5">
                    @if($jailList->isEmpty())
                        <div class="rounded-2xl border border-dashed border-white/10 bg-[#0b0f19]/70 p-10 text-center space-y-2">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 flex items-center justify-center mx-auto">
                                <i class="fa-solid fa-lock-open text-lg"></i>
                            </div>
                            <h3 class="text-sm font-black uppercase tracking-wider text-white">Alle Isoleercellen Staan Leeg</h3>
                            <p class="text-xs text-gray-400 max-w-md mx-auto">
                                Er zitten op dit moment geen andere patiënten vast in Sector Z-04. Zodra iemand wordt opgepakt door de beveiliging, verschijnt het celdossier hier direct.
                            </p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($jailList as $inmate)
                                @php
                                    $rpModel = $inmate['model'] ?? null;
                                    $chanceStil = $inmate['chance_stil'] ?? $inmate['chance'];
                                    $chanceGeweld = $inmate['chance_geweld'] ?? max(5, $inmate['chance'] - 5);
                                @endphp
                                <div x-data="{ selectedMethod: 'stil', chanceStil: {{ (int) $chanceStil }}, chanceGeweld: {{ (int) $chanceGeweld }} }"
                                     class="rounded-xl border border-white/10 bg-[#0b0f19] p-4 flex flex-col justify-between gap-4 hover:border-red-500/30 transition">
                                    <!-- Bovenkant: Avatar, Naam & Resterende Straftijd -->
                                    <div class="space-y-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex items-center gap-3 min-w-0">
                                                @if($rpModel)
                                                    <a href="{{ route('profile.show', $rpModel->name) }}" class="shrink-0">
                                                        <div class="{{ $rpModel->getAvatarBorderClasses() }} rounded-full">
                                                            <img src="{{ $inmate['avatar'] }}" alt="{{ $inmate['name'] }}" class="w-11 h-11 rounded-full object-cover border border-white/15">
                                                        </div>
                                                    </a>
                                                    <div class="min-w-0">
                                                        <a href="{{ route('profile.show', $rpModel->name) }}" class="font-black text-sm hover:underline truncate block {{ $rpModel->isVip() && !$rpModel->getCustomNameColor() ? 'text-amber-300' : 'text-white' }}" @if($rpModel->getCustomNameColor()) style="color: {{ $rpModel->getCustomNameColor() }}" @endif>
                                                            {{ $inmate['name'] }}
                                                        </a>
                                                        <span class="text-[11px] text-gray-400 block truncate">
                                                            Cel #Z-{{ str_pad((string) $inmate['id'], 3, '0', STR_PAD_LEFT) }}
                                                        </span>
                                                    </div>
                                                @else
                                                    <img src="{{ $inmate['avatar'] }}" alt="{{ $inmate['name'] }}" class="w-11 h-11 rounded-full object-cover border border-white/15 shrink-0">
                                                    <div class="min-w-0">
                                                        <span class="font-black text-sm text-white truncate block">{{ $inmate['name'] }}</span>
                                                    </div>
                                                @endif
                                            </div>

                                            <span class="px-2.5 py-1 rounded-lg bg-red-500/15 border border-red-500/30 text-red-300 font-mono text-xs font-black shrink-0"
                                                  data-inmate-timer="{{ $inmate['remaining_seconds'] }}">
                                                <i class="fa-regular fa-clock mr-1"></i>{{ $inmate['time'] }}
                                            </span>
                                        </div>

                                        <!-- Vergrijp / Reden -->
                                        <div class="rounded-lg bg-[#111726] border border-white/5 px-3 py-2 text-xs text-gray-300">
                                            <span class="text-[10px] font-black uppercase tracking-wider text-gray-500 block">Dossiernotitie:</span>
                                            {{ $inmate['reason'] }}
                                        </div>

                                        <!-- Dynamische Slaagkans-indicator -->
                                        <div class="space-y-1.5">
                                            <div class="flex items-center justify-between text-xs">
                                                <span class="text-gray-400 font-bold">Geschatte Slaagkans:</span>
                                                <span class="font-black"
                                                      :class="selectedMethod === 'stil' ? 'text-cyan-400' : 'text-amber-400'"
                                                      x-text="(selectedMethod === 'stil' ? chanceStil : chanceGeweld) + '%'">
                                                    {{ $chanceStil }}%
                                                </span>
                                            </div>
                                            <div class="w-full h-2 rounded-full bg-gray-800 overflow-hidden">
                                                <div class="h-full rounded-full transition-all duration-300"
                                                     :class="selectedMethod === 'stil' ? 'bg-cyan-500' : 'bg-amber-500'"
                                                     :style="'width: ' + (selectedMethod === 'stil' ? chanceStil : chanceGeweld) + '%'"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Formulier met Tactiek-Keuze (Stil vs Geweld) -->
                                    <form action="{{ route('prison.breakout', $inmate['id']) }}" method="POST" class="space-y-2.5 pt-2 border-t border-white/10">
                                        @csrf
                                        <input type="hidden" name="method" value="stil" :value="selectedMethod">

                                        <div class="grid grid-cols-2 gap-2">
                                            <button type="button"
                                                    @click="selectedMethod = 'stil'"
                                                    :class="selectedMethod === 'stil' ? 'border-cyan-400 bg-cyan-500/15 text-cyan-200' : 'border-white/10 bg-[#111726] text-gray-400 hover:text-gray-200'"
                                                    class="p-2 rounded-lg border text-left transition">
                                                <div class="text-[11px] font-black flex items-center justify-between">
                                                    <span><i class="fa-solid fa-user-ninja mr-1"></i> Stil</span>
                                                    <span x-text="chanceStil + '%'">{{ $chanceStil }}%</span>
                                                </div>
                                                <div class="text-[10px] opacity-80 mt-0.5">+2 Resp • +140 XP</div>
                                            </button>

                                            <button type="button"
                                                    @click="selectedMethod = 'geweld'"
                                                    :class="selectedMethod === 'geweld' ? 'border-amber-400 bg-amber-500/15 text-amber-200' : 'border-white/10 bg-[#111726] text-gray-400 hover:text-gray-200'"
                                                    class="p-2 rounded-lg border text-left transition">
                                                <div class="text-[11px] font-black flex items-center justify-between">
                                                    <span><i class="fa-solid fa-hammer mr-1"></i> Geweld</span>
                                                    <span x-text="chanceGeweld + '%'">{{ $chanceGeweld }}%</span>
                                                </div>
                                                <div class="text-[10px] opacity-80 mt-0.5">+4 Resp • +210 XP</div>
                                            </button>
                                        </div>

                                        <button type="submit"
                                                @if($isImprisoned) disabled @endif
                                                class="w-full py-2.5 px-3 rounded-lg font-black text-xs uppercase tracking-wider transition flex items-center justify-center gap-2 {{ $isImprisoned ? 'bg-gray-800 text-gray-500 cursor-not-allowed' : 'bg-gradient-to-r from-red-600 to-amber-600 hover:from-red-500 hover:to-amber-500 text-white shadow-lg' }}">
                                            <i class="fa-solid fa-unlock"></i>
                                            <span x-text="selectedMethod === 'stil' ? 'Stille Sluipuitbraak (15 Energie)' : 'Breekijzer & Forceerwerk (15 Energie)'">
                                                Stille Sluipuitbraak (15 Energie)
                                            </span>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ONDERSTE SECTIE: KLINIEK MACHTSHIËRARCHIE TOP 10 -->
    <div class="rounded-2xl border border-white/10 bg-[#111726] overflow-hidden shadow-xl">
        <div class="px-5 py-4 border-b border-white/10 bg-[#151d30] flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-400">
                    <i class="fa-solid fa-crown text-sm"></i>
                </div>
                <div>
                    <h2 class="text-sm font-black uppercase tracking-wider text-white">Kliniek Machtshiërarchie — Top 10 Patiënten</h2>
                    <p class="text-[11px] text-gray-400">De meest invloedrijke kopstukken binnen de TBS-muren</p>
                </div>
            </div>
        </div>

        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            @foreach($topRankings as $idx => $ranked)
                <a href="{{ route('profile.show', $ranked->name) }}"
                   class="p-3 rounded-xl bg-[#0b0f19] border border-white/10 hover:border-amber-500/40 transition flex items-center gap-3 min-w-0">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-black shrink-0 {{ $idx === 0 ? 'bg-amber-500 text-black' : ($idx === 1 ? 'bg-gray-300 text-black' : ($idx === 2 ? 'bg-amber-700 text-white' : 'bg-white/10 text-gray-400')) }}">
                        #{{ $idx + 1 }}
                    </div>
                    <div class="{{ $ranked->getAvatarBorderClasses() }} rounded-full shrink-0">
                        <img src="{{ $ranked->avatar_url }}" alt="{{ $ranked->name }}" class="w-9 h-9 rounded-full object-cover border border-white/15">
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-black truncate {{ $ranked->isVip() && !$ranked->getCustomNameColor() ? 'text-amber-300' : 'text-white' }}" @if($ranked->getCustomNameColor()) style="color: {{ $ranked->getCustomNameColor() }}" @endif>{{ $ranked->name }}</div>
                        <div class="text-[10px] text-gray-400">Lvl {{ $ranked->level }} • {{ number_format($ranked->respect) }} Resp</div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
function prisonPageController(config) {
    return {
        isImprisoned: config.isImprisoned,
        remainingSecs: config.remainingSecs,
        adsProgress: config.adsProgress,
        adsRequired: config.adsRequired,
        escapeAttempted: config.escapeAttempted,
        reduceCooldownSecs: config.reduceCooldownSecs || 0,
        watchingAd: false,
        timerInterval: null,

        startCountdown() {
            this.timerInterval = setInterval(() => {
                if (this.reduceCooldownSecs > 0) {
                    this.reduceCooldownSecs--;
                }
                if (this.isImprisoned && this.remainingSecs > 0) {
                    this.remainingSecs--;
                    if (this.remainingSecs <= 0) {
                        this.isImprisoned = false;
                        window.location.reload();
                    }
                }
                document.querySelectorAll('[data-inmate-timer]').forEach((el) => {
                    let s = parseInt(el.getAttribute('data-inmate-timer') || '0', 10);
                    if (s > 0) {
                        s--;
                        el.setAttribute('data-inmate-timer', String(s));
                        el.innerHTML = '<i class="fa-regular fa-clock mr-1"></i>' + this.formatTime(s);
                    }
                });
            }, 1000);
        },

        formatTime(secs) {
            const s = Math.max(0, parseInt(secs || 0, 10));
            const m = Math.floor(s / 60);
            const rem = s % 60;
            return String(m).padStart(2, '0') + ':' + String(rem).padStart(2, '0');
        },

        async attemptEscapeAjax(event) {
            event.target.submit();
        },

        watchAdForFreedom() {
            if (typeof window.tbsOpenSponsorAd === 'function') {
                this.watchingAd = true;
                window.tbsOpenSponsorAd('prison', () => {
                    window.location.reload();
                });
            } else if (typeof window.openSponsorVideoModal === 'function') {
                this.watchingAd = true;
                window.openSponsorVideoModal('prison', () => {
                    window.location.reload();
                });
            } else {
                window.location.reload();
            }
        }
    };
}
</script>
@endpush
@endsection
