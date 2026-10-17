@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Top Header Banner (Clean Solid Styling) -->
    <div class="bg-white dark:bg-[#111827] rounded-2xl p-6 sm:p-7 border border-slate-200 dark:border-slate-800 shadow-xs relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 dark:bg-slate-800 border border-amber-200 dark:border-slate-700 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl shrink-0 shadow-2xs mt-0.5">
                    <i class="fa-solid fa-person-digging"></i>
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-700/60">
                            <i class="fa-solid fa-clipboard-check"></i> Officiële Werkplaats &bull; Resocialisatie
                        </span>
                        <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800/60">
                            100% Veilig &bull; 0% Isoleercel Risico
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        Dagbesteding & Werkplaats
                    </h1>
                    <p class="text-xs text-slate-600 dark:text-slate-300 max-w-2xl leading-relaxed font-medium">
                        Werk eerlijk voor je geld zonder enig risico om in de isoleercel te belanden! Verdien een vast uurloon, verhoog je statistieken en vind af en toe waardevolle spullen tussen het zaagsel of in de binnentuinen.
                    </p>
                </div>
            </div>

            <!-- Stats Summary Badges -->
            <div class="flex items-center gap-3 shrink-0 self-start md:self-center">
                <div class="p-3 sm:p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-center min-w-[100px]">
                    <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block mb-0.5">Diensten</span>
                    <span class="text-lg font-black text-slate-900 dark:text-white leading-none font-mono">
                        {{ $character->dagbesteding_shifts_completed ?? 0 }}x
                    </span>
                </div>
                <div class="p-3 sm:p-3.5 rounded-xl bg-emerald-50 dark:bg-slate-800 border border-emerald-200 dark:border-slate-700 text-center min-w-[120px]">
                    <span class="text-[9px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-400 block mb-0.5">Totaal Loon</span>
                    <span class="text-lg font-black text-emerald-600 dark:text-emerald-400 leading-none font-mono">
                        €{{ number_format($character->dagbesteding_earned_total ?? 0, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Cooldown Notice -->
    @if($activeCooldown > 0)
    <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/20 border border-amber-300 dark:border-amber-800/60 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-lg font-black shrink-0 shadow-xs">
                <i class="fa-solid fa-mug-hot"></i>
            </div>
            <div>
                <h4 class="text-xs font-black text-amber-950 dark:text-amber-200">Koffiepauze Actief</h4>
                <p class="text-[11px] text-amber-800 dark:text-amber-300">
                    De zaalwacht laat je even bijkomen. Wacht nog <strong>{{ $activeCooldown }} seconden</strong> of sla direct over met {{ $daycareSkipCost }} Sterren.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="window.tbsOpenSponsorAd('cooldown')" class="bg-purple-600 hover:bg-purple-700 active:scale-95 text-white text-xs font-black px-4 py-2 rounded-xl transition shadow-xs flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-tv text-white text-[11px]"></i>
                <span>Kijk Ad (Reset Cooldown)</span>
            </button>
            <form action="{{ route('cooldown.skip') }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="dagbesteding">
                <button type="submit" class="bg-amber-500 hover:bg-amber-600 active:scale-95 text-white font-black text-xs px-3.5 py-2 rounded-xl transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-star text-white text-[11px]"></i>
                    <span>{{ $daycareSkipCost }} Sterren ⭐</span>
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- LIST OF JOBS -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($jobs as $job)
        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex flex-col justify-between space-y-4 hover:border-amber-400 dark:hover:border-amber-500 transition group">
            
            <div class="space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-slate-800 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl shrink-0 border border-amber-200 dark:border-slate-700">
                        <i class="fa-solid {{ $job['icon'] }}"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono">
                        {{ $job['energy'] }} Energie
                    </span>
                </div>

                <div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition">
                        {{ $job['name'] }}
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed mt-1 font-medium">
                        {{ $job['description'] }}
                    </p>
                </div>

                <!-- Rewards strip -->
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 space-y-1.5 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 font-bold">Vergoeding:</span>
                        <span class="font-black text-emerald-600 dark:text-emerald-400 font-mono">€{{ number_format($job['min_cash'], 0, ',', '.') }} - €{{ number_format($job['max_cash'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 font-bold">Ervaring:</span>
                        <span class="font-extrabold text-purple-600 dark:text-purple-400">+{{ $job['xp'] }} XP</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 font-bold">Ontwikkeling:</span>
                        <span class="font-bold text-slate-700 dark:text-slate-200 text-[11px]">{{ $job['stat_bonus'] }}</span>
                    </div>
                </div>
            </div>

            <form action="{{ route('daycare.perform') }}" method="POST">
                @csrf
                <input type="hidden" name="job_id" value="{{ $job['id'] }}">
                <button type="submit" 
                        {{ $activeCooldown > 0 || $character->energy < $job['energy'] ? 'disabled' : '' }}
                        class="w-full py-2.5 px-4 rounded-xl font-black text-xs uppercase tracking-wider transition text-center shadow-xs flex items-center justify-center gap-2 {{ $activeCooldown > 0 || $character->energy < $job['energy'] ? 'bg-slate-100 dark:bg-slate-800 text-slate-400 cursor-not-allowed border border-slate-200 dark:border-slate-700' : 'bg-slate-900 dark:bg-amber-500 hover:bg-slate-800 dark:hover:bg-amber-600 text-white dark:text-slate-950 cursor-pointer' }}">
                    <i class="fa-solid fa-play text-[10px]"></i>
                    <span>Dienst Draaien (-{{ $job['energy'] }} EN)</span>
                </button>
            </form>

        </div>
        @endforeach
    </div>

</div>
@endsection
