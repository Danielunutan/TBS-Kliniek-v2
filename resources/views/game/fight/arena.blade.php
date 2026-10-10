@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6"
     x-data="{
         countdown: 5,
         resolved: false,
         won: {{ $arenaBattle['won'] ? 'true' : 'false' }},
         init() {
             const timer = setInterval(() => {
                 this.countdown--;
                 if (this.countdown <= 0) {
                     clearInterval(timer);
                     this.resolved = true;
                 }
             }, 1000);
         },
         skip() {
             this.countdown = 0;
             this.resolved = true;
         }
     }">

    <!-- Top Breadcrumb / Title -->
    <div class="flex items-center justify-between">
        <a href="{{ route('fight.index') }}" class="text-xs font-bold text-slate-500 hover:text-purple-600 transition inline-flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> Terug naar de Luchtplaats
        </a>
        <span class="text-xs font-mono font-bold text-purple-700 bg-purple-50 px-3 py-1 rounded-full border border-purple-200">
            Aanvallen over vandaag: {{ $arenaBattle['left_today'] }} / 100
        </span>
    </div>

    <!-- MAIN ARENA COMBAT STAGE -->
    <div class="bg-slate-950 border border-purple-900/40 rounded-2xl p-6 sm:p-10 text-white relative overflow-hidden shadow-2xl">
        <!-- Background Lighting FX -->
        <div class="absolute inset-0 z-0 pointer-events-none">
            <img src="{{ asset('assets/illustrations/banner_fight.jpg') }}" alt="Arena" class="w-full h-full object-cover opacity-25">
            <div class="absolute inset-0 bg-gradient-to-b from-slate-950/80 via-slate-950/90 to-slate-950"></div>
        </div>

        <div class="relative z-10 space-y-8">
            
            <!-- SUSPENSE COUNTDOWN HEADER (Active for first 5 seconds) -->
            <div x-show="!resolved" class="text-center space-y-3">
                <div class="inline-flex items-center gap-2 bg-purple-500/20 text-purple-300 text-xs font-extrabold px-4 py-1.5 rounded-full border border-purple-400/30 uppercase tracking-widest animate-pulse">
                    <i class="fa-solid fa-hand-fist"></i> Gevecht Bezig op de Luchtplaats
                </div>
                <h2 class="text-2xl sm:text-4xl font-black tracking-tight text-white">
                    KRACHTMETING IN UITVOERING...
                </h2>
                <p class="text-slate-400 text-xs sm:text-sm">
                    De zaalwachten kijken de andere kant op terwijl de klappen vallen!
                </p>

                <!-- 5-Second Circular/Pill Countdown Badge -->
                <div class="pt-2 flex flex-col items-center justify-center gap-2">
                    <div class="w-16 h-16 rounded-full bg-purple-900/60 border-2 border-purple-400 flex items-center justify-center font-mono font-black text-2xl text-purple-200 shadow-lg shadow-purple-900/50 animate-bounce">
                        <span x-text="countdown"></span>s
                    </div>
                    <button @click="skip()" class="text-xs text-slate-400 hover:text-white underline cursor-pointer mt-1">
                        Direct uitslag bekijken &raquo;
                    </button>
                </div>
            </div>

            <!-- RESULT BANNER (Appears after 5 seconds) -->
            <div x-show="resolved" x-cloak class="text-center space-y-3 transition-all duration-500 transform">
                <template x-if="won">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 bg-emerald-500/20 text-emerald-300 text-xs font-black px-5 py-2 rounded-full border border-emerald-400/40 uppercase tracking-widest">
                            <i class="fa-solid fa-trophy text-amber-400"></i> OVERWINNING OP DE LUCHTPLAATS!
                        </div>
                        <h2 class="text-3xl sm:text-5xl font-black tracking-tight text-emerald-400">
                            JE HEBT GEWONNEN!
                        </h2>
                        <p class="text-slate-300 text-xs sm:text-sm max-w-md mx-auto">
                            Je hebt <strong class="text-white">{{ $defender->name }}</strong> met succes neergehaald en zijn respect afgedwongen.
                        </p>
                    </div>
                </template>

                <template x-if="!won">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 bg-rose-500/20 text-rose-300 text-xs font-black px-5 py-2 rounded-full border border-rose-400/40 uppercase tracking-widest">
                            <i class="fa-solid fa-skull"></i> AANVAL AFGESLAGEN!
                        </div>
                        <h2 class="text-3xl sm:text-5xl font-black tracking-tight text-rose-400">
                            JE BENT VERSLAGEN!
                        </h2>
                        <p class="text-slate-300 text-xs sm:text-sm max-w-md mx-auto">
                            <strong class="text-white">{{ $defender->name }}</strong> was te sterk en sloeg hard van zich af!
                        </p>
                    </div>
                </template>
            </div>

            <!-- FIGHTERS VS CLASH CARD -->
            <div class="grid grid-cols-1 md:grid-cols-7 gap-6 items-center">
                <!-- Attacker (Player) -->
                <div class="md:col-span-3 bg-white/5 border border-white/10 rounded-2xl p-5 text-center flex flex-col items-center gap-3 backdrop-blur-xs relative overflow-hidden">
                    <div class="absolute top-2 left-3 text-[10px] font-black uppercase tracking-wider text-purple-300">
                        Jij (Aanvaller)
                    </div>
                    <div class="relative mt-2">
                        <img src="{{ $attacker->avatar_url }}" alt="{{ $attacker->name }}" 
                             class="w-24 h-24 rounded-full object-cover border-4 border-purple-500 shadow-xl shadow-purple-900/40">
                        <span class="absolute bottom-0 right-0 bg-purple-600 text-white font-mono text-xs font-bold px-2 py-0.5 rounded-full border border-white/20">
                            Lvl {{ $attacker->level }}
                        </span>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-white">{{ $attacker->name }}</h3>
                        <p class="text-xs text-purple-300 font-semibold">{{ $attacker->gang ? $attacker->gang->name : 'Geen Gang' }}</p>
                    </div>
                    <div class="w-full grid grid-cols-2 gap-2 text-xs pt-2 border-t border-white/10">
                        <div class="bg-white/5 rounded-lg p-2">
                            <span class="text-slate-400 block text-[10px] uppercase">Kracht</span>
                            <strong class="text-white font-mono text-sm">{{ $attacker->strength }}</strong>
                        </div>
                        <div class="bg-white/5 rounded-lg p-2">
                            <span class="text-slate-400 block text-[10px] uppercase">Gezondheid</span>
                            <strong class="text-rose-400 font-mono text-sm">{{ $attacker->health }}%</strong>
                        </div>
                    </div>
                </div>

                <!-- Center VS Clash Badge -->
                <div class="md:col-span-1 flex flex-col items-center justify-center gap-2">
                    <div class="w-14 h-14 rounded-full bg-purple-600/30 border-2 border-purple-400/60 flex items-center justify-center font-black text-xl text-purple-200 shadow-lg shadow-purple-600/30"
                         :class="{ 'animate-pulse': !resolved }">
                        VS
                    </div>
                    <div class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500 text-center">
                        <template x-if="!resolved">
                            <span>Vechten...</span>
                        </template>
                        <template x-if="resolved">
                            <span>Klaar</span>
                        </template>
                    </div>
                </div>

                <!-- Defender -->
                <div class="md:col-span-3 bg-white/5 border border-white/10 rounded-2xl p-5 text-center flex flex-col items-center gap-3 backdrop-blur-xs relative overflow-hidden">
                    <div class="absolute top-2 right-3 text-[10px] font-black uppercase tracking-wider text-slate-400">
                        Tegenstander
                    </div>
                    <div class="relative mt-2">
                        <img src="{{ $defender->avatar_url }}" alt="{{ $defender->name }}" 
                             class="w-24 h-24 rounded-full object-cover border-4 border-slate-600 shadow-xl">
                        <span class="absolute bottom-0 right-0 bg-slate-800 text-white font-mono text-xs font-bold px-2 py-0.5 rounded-full border border-white/20">
                            Lvl {{ $defender->level }}
                        </span>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-white">{{ $defender->name }}</h3>
                        <p class="text-xs text-slate-400 font-semibold">{{ $defender->gang ? $defender->gang->name : 'Geen Gang' }}</p>
                    </div>
                    <div class="w-full grid grid-cols-2 gap-2 text-xs pt-2 border-t border-white/10">
                        <div class="bg-white/5 rounded-lg p-2">
                            <span class="text-slate-400 block text-[10px] uppercase">Verdediging</span>
                            <strong class="text-white font-mono text-sm">{{ $defender->defense }}</strong>
                        </div>
                        <div class="bg-white/5 rounded-lg p-2">
                            <span class="text-slate-400 block text-[10px] uppercase">Respect</span>
                            <strong class="text-purple-400 font-mono text-sm">{{ number_format($defender->respect, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LOOT & REWARDS SUMMARY (Revealed when finished) -->
            <div x-show="resolved" x-cloak class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-xs space-y-4">
                <div class="text-xs font-extrabold uppercase tracking-wider text-slate-400 border-b border-white/10 pb-3 flex items-center justify-between">
                    <span>Gevechtsresultaat</span>
                    <span class="font-mono text-slate-300">Ronde voltooid</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                    <div class="bg-white/5 rounded-xl p-3 border border-white/5">
                        <span class="text-[10px] font-extrabold uppercase text-slate-400 block mb-1">Buit Gesteeld</span>
                        <span class="text-base font-black text-emerald-400 font-mono">
                            +€{{ number_format($arenaBattle['stolen_cash'] ?? 0, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="bg-white/5 rounded-xl p-3 border border-white/5">
                        <span class="text-[10px] font-extrabold uppercase text-slate-400 block mb-1">XP Verdiend</span>
                        <span class="text-base font-black text-purple-300 font-mono">
                            +{{ $arenaBattle['xp'] ?? 0 }} XP
                        </span>
                    </div>
                    <div class="bg-white/5 rounded-xl p-3 border border-white/5">
                        <span class="text-[10px] font-extrabold uppercase text-slate-400 block mb-1">Respect</span>
                        <span class="text-base font-black text-amber-400 font-mono">
                            +{{ $arenaBattle['respect'] ?? 0 }}
                        </span>
                    </div>
                    <div class="bg-white/5 rounded-xl p-3 border border-white/5">
                        <span class="text-[10px] font-extrabold uppercase text-slate-400 block mb-1">Schade Opgelopen</span>
                        <span class="text-base font-black text-rose-400 font-mono">
                            -{{ $arenaBattle['damage'] ?? 0 }} HP
                        </span>
                    </div>
                </div>

                <!-- Action CTA buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-white/10">
                    <a href="{{ route('fight.index') }}" 
                       class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs uppercase tracking-wider transition text-center shadow-lg shadow-purple-900/40">
                        <i class="fa-solid fa-arrow-left mr-1.5"></i> Terug naar Luchtplaats
                    </a>

                    <form action="{{ route('fight.attack') }}" method="POST" class="w-full sm:w-auto">
                        @csrf
                        <input type="hidden" name="opponent_id" value="{{ $defender->id }}">
                        <button type="submit" 
                                {{ $attacker->energy < 20 || $arenaBattle['left_today'] <= 0 ? 'disabled' : '' }}
                                class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-extrabold text-xs uppercase tracking-wider transition text-center shadow-lg shadow-emerald-900/40">
                            <i class="fa-solid fa-hand-fist mr-1.5"></i> Nog een keer aanvallen (20 energie)
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
