@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ searchQuery: '' }">

    <!-- Top Hero Banner -->
    <div class="rounded-2xl shadow-sm p-6 sm:p-8 text-white relative overflow-hidden bg-slate-950 border border-purple-900/40">
        <!-- Background Banner Asset (TBS Paarse Style) -->
        <div class="absolute inset-0 z-0 pointer-events-none">
            <img src="{{ asset('assets/illustrations/banner_fight.jpg') }}" alt="De Luchtplaats" class="w-full h-full object-cover object-center opacity-45">
            <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/85 to-purple-950/70"></div>
        </div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 bg-purple-500/20 text-purple-300 text-xs font-bold px-3 py-1 rounded-full border border-purple-400/20 mb-2">
                    <i class="fa-solid fa-sack-dollar text-emerald-400"></i> De Luchtplaats • Geld Stelen & Beroven
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Geld Stelen op de Luchtplaats</h1>
                <p class="text-slate-300 text-xs sm:text-sm mt-1 max-w-xl leading-relaxed">
                    Beroof andere patiënten van hun contante KliniekCash door ze te overmeesteren op de luchtplaats. Jat 10% tot 25% van hun contanten, verdien respect en rank-XP om de hiërarchie te domineren!
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="px-3.5 py-2 rounded-xl bg-white/10 backdrop-blur-xs border border-white/15 text-xs font-bold text-emerald-300 flex items-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-bolt text-emerald-400"></i> {{ $character->energy }} / {{ $character->max_energy }} Energie
                </span>
                <span class="px-3.5 py-2 rounded-xl bg-white/10 backdrop-blur-xs border border-white/15 text-xs font-bold text-purple-300 flex items-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-fire text-purple-400"></i> {{ number_format($character->respect, 0, ',', '.') }} Respect
                </span>
            </div>
        </div>
    </div>

    <!-- MAIN TWO COLUMN SLAYLIST LAYOUT -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left Column: Slaylist Sidebar Card -->
        <div class="lg:col-span-3 space-y-4">
            <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
                <!-- Slaylist Header Tab -->
                <div class="bg-slate-50 px-4 py-3 flex items-center justify-between border-b border-slate-200">
                    <span class="text-xs font-extrabold tracking-wider uppercase text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-list text-purple-600 text-xs"></i> Slaylist
                    </span>
                    <span class="text-xs font-bold text-slate-400">Patiënten</span>
                </div>

                <div class="p-4 space-y-3">
                    <!-- Online Status Counter -->
                    <div class="flex items-center justify-between text-xs font-bold text-slate-700 bg-slate-50 px-3 py-2 rounded-lg border border-slate-200">
                        <span>Online ({{ count($opponents) + 1 }})</span>
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    </div>

                    <!-- Search Input -->
                    <div class="relative">
                        <input type="text" x-model="searchQuery" placeholder="Zoeken..." 
                                class="w-full bg-white border border-slate-300 focus:border-purple-600 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-600">
                        <i class="fa-solid fa-magnifying-glass absolute right-3 top-2.5 text-slate-400 text-xs"></i>
                    </div>
                </div>
            </div>

            <!-- Dagelijkse Aanvallen Limiet & Cooldown Info -->
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Dagelijkse Aanvallen & Berovingen</span>
                    <span class="text-xs font-extrabold text-slate-900 font-mono">{{ $attacksToday }} / 100</span>
                </div>
                <!-- Progress bar -->
                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-200">
                    <div class="bg-purple-600 h-full rounded-full transition-all duration-300" 
                         style="width: {{ min(100, $attacksToday) }}%"></div>
                </div>
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span>Resterend vandaag:</span>
                    <strong class="text-purple-700 font-mono font-bold">{{ $attacksRemaining }} over</strong>
                </div>
            </div>

            <!-- Your Slay & Respect Info -->
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs space-y-2.5">
                <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2">
                    Jouw Status
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-medium">Respect:</span>
                    <span class="font-extrabold text-purple-700">{{ number_format($character->respect, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-medium">Contant op zak:</span>
                    <span class="font-extrabold text-emerald-600">€{{ number_format($character->cash, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-medium">Energie:</span>
                    <span class="font-extrabold text-slate-900">{{ $character->energy }} / {{ $character->max_energy }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-medium">Gezondheid:</span>
                    <span class="font-extrabold text-rose-600">{{ $character->health }}%</span>
                </div>
            </div>
        </div>

        <!-- Right Column: Luchtplaats Patiënten Table Card -->
        <div class="lg:col-span-9 bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-4"
             x-data="{ 
                 globalCd: {{ $cooldownSeconds }}
             }"
             x-init="
                if (globalCd > 0) { const t = setInterval(() => { globalCd--; if(globalCd <= 0) clearInterval(t); }, 1000); }
             ">

            <script>
                window.tbsTurnstileToken = '';
                function onTurnstileSuccess(token) {
                    window.tbsTurnstileToken = token;
                    document.querySelectorAll('input.turnstile-token-input').forEach(function(el) {
                        el.value = token;
                    });
                    const banner = document.getElementById('turnstile-container-banner');
                    if (banner) {
                        banner.classList.remove('bg-purple-50', 'border-purple-300');
                        banner.classList.add('bg-emerald-50', 'border-emerald-300');
                    }
                    const statusText = document.getElementById('turnstile-status-text');
                    if (statusText) {
                        statusText.innerHTML = '<span class="text-xs font-bold text-emerald-700 flex items-center gap-1.5"><i class="fa-solid fa-circle-check text-emerald-600"></i> Beveiliging gecontroleerd! Klik nu direct op \'Steel Geld\'.</span>';
                    }
                }
                window.onTurnstileSuccess = onTurnstileSuccess;
            </script>

            <!-- CLOUDFLARE TURNSTILE SECURITY BANNER IF REQUIRED -->
            @if($isCaptchaRequired)
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
            <div id="turnstile-container-banner" class="bg-purple-50 border-2 border-purple-300 rounded-xl p-4 shadow-xs space-y-3 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-purple-900">
                            Cloudflare Turnstile Zaalwacht Controle
                        </h4>
                        <p id="turnstile-status-text" class="text-xs text-purple-700 font-medium">
                            Bevestig via de Turnstile verificatie dat je een echte speler bent om contanten te kunnen stelen op de luchtplaats.
                        </p>
                    </div>
                </div>

                <div class="bg-white p-3 rounded-lg border border-purple-200 inline-block shadow-xs">
                    <div class="cf-turnstile" data-sitekey="{{ $turnstileSiteKey ?? '1x00000000000000000000AA' }}" data-callback="onTurnstileSuccess" data-theme="light"></div>
                </div>
            </div>
            @endif

            <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 pb-3 gap-2">
                <div class="flex items-center gap-2 text-sm font-extrabold text-slate-900 uppercase tracking-wide">
                    <i class="fa-solid fa-sack-dollar text-emerald-600"></i>
                    <span>Patiënten op de Luchtplaats (Contanten Beroven)</span>
                </div>
                <div class="text-xs font-bold text-slate-400">
                    <span class="text-purple-700 font-extrabold">3s cooldown</span> tussen berovingen | Max 100 per dag
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2">
                            <th class="py-3 px-2 w-10 text-center">#</th>
                            <th class="py-3 px-3">PATIËNT & CONTANTEN</th>
                            <th class="py-3 px-3">GANG</th>
                            <th class="py-3 px-3 text-right">RESPECT</th>
                            <th class="py-3 px-3 text-right">STEEL GELD</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($opponents as $index => $opp)
                        <tr x-show="!searchQuery || '{{ strtolower($opp->name) }}'.includes(searchQuery.toLowerCase())"
                            class="hover:bg-slate-50 transition-colors">
                            
                            <!-- Rank Number -->
                            <td class="py-3.5 px-2 text-center font-bold font-mono text-xs {{ $index < 3 ? 'text-purple-700' : 'text-slate-400' }}">
                                {{ $index + 1 }}
                            </td>

                            <!-- Patient avatar & name & cash -->
                            <td class="py-3.5 px-3">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <img src="{{ $opp->avatar_url }}" alt="{{ $opp->name }}" 
                                             class="w-10 h-10 rounded-full object-cover border border-slate-200 shadow-xs">
                                        <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-emerald-500 border-2 border-white"></span>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <a href="{{ route('profile.show', $opp->name) }}" class="font-extrabold text-slate-900 hover:text-purple-600 transition text-xs">
                                                {{ $opp->name }}
                                            </a>
                                            <i class="fa-solid fa-circle-check text-purple-600 text-[10px]"></i>
                                        </div>
                                        <div class="text-[11px] font-extrabold text-emerald-600 flex items-center gap-1">
                                            <i class="fa-solid fa-money-bill-wave text-[10px]"></i>
                                            <span>€{{ $opp->cash >= 1000000 ? number_format($opp->cash / 1000000, 1) . 'M' : number_format($opp->cash, 0, ',', '.') }}</span>
                                            <span class="text-[9px] text-slate-400 font-normal">op zak</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Gang Column -->
                            <td class="py-3.5 px-3">
                                @if($opp->gang)
                                    <span class="text-xs font-bold text-slate-700 flex items-center gap-1">
                                        <span>{{ $opp->gang->name }}</span>
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs italic">Geen gang</span>
                                @endif
                            </td>

                            <!-- Slay/Respect Column -->
                            <td class="py-3.5 px-3 text-right font-extrabold text-purple-700 font-mono">
                                {{ $opp->respect >= 1000000 ? number_format($opp->respect / 1000000, 2) . 'M' : ($opp->respect >= 1000 ? number_format($opp->respect / 1000, 1) . 'K' : $opp->respect) }}
                            </td>

                            <!-- Action: Steel Geld Button -->
                            <td class="py-3.5 px-3 text-right">
                                <form action="{{ route('fight.attack') }}" method="POST" class="inline-block"
                                      @submit="
                                        if(globalCd > 0) { $event.preventDefault(); return false; }
                                        const token = window.tbsTurnstileToken 
                                            || (document.querySelector('.cf-turnstile [name=cf-turnstile-response]') ? document.querySelector('.cf-turnstile [name=cf-turnstile-response]').value : '')
                                            || (window.turnstile ? window.turnstile.getResponse() : '');
                                        if (token) {
                                            $el.querySelector('input.turnstile-token-input').value = token;
                                        }
                                      ">
                                    @csrf
                                    <input type="hidden" name="opponent_id" value="{{ $opp->id }}">
                                    <input type="hidden" name="cf-turnstile-response" class="turnstile-token-input" value="">

                                    <button type="submit"
                                            :disabled="globalCd > 0 || {{ $attacksRemaining <= 0 || $character->energy < 20 ? 'true' : 'false' }}"
                                            title="Steel geld van {{ $opp->name }} (20 energie)"
                                            class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed text-white font-extrabold text-xs uppercase tracking-wider transition shadow-xs cursor-pointer inline-flex items-center gap-1.5">
                                        <template x-if="globalCd <= 0">
                                            <span class="inline-flex items-center gap-1.5">
                                                <i class="fa-solid fa-sack-dollar text-xs"></i>
                                                <span class="hidden sm:inline">Steel Geld</span>
                                            </span>
                                        </template>
                                        <template x-if="globalCd > 0">
                                            <span class="inline-flex items-center gap-1 font-mono">
                                                <i class="fa-solid fa-stopwatch text-xs"></i>
                                                <span x-text="globalCd + 's'"></span>
                                            </span>
                                        </template>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400 text-xs">
                                Geen patiënten gevonden.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div class="pt-4 border-t border-slate-100 flex justify-end">
                {{ $opponents->links() }}
            </div>
        </div>

    </div>

    <!-- Bottom Ranglijst Podium -->
    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-5">
        <div class="flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-3">
            <i class="fa-solid fa-trophy text-amber-500"></i>
            <span>RANGLIJST TOP PATIËNTEN</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 lg:grid-cols-10 gap-3 pt-2">
            @foreach($topRankings ?? [] as $index => $player)
            <div class="flex flex-col items-center text-center group">
                <div class="relative mb-2">
                    <img src="{{ $player->avatar_url }}" alt="{{ $player->name }}" 
                         class="w-14 h-14 sm:w-16 sm:h-16 rounded-full object-cover border-2 {{ $index === 0 ? 'border-amber-400 ring-2 ring-amber-400/30' : ($index === 1 ? 'border-purple-400' : 'border-slate-200') }} group-hover:scale-105 transition-transform shadow-xs">
                    
                    <span class="absolute -top-1 -left-1 w-6 h-6 rounded-full {{ $index === 0 ? 'bg-amber-400 text-slate-950 font-black' : ($index < 3 ? 'bg-purple-600 text-white font-bold' : 'bg-slate-800 text-white font-bold') }} text-xs flex items-center justify-center shadow">
                        {{ $index + 1 }}
                    </span>

                    <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 text-white text-[9px] flex items-center justify-center">
                        <i class="fa-solid fa-check"></i>
                    </span>
                </div>

                <a href="{{ route('profile.show', $player->name) }}" class="text-xs font-bold text-slate-900 group-hover:text-purple-600 transition truncate w-full block">
                    {{ $player->name }}
                </a>
                <span class="text-[10px] font-bold text-slate-400">
                    {{ $player->respect >= 1000000 ? number_format($player->respect / 1000000, 2) . 'M' : ($player->respect >= 1000 ? number_format($player->respect / 1000, 1) . 'K' : $player->respect) }}
                </span>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
