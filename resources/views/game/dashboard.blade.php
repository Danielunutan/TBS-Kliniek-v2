@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

    <!-- Main Content (Left) -->
    <div class="xl:col-span-8 space-y-6">
        
        <!-- Open Beta Notice Card -->
        <div class="bg-white border border-purple-200/90 rounded-2xl p-4 sm:p-5 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-purple-50 border border-purple-200 text-purple-600 flex items-center justify-center text-lg shrink-0 mt-0.5 sm:mt-0 shadow-2xs">
                    <i class="fa-solid fa-flask-vial"></i>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-100 text-purple-800 border border-purple-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-purple-600 animate-pulse"></span>
                            Open Beta Testfase
                        </span>
                        <span class="text-xs font-black text-slate-900">TBS Kliniek de Game is momenteel in Open Beta!</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1 font-medium leading-relaxed">
                        We bouwen elke dag aan nieuwe gameplay en updates. Mocht je fouten, bugs of onduidelijkheden tegenkomen, of heb je goede feedback en suggesties? Maak eenvoudig een ticket aan onder <strong>Helptool</strong> of raadpleeg de Kennisbank.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto shrink-0">
                <a href="{{ route('help.tickets.create') }}" 
                   class="flex-1 sm:flex-none px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-extrabold text-xs transition shadow-xs cursor-pointer inline-flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-plus text-[10px]"></i>
                    <span>Ticket Aanmaken</span>
                </a>
                <a href="{{ route('help.tickets.index') }}" 
                   class="flex-1 sm:flex-none px-3.5 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 font-bold text-xs transition inline-flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-headset text-slate-500"></i>
                    <span>Helpdesk</span>
                </a>
            </div>
        </div>

        <!-- Roadmap & Updates Teaser Card (Clean Light Styling) -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-purple-200/90 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-purple-50 border border-purple-200 text-purple-600 flex items-center justify-center text-lg shrink-0 mt-0.5 sm:mt-0 shadow-2xs">
                    <i class="fa-solid fa-compass"></i>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-100 text-purple-800 border border-purple-200">
                            10 Grote Updates
                        </span>
                        <span class="text-xs font-black text-slate-900">Toekomst van de Kliniek: Stem mee op nieuwe functies!</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1 font-medium leading-relaxed">
                        Van de <strong>Medicijnkluis Gang Heist</strong> en <strong>Armpjedruk Arena</strong> tot het <strong>Behandelaarsgesprek</strong> en de <strong>Peukeneconomie</strong>.
                    </p>
                </div>
            </div>

            <a href="{{ route('roadmap.index') }}" 
               class="w-full sm:w-auto px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 active:scale-95 text-white font-extrabold text-xs transition shadow-xs inline-flex items-center justify-center gap-2 shrink-0">
                <i class="fa-solid fa-thumbs-up text-amber-400 text-xs"></i>
                <span>Bekijk & Stem Nu</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <!-- Welcome Hero Card -->
        <div class="bg-white dark:bg-slate-900/90 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden relative">
            <!-- Optional subtle pattern background -->
            <div class="absolute inset-0 bg-slate-50/50 dark:bg-transparent"></div>
            
            <div class="relative p-6 sm:p-8 flex flex-col sm:flex-row gap-6 items-center sm:items-start">
                <!-- Large Avatar with level -->
                <div class="relative shrink-0">
                    <img src="{{ $character->avatar_url }}" alt="{{ $character->name }}" class="w-32 h-32 rounded-xl object-cover shadow-md border-4 border-white dark:border-slate-800 bg-slate-100 dark:bg-slate-800">
                    <div class="absolute -bottom-3 -right-3 bg-white dark:bg-slate-800 p-1 rounded-lg shadow-sm border border-slate-100 dark:border-slate-700">
                        <div class="bg-slate-900 text-white font-bold text-xs px-3 py-1.5 rounded-lg flex flex-col items-center leading-none">
                            <span class="text-[9px] text-amber-400 font-bold uppercase">Level</span>
                            <span class="text-lg">{{ $character->level }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex-1 w-full text-center sm:text-left">
                    <h1 class="text-3xl sm:text-4xl font-bold text-slate-900 dark:text-white tracking-tight mb-2">Welkom terug, {{ $character->name }}!</h1>
                    <p class="text-slate-500 dark:text-slate-400 font-medium mb-6">De kliniek is zwaar, maar jij bent zwaarder.</p>

                    <!-- XP Bar (Sleek full-width without awkward clock circle) -->
                    @php
                        $lvlService = app(\App\Services\LevelService::class);
                        $reqXp = $lvlService->getXpRequiredForNextLevel($character->level);
                        $pctXp = min(100, max(0, round(($character->xp / max(1, $reqXp)) * 100)));
                    @endphp
                    <div class="space-y-1.5 mb-6">
                        <div class="flex justify-between items-center text-xs">
                            <span class="font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-solid fa-shield-halved text-purple-600 dark:text-purple-400 text-[11px]"></i>
                                <span>Level {{ $character->level }} Voortgang</span>
                            </span>
                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200">
                                {{ number_format($character->xp, 0, ',', '.') }} / {{ number_format($reqXp, 0, ',', '.') }} XP
                            </span>
                        </div>
                        <div class="h-2.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden border border-slate-200 dark:border-slate-700">
                            <div class="h-full bg-purple-600 rounded-full transition-all duration-500" style="width: {{ $pctXp }}%"></div>
                        </div>
                    </div>

                    <!-- Saldo & Gang Info Directly Inside Hero Card -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-left">
                        <!-- Contant Saldo -->
                        <a href="{{ route('bank.index') }}" class="bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl p-3 hover:border-purple-400 dark:hover:border-purple-500 transition group block shadow-2xs">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-6 h-6 rounded-md bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-[10px]">
                                    <i class="fa-solid fa-coins"></i>
                                </div>
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-400">Contant</span>
                            </div>
                            <div class="text-sm sm:text-base font-black text-slate-900 dark:text-white truncate">
                                €{{ number_format($character->cash, 0, ',', '.') }}
                            </div>
                        </a>

                        <!-- Bank Saldo -->
                        <a href="{{ route('bank.index') }}" class="bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl p-3 hover:border-indigo-400 dark:hover:border-indigo-500 transition group block shadow-2xs">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-6 h-6 rounded-md bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-[10px]">
                                    <i class="fa-solid fa-building-columns"></i>
                                </div>
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-400">Bank</span>
                            </div>
                            <div class="text-sm sm:text-base font-black text-slate-900 dark:text-white truncate">
                                €{{ number_format($character->bank, 0, ',', '.') }}
                            </div>
                        </a>

                        <!-- Bende / Gang Info -->
                        <a href="{{ route('gangs.index') }}" class="bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl p-3 hover:border-purple-400 dark:hover:border-purple-500 transition group block shadow-2xs">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-6 h-6 rounded-md bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-[10px]">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-400">Bende</span>
                            </div>
                            <div class="text-sm font-black text-purple-700 dark:text-purple-300 truncate">
                                {{ $character->gang ? $character->gang->name : 'Geen Bende' }}
                            </div>
                        </a>

                        <!-- TBS Uitkering -->
                        <div class="bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl p-3 shadow-2xs">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-6 h-6 rounded-md bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-[10px]">
                                    <i class="fa-solid fa-hand-holding-dollar"></i>
                                </div>
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-400">Uitkering</span>
                            </div>
                            <div class="text-sm sm:text-base font-black text-emerald-600 dark:text-emerald-400 truncate">
                                €1.000 <span class="text-[10px] font-bold text-slate-400">/ 15m</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AFDELINGSLOTERIJ JACKPOT PROMO WIDGET (Crisp Light & Dark Mode, Geen Gradients) -->
        @if(isset($lotteryRound))
        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-5 sm:p-6 text-slate-900 dark:text-white shadow-xs relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="space-y-2 max-w-xl">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-700/60">
                        <i class="fa-solid fa-crown text-amber-500"></i> Afdelingsloterij Ronde #{{ $lotteryRound->round_number }}
                    </span>
                    <span class="text-[10px] font-bold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full border border-slate-200 dark:border-slate-700">
                        1x per 14 dagen
                    </span>
                </div>
                <h2 class="text-lg sm:text-xl font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                    Grijp de Jackpot: <span class="text-amber-600 dark:text-amber-400 font-mono font-black">€{{ number_format($lotteryRound->jackpot, 0, ',', '.') }}</span>
                </h2>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-medium">
                    Iedere cent opnamebelasting van de bank stroomt rechtstreeks in deze pot. Koop loten met contant geld of sterren en maak kans op de volledige pot bij de 14-daagse trekking!
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-3 shrink-0 self-stretch md:self-center">
                @php
                    $dashSecondsLeft = max(0, (int) round(now()->diffInSeconds($lotteryRound->draws_at, false)));
                @endphp
                <div class="w-full sm:w-auto p-3 rounded-xl bg-amber-50/70 dark:bg-slate-800 border border-amber-200 dark:border-slate-700 text-center shrink-0"
                     x-data="{
                         left: {{ $dashSecondsLeft }},
                         format() {
                             if (this.left <= 0) return 'Trekking!';
                             const t = Math.floor(this.left);
                             const d = Math.floor(t / 86400);
                             const h = Math.floor((t % 86400) / 3600);
                             const m = Math.floor((t % 3600) / 60);
                             const s = Math.floor(t % 60);
                             return d > 0 ? `${d}d ${h}u ${m}m ${s < 10 ? '0' : ''}${s}s` : `${h}u ${m}m ${s < 10 ? '0' : ''}${s}s`;
                         },
                         init() { setInterval(() => { if (this.left > 0) this.left--; }, 1000); }
                     }">
                    <span class="text-[9px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Tijd tot trekking</span>
                    <span class="text-xs font-mono font-black text-slate-900 dark:text-amber-400 mt-0.5 block" x-text="format()">
                        {{ now()->diffForHumans($lotteryRound->draws_at, true) }}
                    </span>
                </div>

                <a href="{{ route('lottery.index') }}" 
                   class="w-full sm:w-auto px-5 py-3 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-black text-xs uppercase tracking-wider transition text-center shadow-xs flex items-center justify-center gap-2 shrink-0">
                    <i class="fa-solid fa-ticket"></i>
                    <span>Koop een Lot</span>
                </a>
            </div>
        </div>
        @endif

        <!-- PERSOONLIJKE REFERRAL & GRATIS VIP CARD (In lijn met de website styling) -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 shadow-xs border border-purple-200/90 relative overflow-hidden" 
             x-data="{ 
                 copied: false,
                 copyLink() {
                     navigator.clipboard.writeText('{{ $referralStats['link'] }}');
                     this.copied = true;
                     setTimeout(() => this.copied = false, 2500);
                 }
             }">
            <!-- Top Section: Badges, Title & Stats Summary -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-5 border-b border-slate-100">
                <div class="flex items-start gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-purple-50 border border-purple-200 text-purple-600 flex items-center justify-center text-xl shrink-0 mt-0.5 shadow-2xs">
                        <i class="fa-solid fa-crown text-amber-500"></i>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200">
                                <i class="fa-solid fa-crown text-amber-500 text-[10px]"></i>
                                Gratis VIP Actie
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                3 Spelers = 1 Maand VIP
                            </span>
                        </div>
                        <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">
                            Nodig Vrienden Uit & Krijg Gratis VIP
                        </h2>
                        <p class="text-xs text-slate-500 mt-1 font-medium leading-relaxed max-w-xl">
                            Deel jouw unieke link met je vrienden of Discord-connecties. Voor elke <strong class="text-slate-900 font-extrabold">3 geregistreerde spelers</strong> die jij aanlevert, activeert het systeem automatisch <strong class="text-purple-700 font-extrabold">1 maand gratis VIP</strong> op jouw account!
                        </p>
                    </div>
                </div>

                <!-- Stats summary badge -->
                <div class="flex items-center gap-4 shrink-0 self-start md:self-center p-3 rounded-xl bg-slate-50 border border-slate-200 shadow-2xs">
                    <div class="text-center px-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block mb-0.5">Aangebracht</span>
                        <span class="text-2xl font-black text-slate-900 leading-none">{{ $referralStats['total'] }}</span>
                    </div>
                    <div class="w-px h-8 bg-slate-200"></div>
                    <div class="text-center px-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-amber-600 block mb-0.5">VIP Maanden</span>
                        <span class="text-2xl font-black text-amber-600 leading-none">{{ $referralStats['rewarded_months'] }}</span>
                    </div>
                </div>
            </div>

            @if(! ($referralStats['has_username'] ?? true))
            <!-- Notice when username is not configured yet -->
            <div class="mt-4 p-4 rounded-xl bg-amber-50 border border-amber-300 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3 text-xs text-amber-900 font-bold">
                    <i class="fa-solid fa-triangle-exclamation text-amber-500 text-lg shrink-0"></i>
                    <span>Je hebt nog geen unieke @gebruikersnaam ingesteld! Stel eerst je gebruikersnaam in via Account Instellingen zodat jouw link jouw eigen naam bevat.</span>
                </div>
                <a href="{{ route('settings.account') }}" class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs transition shrink-0 text-center shadow-xs">
                    Stel @gebruikersnaam In
                </a>
            </div>
            @endif

            <!-- Progress Meter Towards Next VIP Month -->
            <div class="pt-4 pb-4 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-600 flex items-center gap-1.5 font-bold">
                        <i class="fa-solid fa-gift text-purple-600"></i> Voortgang naar volgende gratis maand:
                    </span>
                    <span class="font-black font-mono text-xs text-slate-900">
                        {{ $referralStats['progress'] }} / 3 Spelers 
                        @if($referralStats['needed'] === 1)
                            <span class="text-amber-600 font-extrabold">(Nog 1 voor VIP! )</span>
                        @else
                            <span class="text-slate-400 font-normal">(Nog {{ $referralStats['needed'] }} nodig)</span>
                        @endif
                    </span>
                </div>

                <!-- 3-Segment Visual Tracker -->
                <div class="grid grid-cols-3 gap-2">
                    <div class="h-3 rounded-full transition-all duration-300 flex items-center justify-center {{ $referralStats['progress'] >= 1 ? 'bg-emerald-500 shadow-2xs' : 'bg-slate-100 border border-slate-200' }}">
                        @if($referralStats['progress'] >= 1)
                            <i class="fa-solid fa-check text-[9px] text-white font-black"></i>
                        @endif
                    </div>
                    <div class="h-3 rounded-full transition-all duration-300 flex items-center justify-center {{ $referralStats['progress'] >= 2 ? 'bg-emerald-500 shadow-2xs' : 'bg-slate-100 border border-slate-200' }}">
                        @if($referralStats['progress'] >= 2)
                            <i class="fa-solid fa-check text-[9px] text-white font-black"></i>
                        @endif
                    </div>
                    <div class="h-3 rounded-full transition-all duration-300 flex items-center justify-center {{ $referralStats['progress'] >= 3 ? 'bg-amber-400 shadow-2xs' : 'bg-slate-100 border border-slate-200' }}">
                        <i class="fa-solid fa-crown text-[9px] {{ $referralStats['progress'] >= 3 ? 'text-slate-950 font-black' : 'text-slate-300' }}"></i>
                    </div>
                </div>
            </div>

            <!-- Referral Link Copy Box -->
            <div class="pt-1">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block mb-1.5">
                    Jouw Persoonlijke Aanmeldlink (met @<span>{{ $referralStats['username'] }}</span>)
                </span>
                <div class="flex flex-col sm:flex-row items-stretch gap-2">
                    <div class="relative flex-1">
                        <input type="text" readonly value="{{ $referralStats['link'] }}" 
                               @click="$el.select()"
                               class="w-full rounded-xl px-4 py-2.5 text-xs sm:text-sm font-mono font-bold text-slate-800 bg-slate-50 border border-slate-200 hover:border-slate-300 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none select-all transition shadow-2xs">
                    </div>
                    <button type="button" 
                            @click="copyLink()"
                            class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 active:scale-95 text-white font-extrabold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer shadow-xs flex items-center justify-center gap-2 shrink-0">
                        <template x-if="!copied">
                            <span class="flex items-center gap-2">
                                <i class="fa-solid fa-copy text-amber-400"></i>
                                <span>Kopieer Link</span>
                            </span>
                        </template>
                        <template x-if="copied">
                            <span class="flex items-center gap-2 text-emerald-400">
                                <i class="fa-solid fa-check font-black"></i>
                                <span>Gekopieerd!</span>
                            </span>
                        </template>
                    </button>
                </div>
            </div>
        </div>

        <!-- Rad van Fortuin Daily Notification Banner -->
        @if($character->canDailySpin())
            <div class="bg-white border-2 border-purple-200 rounded-xl p-4 shadow-xs text-slate-900 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-purple-700 text-white flex items-center justify-center shrink-0 text-xl shadow-xs">
                        <i class="fa-solid fa-dharmachakra"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-black text-slate-900">Rad van Fortuin & Dagelijkse Beloning!</h3>
                            <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-purple-100 text-purple-800 border border-purple-200">Nu Beschikbaar</span>
                        </div>
                        <p class="text-xs text-slate-600 mt-0.5">
                            Draai gratis aan het TBS rad voor kluiscodes, maskers, koffers of cash en behoud je 7-daagse streak!
                        </p>
                    </div>
                </div>
                <a href="{{ route('daily.index') }}" class="shrink-0 px-4 py-2 rounded-lg bg-purple-700 hover:bg-purple-800 text-white font-black text-xs uppercase tracking-wider shadow-xs transition">
                    <i class="fa-solid fa-play mr-1"></i> Nu Draaien
                </a>
            </div>
        @endif

        <!-- TBS Uitkering Banner for inmates without a gang -->
        @if($allowanceDetails['eligible'] ?? false)
            <div class="bg-emerald-50/60 border border-emerald-200 rounded-xl p-4 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <i class="fa-solid fa-hand-holding-dollar text-xl"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-extrabold text-slate-900">TBS Patiënten Uitkering</h3>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">Iedereen zonder bende</span>
                        </div>
                        <p class="text-xs text-slate-600 mt-0.5">
                            Omdat je niet in een bende zit, ontvang je automatisch <strong class="text-emerald-700 font-extrabold">€1.000 per kwartier (15 min)</strong> direct op je <a href="{{ route('bank.index') }}" class="underline font-bold hover:text-emerald-800">bankrekening</a>!
                        </p>
                    </div>
                </div>
                <div class="shrink-0 flex items-center gap-2 bg-white px-3.5 py-2 rounded-lg border border-emerald-200 shadow-xs self-end sm:self-center"
                     x-data="{ 
                         secondsLeft: {{ $allowanceDetails['seconds_until_next'] ?? 900 }},
                         init() {
                             setInterval(() => {
                                 if (this.secondsLeft > 0) {
                                     this.secondsLeft--;
                                 } else {
                                     this.secondsLeft = 900;
                                 }
                             }, 1000);
                         },
                         formatTime() {
                             const m = Math.floor(this.secondsLeft / 60);
                             const s = this.secondsLeft % 60;
                             return `${m}m ${s < 10 ? '0' : ''}${s}s`;
                         }
                     }">
                    <i class="fa-solid fa-hourglass-half text-emerald-600 text-xs"></i>
                    <span class="text-xs text-slate-500 font-medium">Volgende storting:</span>
                    <span class="text-xs font-extrabold text-emerald-700 font-mono" x-text="formatTime()">{{ $allowanceDetails['next_human'] ?? '15m 00s' }}</span>
                </div>
            </div>
        @endif

        <!-- Character Attributes -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Karakter Eigenschappen & Actieve Stats</h3>
                </div>
                <a href="{{ route('training.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 transition">
                    <i class="fa-solid fa-dumbbell text-xs"></i> Trainen in Sportschool &rarr;
                </a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 text-center">
                @php
                    $attrList = [
                        [
                            'label' => 'Kracht',
                            'base' => $character->strength ?? 10,
                            'bonus' => $character->getItemBonus('strength'),
                            'effective' => $character->getEffectiveStrength(),
                            'icon' => 'fa-hand-fist',
                            'color' => 'text-rose-500',
                            'bg' => 'hover:bg-rose-50/40',
                            'desc' => 'Gevechten & Slagkracht',
                        ],
                        [
                            'label' => 'Verdediging',
                            'base' => $character->defense ?? 10,
                            'bonus' => $character->getItemBonus('defense'),
                            'effective' => $character->getEffectiveDefense(),
                            'icon' => 'fa-shield-halved',
                            'color' => 'text-indigo-500',
                            'bg' => 'hover:bg-indigo-50/40',
                            'desc' => 'Weerstand & Ontwijken',
                        ],
                        [
                            'label' => 'Conditie',
                            'base' => $character->stamina ?? 10,
                            'bonus' => $character->getItemBonus('stamina'),
                            'effective' => $character->getEffectiveStamina(),
                            'icon' => 'fa-person-running',
                            'color' => 'text-emerald-500',
                            'bg' => 'hover:bg-emerald-50/40',
                            'desc' => 'Vluchten & Diefstal',
                        ],
                        [
                            'label' => 'Intelligentie',
                            'base' => $character->intelligence ?? 10,
                            'bonus' => $character->getItemBonus('intelligence'),
                            'effective' => $character->getEffectiveIntelligence(),
                            'icon' => 'fa-brain',
                            'color' => 'text-purple-500',
                            'bg' => 'hover:bg-purple-50/40',
                            'desc' => 'Missies, Hacks & Kluizen',
                        ],
                        [
                            'label' => 'Charisma',
                            'base' => $character->charisma ?? 10,
                            'bonus' => $character->getItemBonus('charisma'),
                            'effective' => $character->getEffectiveCharisma(),
                            'icon' => 'fa-face-smile-wink',
                            'color' => 'text-amber-500',
                            'bg' => 'hover:bg-amber-50/40',
                            'desc' => 'Winkelkorting & Hossels',
                        ],
                        [
                            'label' => 'Discipline',
                            'base' => $character->discipline ?? 10,
                            'bonus' => $character->getItemBonus('discipline'),
                            'effective' => $character->getEffectiveDiscipline(),
                            'icon' => 'fa-scale-balanced',
                            'color' => 'text-sky-500',
                            'bg' => 'hover:bg-sky-50/40',
                            'desc' => 'Strafkorting & Focus',
                        ],
                    ];
                @endphp
                @foreach($attrList as $attr)
                    <div class="flex flex-col items-center justify-between p-3 rounded-lg border border-slate-100/80 bg-slate-50/40 {{ $attr['bg'] }} transition group">
                        <i class="fa-solid {{ $attr['icon'] }} {{ $attr['color'] }} text-xl mb-1 group-hover:scale-110 transition-transform"></i>
                        <span class="text-xs font-bold text-slate-500">{{ $attr['label'] }}</span>
                        <div class="flex items-center gap-1 my-0.5">
                            <span class="text-lg font-black text-slate-900">{{ $attr['base'] }}</span>
                            @if($attr['bonus'] > 0)
                                <span class="text-[10px] font-extrabold text-emerald-600 bg-emerald-50 px-1 py-0.5 rounded border border-emerald-200" title="Item bonus in rugzak">+{{ $attr['bonus'] }}</span>
                            @endif
                        </div>
                        <span class="text-[9px] font-semibold text-slate-400 block leading-tight">{{ $attr['desc'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Recente activiteiten -->
        <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </span>
                    <h3 class="text-sm font-extrabold text-slate-900">Recente activiteiten</h3>
                </div>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentActivities as $activity)
                    <div class="p-4 hover:bg-slate-50/70 transition flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full {{ $activity['icon_bg'] }} flex items-center justify-center shrink-0 shadow-2xs">
                            <i class="fa-solid {{ $activity['icon'] }} text-xs"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-slate-800 truncate">{{ $activity['title'] }}</p>
                            <p class="text-xs font-bold {{ $activity['badge_color'] }} mt-0.5">{{ $activity['badge'] }}</p>
                        </div>
                        <div class="text-xs text-slate-400 font-semibold whitespace-nowrap shrink-0">
                            {{ \Carbon\Carbon::parse($activity['time'])->diffForHumans() }}
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-slate-400 font-medium">
                        <i class="fa-solid fa-clock-rotate-left text-2xl text-slate-300 mb-2 block"></i>
                        Nog geen recente activiteiten geregistreerd. Voer een missie, misdaad of training uit!
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Right Sidebar -->
    <div class="xl:col-span-4 space-y-6">

        <!-- Laatste Nieuws Widget (TBS Kliniek Nieuws) -->
        <div class="bg-white dark:bg-slate-900/90 rounded-xl shadow-xs border border-slate-200/80 dark:border-slate-800 overflow-hidden">
            <div class="px-5 py-3.5 bg-slate-50/70 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs font-black tracking-wide text-slate-900 dark:text-white">
                    <span class="w-5 h-5 rounded-md bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 flex items-center justify-center text-[10px] border border-purple-200 dark:border-purple-800">
                        <i class="fa-solid fa-newspaper"></i>
                    </span>
                    <span>Laatste nieuws</span>
                </div>
                <span class="text-[10px] font-bold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/50 px-2.5 py-0.5 rounded-full border border-purple-200 dark:border-purple-800">
                    Live Updates
                </span>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($latestNews as $newsItem)
                <a href="{{ route('news.show', $newsItem->slug) }}" class="p-3.5 px-5 hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition block group">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="text-sm shrink-0 group-hover:scale-110 transition-transform">{{ $newsItem->emoji }}</span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 group-hover:text-purple-700 dark:group-hover:text-purple-400 transition truncate">
                                {{ $newsItem->title }}
                            </span>
                        </div>
                        @if($newsItem->is_pinned)
                            <span class="text-[9px] font-black uppercase text-amber-800 dark:text-amber-300 bg-amber-100 dark:bg-amber-950/70 px-1.5 py-0.5 rounded-md border border-amber-300 dark:border-amber-700 shrink-0">Pin</span>
                        @endif
                    </div>
                    <div class="text-[10px] font-semibold text-slate-400 mt-1 pl-6">
                        {{ $newsItem->created_at->diffForHumans() }}
                    </div>
                </a>
                @empty
                <div class="p-5 text-center text-xs text-slate-400 font-medium">
                    Nog geen nieuwsberichten geplaatst.
                </div>
                @endforelse
            </div>

            <a href="{{ route('news.index') }}" class="block w-full text-center py-2.5 bg-slate-50/70 dark:bg-slate-800/60 hover:bg-slate-100 dark:hover:bg-slate-800/90 text-purple-700 dark:text-purple-300 hover:text-purple-900 dark:hover:text-purple-200 font-extrabold text-xs transition border-t border-slate-100 dark:border-slate-800 flex items-center justify-center gap-1.5">
                <span>Meer nieuws</span>
                <i class="fa-solid fa-angles-right text-[10px] text-purple-500"></i>
            </a>
        </div>
        
        <!-- Gang Card -->
        <div class="bg-white dark:bg-slate-900/90 rounded-xl shadow-xs border border-slate-200/80 dark:border-slate-800 overflow-hidden relative group">
            @if($character->gang)
                <div class="p-6 flex flex-col items-center text-center">
                    <div class="w-16 h-16 mb-3 rounded-2xl bg-slate-900 dark:bg-slate-800 text-amber-400 flex items-center justify-center text-2xl shadow-sm group-hover:scale-105 transition-transform">
                        <i class="fa-solid {{ $character->gang->emblem ?: 'fa-crown' }}"></i>
                    </div>
                    <div class="flex items-center gap-1.5 justify-center flex-wrap">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-0.5">{{ $character->gang->name }}</h3>
                        <span class="text-[10px] font-black uppercase px-1.5 py-0.5 rounded bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300">[{{ $character->gang->tag }}]</span>
                    </div>
                    <span class="text-xs font-semibold text-slate-400 mb-4">{{ ucfirst($character->gang_role ?: 'Lid') }}</span>
                    <div class="w-full border-t border-slate-100 dark:border-slate-800 pt-3 mb-4 space-y-2 text-xs font-bold">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 dark:text-slate-400">Leden:</span>
                            <span class="text-slate-900 dark:text-white">{{ $character->gang->members()->count() }} actief</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 dark:text-slate-400">Bendekas:</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-extrabold">€{{ number_format($character->gang->treasury, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <a href="{{ route('gangs.index') }}" class="block w-full text-center bg-slate-900 hover:bg-slate-800 dark:bg-purple-600 dark:hover:bg-purple-700 text-white font-bold py-2.5 rounded-xl transition text-xs shadow-xs">
                        Bekijk Mijn Bende <i class="fa-solid fa-chevron-right text-[10px] ml-1"></i>
                    </a>
                </div>
            @else
                <div class="p-6 flex flex-col items-center text-center">
                    <div class="w-16 h-16 mb-3 rounded-2xl bg-purple-50 dark:bg-slate-800 text-purple-600 dark:text-purple-400 border border-purple-100 dark:border-slate-700 flex items-center justify-center text-2xl shadow-2xs group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white mb-0.5">Maak Gang of word lid van een gang</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4 leading-relaxed">
                        Alleen sta je zwak in de TBS kliniek. Sluit je aan bij een factie of richt je eigen bende op om samen te heersen!
                    </p>
                    <div class="w-full border-t border-slate-100 dark:border-slate-800 pt-3 mb-4 flex justify-between items-center text-xs font-bold">
                        <span class="text-slate-500 dark:text-slate-400">Status:</span>
                        <span class="text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/50 px-2 py-0.5 rounded-full border border-amber-200 dark:border-amber-800">Geen Bende</span>
                    </div>
                    <a href="{{ route('gangs.index') }}" class="block w-full text-center bg-purple-600 hover:bg-purple-700 text-white font-bold py-2.5 rounded-xl transition text-xs shadow-xs">
                        Maak Gang of Word Lid <i class="fa-solid fa-chevron-right text-[10px] ml-1"></i>
                    </a>
                </div>
            @endif
        </div>

        <!-- Actieve Missies -->
        <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
            <div class="px-6 py-4.5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-sm font-bold border border-purple-100">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900">Actieve Missies</h3>
                        <p class="text-[11px] text-slate-400 font-medium">Voltooi opdrachten voor contant geld, respect en XP</p>
                    </div>
                </div>
                <a href="{{ route('missions.index') }}" class="text-xs font-bold text-purple-600 hover:text-purple-700 flex items-center gap-1">
                    <span>Bekijk alle ({{ \App\Models\Mission::count() }})</span>
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </a>
            </div>
            <div class="p-0">
                <div class="divide-y divide-slate-100">
                    @forelse($quickMissions as $m)
                        @php
                            $isUnlocked = $character->level >= $m->required_level;
                            $completedTimes = $completedMissionCounts[$m->id] ?? 0;
                            $isCompleted = $completedTimes > 0;
                            
                            $avatarMap = [
                                'kantine' => 'assets/characters/henkie.jpg',
                                'smokkel' => 'assets/characters/ricardo.jpg',
                                'gevecht' => 'assets/characters/barry.jpg',
                                'medicatie' => 'assets/characters/avatar_female_1.jpg',
                                'administratie' => 'assets/characters/avatar_female_2.jpg',
                                'beveiliging' => 'assets/characters/avatar_male_4.jpg',
                                'casino' => 'assets/characters/avatar_male_5.jpg',
                            ];
                            $missionImg = asset($avatarMap[$m->category] ?? 'assets/characters/avatar_male_1.jpg');
                        @endphp
                        <div class="p-4 sm:p-5 flex items-center justify-between gap-4 hover:bg-slate-50/80 transition {{ $isCompleted ? 'bg-emerald-50/20' : '' }}">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <img src="{{ $missionImg }}" alt="{{ $m->giver_name }}" class="w-12 h-12 rounded-xl object-cover shadow-2xs shrink-0 border border-slate-200">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 mb-0.5 flex-wrap">
                                        <h4 class="text-sm font-extrabold text-slate-900 truncate">{{ $m->title }}</h4>
                                        @if($isCompleted)
                                            <span class="bg-emerald-100 text-emerald-800 text-[10px] font-black px-2 py-0.5 rounded-md inline-flex items-center gap-1">
                                                <i class="fa-solid fa-check text-[9px]"></i> Voltooid ({{ $completedTimes }}x)
                                            </span>
                                        @elseif($isUnlocked)
                                            <span class="bg-indigo-50 text-indigo-700 text-[10px] font-black px-2 py-0.5 rounded-md border border-indigo-100">
                                                Beschikbaar
                                            </span>
                                        @else
                                            <span class="bg-slate-100 text-slate-600 text-[10px] font-black px-2 py-0.5 rounded-md inline-flex items-center gap-1">
                                                <i class="fa-solid fa-lock text-[9px]"></i> Level {{ $m->required_level }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 text-[11px] text-slate-500 font-medium flex-wrap">
                                        <span>{{ $m->giver_name }}</span>
                                        <span>&bull;</span>
                                        <span class="text-emerald-700 font-bold">€{{ number_format($m->min_cash_reward, 0, ',', '.') }}</span>
                                        <span>&bull;</span>
                                        <span class="text-purple-700 font-bold">+{{ $m->xp_reward }} XP</span>
                                    </div>
                                </div>
                            </div>
                            <div class="shrink-0">
                                <a href="{{ route('missions.index') }}" 
                                   class="text-xs font-black px-4 py-2 rounded-xl transition shadow-xs whitespace-nowrap inline-flex items-center gap-1.5 {{ $isCompleted ? 'bg-slate-100 hover:bg-slate-200 text-slate-700' : ($isUnlocked ? 'bg-purple-600 hover:bg-purple-700 text-white' : 'bg-slate-100 text-slate-400 cursor-not-allowed') }}">
                                    <span>{{ $isCompleted ? 'Opnieuw' : ($isUnlocked ? 'Start Missie' : 'Vergrendeld') }}</span>
                                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-slate-400">
                            Geen actieve missies gevonden.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
