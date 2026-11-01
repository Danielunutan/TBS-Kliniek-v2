@extends('layouts.app')

@section('content')
@php
    $attacker = $attacker ?? $character;
@endphp
<div class="max-w-4xl mx-auto space-y-6"
     x-data="{
         countdown: {{ (!empty($arenaBattle['resolved'])) ? 0 : 2 }},
         resolved: {{ (!empty($arenaBattle['resolved'])) ? 'true' : 'false' }},
         resolving: false,
         won: {{ (!empty($arenaBattle['won'])) ? 'true' : 'false' }},
         stolenCash: {{ (int)($arenaBattle['stolen_cash'] ?? 0) }},
         claimedBounty: {{ (int)($arenaBattle['claimed_bounty'] ?? 0) }},
         damage: {{ (int)($arenaBattle['damage'] ?? 0) }},
         xp: {{ (int)($arenaBattle['xp'] ?? 0) }},
         respect: {{ (int)($arenaBattle['respect'] ?? 0) }},
         init() {
             if (this.resolved) return;
             const timer = setInterval(() => {
                 this.countdown--;
                 if (this.countdown <= 0) {
                     clearInterval(timer);
                     this.resolveBattle();
                 }
             }, 1000);
         },
         skip() {
             if (this.resolved || this.resolving) return;
             this.countdown = 0;
             this.resolveBattle();
         },
         async resolveBattle() {
             if (this.resolved || this.resolving) return;
             this.resolving = true;
             try {
                 const res = await fetch('{{ route('fight.arena.resolve', $defender) }}', {
                     method: 'POST',
                     headers: {
                         'X-CSRF-TOKEN': '{{ csrf_token() }}',
                         'Accept': 'application/json',
                         'Content-Type': 'application/json'
                     }
                 });
                 const data = await res.json();
                 if (data && data.success) {
                     this.won = data.won;
                     this.stolenCash = data.stolen_cash;
                     this.claimedBounty = data.claimed_bounty;
                     this.damage = data.damage;
                     this.xp = data.xp;
                     this.respect = data.respect;
                     this.resolved = true;
                     if (window.tbsApplyStats && data.stats) {
                         window.tbsApplyStats(data.stats);
                     }
                 } else {
                     this.resolved = true;
                 }
             } catch (e) {
                 this.resolved = true;
             } finally {
                 this.resolving = false;
             }
         }
     }">

    <!-- Top Breadcrumb / Title -->
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('fight.index') }}" class="text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-purple-600 dark:hover:text-purple-400 transition inline-flex items-center gap-1.5">
                <i class="fa-solid fa-list text-purple-600 dark:text-purple-400"></i> Respectlijst
            </a>
            <span class="text-slate-300 dark:text-slate-700">•</span>
            <a href="{{ route('profile.show', $defender->name) }}" class="text-xs font-bold text-purple-600 dark:text-purple-400 hover:underline inline-flex items-center gap-1.5">
                <i class="fa-solid fa-user"></i> Profiel van {{ $defender->name }}
            </a>
        </div>
        <span class="text-xs font-mono font-bold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 px-3 py-1 rounded-full border border-purple-200/80 dark:border-purple-800/70">
            Aanvallen over vandaag: {{ $arenaBattle['left_today'] ?? 0 }} / {{ $arenaBattle['max_today'] ?? 100 }}
        </span>
    </div>

    <!-- MAIN ARENA COMBAT STAGE (Clean White TBS Style) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 sm:p-10 text-slate-800 dark:text-slate-100 relative overflow-hidden shadow-xs">
        <div class="relative z-10 space-y-8">
            
            <!-- SUSPENSE COUNTDOWN HEADER (Active for first 2 seconds) -->
            <div x-show="!resolved" class="text-center space-y-3">
                <div class="inline-flex items-center gap-2 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 text-xs font-extrabold px-4 py-1.5 rounded-full border border-emerald-200/80 dark:border-emerald-800/70 uppercase tracking-widest animate-pulse">
                    <i class="fa-solid fa-sack-dollar text-emerald-600"></i> Luchtplaats Beroving in Uitvoering
                </div>
                <h2 class="text-2xl sm:text-4xl font-black tracking-tight text-slate-900 dark:text-white">
                    PATIËNT OVERMEESTEREN...
                </h2>
                <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm">
                    Je doorzoekt vliegensvlug de zakken van {{ $defender->name }} naar contant geld!
                </p>

                <!-- 2-Second Circular/Pill Countdown Badge -->
                <div class="pt-2 flex flex-col items-center justify-center gap-2">
                    <div class="w-14 h-14 rounded-full bg-emerald-50 dark:bg-emerald-950/60 border-2 border-emerald-400 dark:border-emerald-500 flex items-center justify-center font-mono font-black text-2xl text-emerald-700 dark:text-emerald-300 shadow-xs animate-bounce">
                        <span x-text="countdown"></span>s
                    </div>
                    <button @click="skip()" class="text-xs text-slate-400 dark:text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 underline cursor-pointer mt-1">
                        Direct uitslag bekijken &raquo;
                    </button>
                </div>
            </div>

            <!-- RESULT BANNER (Appears after countdown) -->
            <div x-show="resolved" x-cloak class="text-center space-y-3 transition-all duration-500 transform">
                <template x-if="won">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 text-xs font-black px-5 py-2 rounded-full border border-emerald-200/80 dark:border-emerald-800/70 uppercase tracking-widest">
                            <i class="fa-solid fa-sack-dollar text-amber-500"></i> BEROVING GESLAAGD!
                        </div>
                        <h2 class="text-3xl sm:text-5xl font-black tracking-tight text-emerald-600 dark:text-emerald-400">
                            <span x-show="stolenCash > 0">GELD GESTEELD!</span>
                            <span x-show="stolenCash <= 0">OVERMEESTERD!</span>
                        </h2>
                        <p class="text-slate-600 dark:text-slate-300 text-xs sm:text-sm max-w-md mx-auto">
                            <span x-show="stolenCash > 0">
                                Je hebt <strong class="text-emerald-700 dark:text-emerald-400 font-mono" x-text="'€' + new Intl.NumberFormat('nl-NL').format(stolenCash)">€{{ number_format($arenaBattle['stolen_cash'] ?? 0, 0, ',', '.') }}</strong> contant uit de zakken van <strong class="text-slate-900 dark:text-white">{{ $defender->name }}</strong> geroofd!
                            </span>
                            <span x-show="stolenCash <= 0">
                                Je hebt <strong class="text-slate-900 dark:text-white">{{ $defender->name }}</strong> overmeesterd, maar die had helaas geen contanten op zak!
                            </span>
                        </p>
                    </div>
                </template>

                <template x-if="!won">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 text-xs font-black px-5 py-2 rounded-full border border-rose-200/80 dark:border-rose-800/70 uppercase tracking-widest">
                            <i class="fa-solid fa-shield-halved text-rose-600"></i> BEROVING AFGESLAGEN!
                        </div>
                        <h2 class="text-3xl sm:text-5xl font-black tracking-tight text-rose-600 dark:text-rose-400">
                            JE BENT VERJAAGD!
                        </h2>
                        <p class="text-slate-600 dark:text-slate-300 text-xs sm:text-sm max-w-md mx-auto">
                            <strong class="text-slate-900 dark:text-white">{{ $defender->name }}</strong> was alert en sloeg hard van zich af voor je geld kon pakken!
                        </p>
                    </div>
                </template>
            </div>

            <!-- FIGHTERS VS CLASH CARD -->
            <div class="grid grid-cols-1 md:grid-cols-7 gap-6 items-center">
                <!-- Attacker (Player) -->
                <div class="md:col-span-3 bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-5 text-center flex flex-col items-center gap-3 relative overflow-hidden">
                    <div class="absolute top-2 left-3 text-[10px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-400">
                        Jij (Aanvaller)
                    </div>
                    <div class="relative mt-2">
                        <img src="{{ $attacker->avatar_url }}" alt="{{ $attacker->name }}" 
                             class="w-24 h-24 rounded-full object-cover border-4 border-purple-500 shadow-md">
                        <span class="absolute bottom-0 right-0 bg-purple-600 text-white font-mono text-xs font-bold px-2 py-0.5 rounded-full border border-white/20">
                            Lvl {{ $attacker->level }}
                        </span>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">{{ $attacker->name }}</h3>
                        <p class="text-xs text-purple-600 dark:text-purple-400 font-semibold">{{ $attacker->gang ? $attacker->gang->name : 'Geen Gang' }}</p>
                    </div>
                    <div class="w-full grid grid-cols-2 gap-2 text-xs pt-2 border-t border-slate-200/80 dark:border-slate-700/70">
                        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-2">
                            <span class="text-slate-400 dark:text-slate-500 block text-[10px] uppercase font-bold">Kracht</span>
                            <strong class="text-slate-900 dark:text-white font-mono text-sm">{{ $attacker->strength }}</strong>
                        </div>
                        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-2">
                            <span class="text-slate-400 dark:text-slate-500 block text-[10px] uppercase font-bold">Gezondheid</span>
                            <strong class="text-rose-600 dark:text-rose-400 font-mono text-sm">{{ $attacker->health }}%</strong>
                        </div>
                    </div>
                </div>

                <!-- Center VS Clash Badge -->
                <div class="md:col-span-1 flex flex-col items-center justify-center gap-2">
                    <div class="w-14 h-14 rounded-full bg-purple-100 dark:bg-purple-950/60 border-2 border-purple-300 dark:border-purple-700/80 flex items-center justify-center font-black text-xl text-purple-700 dark:text-purple-300 shadow-xs"
                         :class="{ 'animate-pulse': !resolved }">
                        VS
                    </div>
                    <div class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400 dark:text-slate-500 text-center">
                        <template x-if="!resolved">
                            <span>Vechten...</span>
                        </template>
                        <template x-if="resolved">
                            <span>Klaar</span>
                        </template>
                    </div>
                </div>

                <!-- Defender -->
                <div class="md:col-span-3 bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-5 text-center flex flex-col items-center gap-3 relative overflow-hidden">
                    <div class="absolute top-2 right-3 text-[10px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Tegenstander
                    </div>
                    <div class="relative mt-2">
                        <img src="{{ $defender->avatar_url }}" alt="{{ $defender->name }}" 
                             class="w-24 h-24 rounded-full object-cover border-4 border-slate-300 dark:border-slate-600 shadow-md">
                        <span class="absolute bottom-0 right-0 bg-slate-700 text-white font-mono text-xs font-bold px-2 py-0.5 rounded-full border border-white/20">
                            Lvl {{ $defender->level }}
                        </span>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">{{ $defender->name }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold">{{ $defender->gang ? $defender->gang->name : 'Geen Gang' }}</p>
                    </div>
                    <div class="w-full grid grid-cols-2 gap-2 text-xs pt-2 border-t border-slate-200/80 dark:border-slate-700/70">
                        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-2">
                            <span class="text-slate-400 dark:text-slate-500 block text-[10px] uppercase font-bold">Verdediging</span>
                            <strong class="text-slate-900 dark:text-white font-mono text-sm">{{ $defender->defense }}</strong>
                        </div>
                        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-2">
                            <span class="text-slate-400 dark:text-slate-500 block text-[10px] uppercase font-bold">Respect</span>
                            <strong class="text-purple-700 dark:text-purple-400 font-mono text-sm">{{ number_format($defender->respect, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LOOT & REWARDS SUMMARY (Revealed when finished) -->
            <div x-show="resolved" x-cloak class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-6 space-y-4">
                <div class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200/80 dark:border-slate-700/70 pb-3 flex items-center justify-between">
                    <span>Gevechtsresultaat</span>
                    <span class="font-mono text-slate-600 dark:text-slate-400">Ronde voltooid</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                    <div class="bg-white dark:bg-slate-900 rounded-xl p-3 border border-slate-200/80 dark:border-slate-700/70">
                        <span class="text-[10px] font-extrabold uppercase text-slate-400 dark:text-slate-500 block mb-1">Buit Gesteeld</span>
                        <span class="text-base font-black text-emerald-600 dark:text-emerald-400 font-mono" x-text="'+€' + new Intl.NumberFormat('nl-NL').format(stolenCash)">
                            +€{{ number_format($arenaBattle['stolen_cash'] ?? 0, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="bg-white dark:bg-slate-900 rounded-xl p-3 border border-slate-200/80 dark:border-slate-700/70">
                        <span class="text-[10px] font-extrabold uppercase text-slate-400 dark:text-slate-500 block mb-1">XP Verdiend</span>
                        <span class="text-base font-black text-purple-700 dark:text-purple-400 font-mono" x-text="'+' + xp + ' XP'">
                            +{{ $arenaBattle['xp'] ?? 0 }} XP
                        </span>
                    </div>
                    <div class="bg-white dark:bg-slate-900 rounded-xl p-3 border border-slate-200/80 dark:border-slate-700/70">
                        <span class="text-[10px] font-extrabold uppercase text-slate-400 dark:text-slate-500 block mb-1">Respect</span>
                        <span class="text-base font-black text-amber-600 dark:text-amber-400 font-mono" x-text="'+' + respect">
                            +{{ $arenaBattle['respect'] ?? 0 }}
                        </span>
                    </div>
                    <div class="bg-white dark:bg-slate-900 rounded-xl p-3 border border-slate-200/80 dark:border-slate-700/70">
                        <span class="text-[10px] font-extrabold uppercase text-slate-400 dark:text-slate-500 block mb-1">Schade Opgelopen</span>
                        <span class="text-base font-black text-rose-600 dark:text-rose-400 font-mono" x-text="'-' + damage + ' HP'">
                            -{{ $arenaBattle['damage'] ?? 0 }} HP
                        </span>
                    </div>
                </div>

                @if(!empty($arenaBattle['gained_strength']))
                    <div class="bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/70 text-amber-800 dark:text-amber-300 rounded-xl p-3 text-xs font-black flex items-center justify-center gap-2">
                        <i class="fa-solid fa-dumbbell text-amber-600"></i>
                        <span>Door deze felle knokpartij is jouw Kracht met +1 gestegen! 💪</span>
                    </div>
                @endif

                <!-- Action CTA buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-slate-200/80 dark:border-slate-800">
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <a href="{{ route('profile.show', $defender->name) }}" 
                           class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs uppercase tracking-wider transition-all duration-150 active:scale-95 text-center border border-slate-200/80 dark:border-slate-700 cursor-pointer">
                            <i class="fa-solid fa-user mr-1.5"></i> Naar Profiel
                        </a>
                        <a href="{{ route('fight.index') }}" 
                           class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 active:scale-95 text-center shadow-xs cursor-pointer">
                            <i class="fa-solid fa-list mr-1.5"></i> Respectlijst
                        </a>
                    </div>

                    <form action="{{ route('fight.attack') }}" method="POST" class="w-full sm:w-auto">
                        @csrf
                        <input type="hidden" name="opponent_id" value="{{ $defender->id }}">
                        <input type="hidden" name="from_profile" value="1">
                        <button type="submit" 
                                {{ $attacker->energy < 20 || ($arenaBattle['left_today'] ?? 0) <= 0 || $attacker->isImprisoned() || $attacker->isComatose() ? 'disabled' : '' }}
                                class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 disabled:opacity-40 disabled:cursor-not-allowed text-white font-black text-xs uppercase tracking-wider transition-all duration-150 active:scale-95 text-center shadow-xs cursor-pointer">
                            <i class="fa-solid fa-sack-dollar mr-1.5"></i> Opnieuw geld stelen (20 energie)
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
