@extends('layouts.app')

@section('title', 'Karakter Perks (24 Uur Actief) - TBS Kliniek')
@section('meta_description', 'Activeer krachtige 24-uurs voordelen en perks voor je TBS karakter met zeldzame Sterren ⭐.')

@section('content')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-amber-500 text-slate-950 flex items-center justify-center text-xl shadow-md shrink-0">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">Karakter Perks (24 Uur Actief)</h2>
                        <span class="px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 font-black text-[10px] uppercase tracking-wider">24 Uur Boost ⭐</span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Investeer je zeldzame Sterren in krachtige 24-uurs privileges. Elke geactiveerde perk blijft exact 24 uur actief en kan na afloop direct weer opnieuw worden geactiveerd!
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <div class="bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-center">
                    <div class="text-[10px] uppercase font-bold text-slate-400">Jouw Sterren</div>
                    <div class="text-base font-black text-amber-500 font-mono flex items-center justify-center gap-1">
                        <i class="fa-solid fa-star text-xs"></i>
                        <span>{{ number_format($character->stars ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
                <a href="{{ route('garage.index') }}" 
                   class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs uppercase tracking-wider transition shadow-xs flex items-center gap-2">
                    <i class="fa-solid fa-car text-xs"></i>
                    <span>Sterren Voertuigen</span>
                </a>
            </div>
        </div>
    </div>

    <!-- PERKS GRID -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        @foreach($perks as $key => $perk)
        @php
            $isOwned = $character->hasPerk($key);
            $expiresAt = $character->getPerkExpiresAt($key);
            $secondsLeft = $character->getPerkRemainingSeconds($key);
            $hasEnough = ($character->stars ?? 0) >= $perk['cost_stars'];
        @endphp
        <div class="bg-white dark:bg-slate-900 border {{ $isOwned ? 'border-emerald-400/80 dark:border-emerald-500/80 ring-2 ring-emerald-400/20' : 'border-slate-200 dark:border-slate-800' }} rounded-2xl p-6 shadow-xs flex flex-col justify-between gap-5 relative overflow-hidden transition hover:shadow-md">
            
            @if($isOwned)
                <div class="absolute -right-12 top-6 bg-emerald-500 text-slate-950 font-black text-[10px] uppercase tracking-wider py-1 px-12 rotate-45 shadow-xs text-center pointer-events-none">
                    24U Actief ✓
                </div>
            @endif

            <div class="space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="w-14 h-14 rounded-2xl {{ $isOwned ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-400/30' : 'bg-amber-500/10 text-amber-500 border border-amber-400/30' }} flex items-center justify-center text-2xl shrink-0">
                        <i class="fa-solid {{ $perk['icon'] }}"></i>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-700/60 font-black text-xs font-mono">
                            <i class="fa-solid fa-star text-[10px]"></i>
                            <span>{{ $perk['cost_stars'] }} Sterren</span>
                        </span>
                    </div>
                </div>

                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">{{ $perk['tagline'] }}</span>
                    <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight mt-0.5">{{ $perk['name'] }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                        {{ $perk['description'] }}
                    </p>
                </div>

                <!-- BENEFIT HIGHLIGHT -->
                <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 rounded-xl p-3 flex items-center gap-2.5">
                    <i class="fa-solid fa-circle-check text-emerald-500 text-sm shrink-0"></i>
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $perk['benefit'] }}</span>
                </div>

                @if($isOwned)
                    <!-- COUNTDOWN TIMER BOX -->
                    <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl p-3 text-xs space-y-1"
                         x-data="{
                             left: {{ $secondsLeft }},
                             format() {
                                 if (this.left <= 0) return 'Zojuist verlopen (ververs pagina)';
                                 const total = Math.floor(this.left);
                                 const h = Math.floor(total / 3600);
                                 const m = Math.floor((total % 3600) / 60);
                                 const s = Math.floor(total % 60);
                                 return `${h}u ${m < 10 ? '0' : ''}${m}m ${s < 10 ? '0' : ''}${s}s`;
                             },
                             init() {
                                 setInterval(() => { if (this.left > 0) this.left--; }, 1000);
                             }
                         }">
                        <div class="flex items-center justify-between text-emerald-800 dark:text-emerald-300 font-bold">
                            <span class="flex items-center gap-1.5"><i class="fa-solid fa-clock"></i> Resterende actieve tijd:</span>
                            <span class="font-black font-mono" x-text="format()"></span>
                        </div>
                        @if($expiresAt)
                            <p class="text-[10px] text-emerald-600 dark:text-emerald-400">
                                Loopt af op {{ $expiresAt->format('d-m-Y \o\m H:i') }} uur.
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <!-- ACTION BUTTON -->
            <div class="pt-2">
                @if($isOwned)
                    <div class="w-full py-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-black text-xs text-center border border-emerald-200 dark:border-emerald-800/60 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-circle-check text-xs"></i>
                        <span>24 Uur Actief Ingeschakeld</span>
                    </div>
                @else
                    <form action="{{ route('perks.buy', $key) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                @if(! $hasEnough) disabled @endif
                                class="w-full py-3 px-4 rounded-xl {{ $hasEnough ? 'bg-amber-500 hover:bg-amber-600 active:scale-95 text-slate-950 font-black cursor-pointer shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 font-bold cursor-not-allowed' }} text-xs uppercase tracking-wider transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-bolt"></i>
                            @if($hasEnough)
                                <span>Activeer 24 Uur ({{ $perk['cost_stars'] }} ⭐)</span>
                            @else
                                <span>Niet genoeg Sterren ({{ $character->stars ?? 0 }}/{{ $perk['cost_stars'] }} ⭐)</span>
                            @endif
                        </button>
                    </form>
                @endif
            </div>

        </div>
        @endforeach
    </div>

    <!-- INFO CARD OVER STERREN -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="space-y-1">
                <h4 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-circle-question text-amber-500"></i>
                    <span>Hoe kom je aan Sterren ⭐?</span>
                </h4>
                <p class="text-xs text-slate-600 dark:text-slate-400 max-w-2xl leading-relaxed">
                    Cooldowns en de isoleercel kun je nu gratis skippen via een sponsor advertentie. Hierdoor zijn Sterren een pure verzamel- en prestige-valuta geworden! Verdien dagelijks <strong>5 gratis Sterren</strong> via de sponsor-ads in de header, behaal prestaties, heists en open speciale kisten.
                </p>
            </div>
            <a href="{{ route('sponsor.index') }}" 
               onclick="if(window.tbsOpenSponsorAd){ window.tbsOpenSponsorAd('stars'); return false; }"
               class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs uppercase tracking-wider transition shadow-sm flex items-center gap-2 shrink-0 cursor-pointer">
                <i class="fa-solid fa-star"></i>
                <span>Claim Dagelijkse Sterren</span>
            </a>
        </div>
    </div>

</div>
@endsection
