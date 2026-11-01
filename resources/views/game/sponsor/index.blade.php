@extends('layouts.app')

@section('title', 'Sponsorkamer - Bekijk Ads voor Gratis VIP & Beloningen')

@section('content')
@php
    $isComaOrCodeBlauw = $character->isComatose() || $character->needsReviveSerum();
    $cannotWatchStandardAd = ($adsLeftToday <= 0) || $isComaOrCodeBlauw;
    $comaProgress = (int) ($character->ad_coma_progress ?? 0);
    $bulletsProgress = (int) ($character->ad_bullets_progress ?? 0);
    $vestProgress = (int) ($character->ad_vest_progress ?? 0);
@endphp
<div class="space-y-6">

    <!-- Top Hero Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xs relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 text-xs font-black px-3 py-1 rounded-full border border-purple-200/80 dark:border-purple-800/70 mb-2">
                    <i class="fa-solid fa-tv text-purple-600 dark:text-purple-400"></i> TBS Sponsorkamer & Gratis VIP
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Kijk & Verdien</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-xl leading-relaxed">
                    Help mee de TBS Kliniek gratis online te houden! Bekijk korte sponsor advertenties van 15 seconden en ontvang direct zakgeld, maskers, strafvermindering en ontgrendel bij 250 views <strong>24 uur gratis VIP status</strong>!
                </p>
            </div>

            <!-- Stats Chips -->
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <div class="px-3.5 py-2 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-xs font-bold text-slate-700 dark:text-slate-200 shadow-xs">
                    <span class="text-slate-400 dark:text-slate-400 block text-[10px] uppercase tracking-wider">Vandaag Bekeken</span>
                    <span class="text-slate-900 dark:text-white font-extrabold text-sm">{{ $adsToday }} / 500</span>
                </div>
                <div class="px-3.5 py-2 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 text-xs font-bold text-purple-700 dark:text-purple-300 shadow-xs">
                    <span class="text-purple-700/80 dark:text-purple-400 block text-[10px] uppercase tracking-wider">Totaal Bekeken</span>
                    <span class="text-purple-900 dark:text-purple-200 font-black text-sm">{{ $totalAds }} ads</span>
                </div>
            </div>
        </div>
    </div>

    @if($isComaOrCodeBlauw)
    <!-- Urgent IC Coma / Code Blauw Reanimatie Banner -->
    <div class="bg-emerald-50/90 dark:bg-emerald-950/40 border-2 border-emerald-500 dark:border-emerald-600 rounded-2xl p-5 sm:p-6 shadow-md flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-2 flex-1">
            <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-emerald-600 text-white text-[10px] font-black uppercase tracking-wider">
                <i class="fa-solid fa-heart-pulse animate-pulse"></i> Spoed IC Reanimatie Actief
            </div>
            <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white">
                Je ligt momenteel op de Intensive Care! Ontwaak gratis via 3 Sponsor Ads
            </h2>
            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                Zolang je in coma of Code Blauw ligt zijn reguliere beloningen gepauzeerd. Bekijk 3 korte sponsor-ads om direct te ontwaken uit het coma!
            </p>
            <div class="max-w-xs pt-1">
                <div class="flex items-center justify-between text-[11px] font-bold mb-1">
                    <span class="text-slate-600 dark:text-slate-300">Reanimatie Voortgang:</span>
                    <span class="text-emerald-700 dark:text-emerald-400 font-extrabold">{{ $comaProgress }} / 3 Ads</span>
                </div>
                <div class="w-full bg-white dark:bg-slate-800 rounded-full h-2 overflow-hidden border border-emerald-200 dark:border-emerald-800">
                    <div class="bg-emerald-600 h-full rounded-full transition-all duration-300" style="width: {{ min(100, ($comaProgress / 3) * 100) }}%;"></div>
                </div>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2 shrink-0 w-full md:w-auto">
            <button type="button"
                    @disabled($adsLeftToday <= 0)
                    onclick="window.tbsOpenSponsorAd('coma_revive', () => { window.location.reload(); })"
                    class="w-full sm:w-auto min-h-11 py-2.5 px-5 rounded-xl bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white font-black text-xs shadow-sm transition-all duration-150 active:scale-95 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fa-solid fa-syringe text-xs"></i>
                <span>Kijk Ad voor Ontwaken ({{ $comaProgress + 1 }}/3{{ $comaProgress === 2 ? ' - Claim!' : '' }})</span>
            </button>
        </div>
    </div>
    @endif

    <!-- VIP MILESTONE PROGRESS BAR (250 Ads = 24h VIP) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs relative overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-300 dark:border-amber-700/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl shrink-0 shadow-2xs">
                    <i class="fa-solid fa-crown text-amber-500"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        <span>24 Uur Gratis VIP Voortgang</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70">
                            {{ $totalAds % 250 }} / 250 Ads
                        </span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Bekijk in totaal 250 advertenties en ontvang automatisch een <strong>volledige 24-uurs VIP status</strong> (incl. 5 VIP punten/uur & extra spins)!
                    </p>
                </div>
            </div>

            <div class="text-right shrink-0">
                <span class="text-xs font-extrabold text-purple-700 dark:text-purple-400">{{ $vipProgress }}% Voltooid</span>
                <span class="text-[11px] text-slate-400 block">Nog {{ $adsUntilVip }} ads tot 24h VIP</span>
            </div>
        </div>

        <!-- Progress Tracker Bar -->
        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-3.5 p-0.5 border border-slate-200/80 dark:border-slate-700 overflow-hidden">
            <div class="bg-purple-600 h-full rounded-full transition-all duration-500 shadow-xs" 
                 style="width: {{ $vipProgress }}%;"></div>
        </div>

        @if($character->isVip())
        <div class="mt-3 text-xs text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/70 rounded-xl px-3 py-2 flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400"></i>
            <span>Jouw VIP status is momenteel actief tot <strong>{{ $character->vip_expires_at?->format('d-m-Y H:i') ?? 'Onbeperkt' }}</strong>! Extra 250 ads verlengen jouw VIP met +24 uur.</span>
        </div>
        @endif
    </div>

    <!-- DAILY SPONSOR REWARDS (1x PER DAG) -->
    <div class="space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
            <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2">
                <i class="fa-solid fa-calendar-check text-purple-600 dark:text-purple-400"></i> Exclusieve Dagelijkse Beloningen &amp; Isoleercel Noodvrijlating:
            </h2>
            <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">Dagelijkse boosts resetten om 00:00 uur &bull; Vrijlating altijd direct beschikbaar</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            <!-- Daily Card 1: 5 Sterren (2 Ads) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700/80 rounded-2xl p-5 shadow-xs flex flex-col justify-between transition-all duration-200 group relative overflow-hidden">
                <div class="relative z-10">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-200/80 dark:border-amber-800/70 flex items-center justify-center text-xl mb-3 group-hover:scale-105 transition shadow-2xs">
                        <i class="fa-solid fa-star text-amber-500"></i>
                    </div>
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/70 px-2 py-0.5 rounded-full">1x per dag • 2 Ads</span>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white group-hover:text-amber-600 transition">5 Sterren ⭐</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Spaar dagelijks 5 gratis Sterren bij elkaar door slechts 2 sponsor video's te bekijken.
                    </p>

                    <!-- Voortgangsbalk (0/2, 1/2, 2/2) -->
                    <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between text-[11px] font-bold mb-1">
                            <span class="text-slate-500 dark:text-slate-400">Voortgang:</span>
                            <span class="text-amber-600 dark:text-amber-400 font-extrabold">
                                {{ !empty($adDailyData['stars_claimed']) ? '2 / 2' : ($adDailyData['stars_progress'] ?? 0).' / 2' }} Ads
                            </span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden border border-slate-200 dark:border-slate-700">
                            <div class="bg-gradient-to-r from-amber-400 to-yellow-500 h-full rounded-full transition-all duration-300" 
                                 style="width: {{ !empty($adDailyData['stars_claimed']) ? 100 : min(100, (($adDailyData['stars_progress'] ?? 0) / 2) * 100) }}%;"></div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 relative z-10">
                    @if(!empty($adDailyData['stars_claimed']))
                        <button type="button" disabled 
                                class="w-full min-h-11 py-2 px-3 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 font-black text-xs shadow-xs transition flex items-center justify-center gap-1.5 cursor-not-allowed text-center leading-tight">
                            <i class="fa-solid fa-check text-xs text-emerald-500"></i>
                            <span>Vandaag Al Geclaimd</span>
                        </button>
                    @else
                        <button type="button" @disabled($cannotWatchStandardAd) onclick="window.tbsOpenSponsorAd('daily_stars')" class="btn-star-gold w-full min-h-11 py-2 px-3 rounded-xl font-black text-xs shadow-xs transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer text-center leading-tight disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fa-solid fa-star text-xs"></i>
                            <span>Kijk Ad voor 5 Sterren ({{ ($adDailyData['stars_progress'] ?? 0) + 1 }}/2{{ ($adDailyData['stars_progress'] ?? 0) === 1 ? ' - Claim!' : '' }})</span>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Daily Card 2: € 50.000 Cash (3 Ads) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700/80 rounded-2xl p-5 shadow-xs flex flex-col justify-between transition-all duration-200 group relative overflow-hidden">
                <div class="relative z-10">
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/70 flex items-center justify-center text-xl mb-3 group-hover:scale-105 transition shadow-2xs">
                        <i class="fa-solid fa-sack-dollar text-emerald-500"></i>
                    </div>
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/70 px-2 py-0.5 rounded-full">1x per dag • 3 Ads</span>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white group-hover:text-emerald-600 transition">€ 50.000 Cash</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Grote dagelijkse geldinjectie van € 50.000 contant geld op zak door 3 sponsor ads te kijken.
                    </p>

                    <!-- Voortgangsbalk (0/3, 1/3, 2/3, 3/3) -->
                    <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between text-[11px] font-bold mb-1">
                            <span class="text-slate-500 dark:text-slate-400">Voortgang:</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-extrabold">
                                {{ !empty($adDailyData['cash_claimed']) ? '3 / 3' : ($adDailyData['cash_progress'] ?? 0).' / 3' }} Ads
                            </span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden border border-slate-200 dark:border-slate-700">
                            <div class="bg-gradient-to-r from-emerald-400 to-teal-500 h-full rounded-full transition-all duration-300" 
                                 style="width: {{ !empty($adDailyData['cash_claimed']) ? 100 : min(100, (($adDailyData['cash_progress'] ?? 0) / 3) * 100) }}%;"></div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 relative z-10">
                    @if(!empty($adDailyData['cash_claimed']))
                        <button type="button" disabled 
                                class="w-full min-h-11 py-2 px-3 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 font-black text-xs shadow-xs transition flex items-center justify-center gap-1.5 cursor-not-allowed text-center leading-tight">
                            <i class="fa-solid fa-check text-xs text-emerald-500"></i>
                            <span>Vandaag Al Geclaimd</span>
                        </button>
                    @else
                        <button type="button" @disabled($cannotWatchStandardAd) onclick="window.tbsOpenSponsorAd('daily_cash')" class="w-full min-h-11 py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white font-black text-xs shadow-xs transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer text-center leading-tight disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fa-solid fa-sack-dollar text-xs"></i>
                            <span>Kijk Ad voor €50.000 ({{ ($adDailyData['cash_progress'] ?? 0) + 1 }}/3{{ ($adDailyData['cash_progress'] ?? 0) === 2 ? ' - Claim!' : '' }})</span>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Daily Card 3: +50 XP Boost (3 Ads) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700/80 rounded-2xl p-5 shadow-xs flex flex-col justify-between transition-all duration-200 group relative overflow-hidden">
                <div class="relative z-10">
                    <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-xl mb-3 group-hover:scale-105 transition shadow-2xs">
                        <i class="fa-solid fa-bolt text-purple-600 dark:text-purple-400"></i>
                    </div>
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 px-2 py-0.5 rounded-full">1x per dag • 3 Ads</span>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white group-hover:text-purple-600 transition">+50 XP Boost</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Geef je karakter dagelijks een fikse level-up boost van +50 XP met 3 sponsor advertenties.
                    </p>

                    <!-- Voortgangsbalk (0/3, 1/3, 2/3, 3/3) -->
                    <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between text-[11px] font-bold mb-1">
                            <span class="text-slate-500 dark:text-slate-400">Voortgang:</span>
                            <span class="text-purple-600 dark:text-purple-400 font-extrabold">
                                {{ !empty($adDailyData['xp_claimed']) ? '3 / 3' : ($adDailyData['xp_progress'] ?? 0).' / 3' }} Ads
                            </span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden border border-slate-200 dark:border-slate-700">
                            <div class="bg-purple-600 h-full rounded-full transition-all duration-300" 
                                 style="width: {{ !empty($adDailyData['xp_claimed']) ? 100 : min(100, (($adDailyData['xp_progress'] ?? 0) / 3) * 100) }}%;"></div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 relative z-10">
                    @if(!empty($adDailyData['xp_claimed']))
                        <button type="button" disabled 
                                class="w-full min-h-11 py-2 px-3 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 font-black text-xs shadow-xs transition flex items-center justify-center gap-1.5 cursor-not-allowed text-center leading-tight">
                            <i class="fa-solid fa-check text-xs text-emerald-500"></i>
                            <span>Vandaag Al Geclaimd</span>
                        </button>
                    @else
                        <button type="button" @disabled($cannotWatchStandardAd) onclick="window.tbsOpenSponsorAd('daily_xp')" class="w-full min-h-11 py-2 px-3 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs shadow-xs transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer text-center leading-tight disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fa-solid fa-bolt text-xs"></i>
                            <span>Kijk Ad voor +50 XP ({{ ($adDailyData['xp_progress'] ?? 0) + 1 }}/3{{ ($adDailyData['xp_progress'] ?? 0) === 2 ? ' - Claim!' : '' }})</span>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Daily Card 4: Directe Vrijlating (Onbeperkt) -->
            @php
                $reqPrisonAds = $character->getRequiredPrisonAds();
                $currPrisonAds = (int) ($character->ad_prison_progress ?? 0);
            @endphp
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700/80 rounded-2xl p-5 shadow-xs flex flex-col justify-between transition-all duration-200 group relative overflow-hidden">
                <div class="relative z-10">
                    <div class="w-12 h-12 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/70 flex items-center justify-center text-xl mb-3 group-hover:scale-105 transition shadow-2xs">
                        <i class="fa-solid fa-unlock-keyhole"></i>
                    </div>
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/60 border border-rose-200/80 dark:border-rose-800/70 px-2 py-0.5 rounded-full">Onbeperkt • {{ $character->isImprisoned() ? "{$currPrisonAds}/{$reqPrisonAds} Ads" : '-3m / Vrijlating' }}</span>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white group-hover:text-rose-600 transition">Direct Vrijkomen</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Zit je vast in de isoleercel? Elke sponsor video haalt 3 minuten van je straf af en bij {{ $reqPrisonAds }} {{ $reqPrisonAds === 1 ? 'ad' : 'ads' }} kom je per direct vrij!
                    </p>

                    <!-- Status Indicator -->
                    <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between text-[11px] font-bold">
                            <span class="text-slate-500 dark:text-slate-400">Status:</span>
                            @if($character->isImprisoned())
                                <span class="text-rose-600 dark:text-rose-400 font-extrabold animate-pulse">Vast ({{ $currPrisonAds }} / {{ $reqPrisonAds }} Ads)</span>
                            @else
                                <span class="text-slate-500 dark:text-slate-400 font-extrabold">Geen straf actief</span>
                            @endif
                        </div>
                        @if($character->isImprisoned())
                        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden border border-slate-200 dark:border-slate-700 mt-1">
                            <div class="bg-rose-600 h-full rounded-full transition-all duration-300"
                                 style="width: {{ min(100, ($currPrisonAds / max(1, $reqPrisonAds)) * 100) }}%;"></div>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 relative z-10">
                    @if(!$character->isImprisoned())
                        <button type="button" disabled 
                                title="Je kunt deze actie alleen gebruiken wanneer je daadwerkelijk vastzit in de isoleercel."
                                class="w-full min-h-11 py-2 px-3 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 font-black text-xs shadow-xs transition flex items-center justify-center gap-1.5 cursor-not-allowed text-center leading-tight">
                            <i class="fa-solid fa-shield-halved text-xs"></i>
                            <span>Alleen bij Isoleercel Straf</span>
                        </button>
                    @else
                        <button type="button" @disabled($cannotWatchStandardAd) onclick="window.tbsOpenSponsorAd('prison', () => { window.location.reload(); })" class="w-full min-h-11 py-2 px-3 rounded-xl bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500 text-white font-black text-xs shadow-xs transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer text-center leading-tight disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fa-solid fa-unlock text-xs"></i>
                            <span>Kijk Ad voor Vrijlating ({{ min($reqPrisonAds, $currPrisonAds + 1) }}/{{ $reqPrisonAds }}{{ ($currPrisonAds + 1) >= $reqPrisonAds ? ' - Direct Vrij!' : '' }})</span>
                        </button>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- COMBAT & LIQUIDATIE SPONSOR REWARDS -->
    <div class="space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
            <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2">
                <i class="fa-solid fa-crosshairs text-rose-600 dark:text-rose-400"></i> Liquidatie & Wapenarsenaal (Onbeperkt):
            </h2>
            <span class="text-[11px] font-bold text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/60 px-2.5 py-0.5 rounded-full border border-rose-200/80 dark:border-rose-800/70">Geen echt geld nodig &bull; 100% via Sponsor Ads</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Card 1: +50 Kogels -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700/80 rounded-2xl p-4 sm:p-5 shadow-xs flex flex-col justify-between transition-all duration-200 group">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/70 flex items-center justify-center text-xl mb-3 group-hover:scale-105 transition shadow-2xs">
                        <i class="fa-solid fa-bomb"></i>
                    </div>
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/60 border border-rose-200/80 dark:border-rose-800/70 px-2 py-0.5 rounded-full">3 Ads &bull; [{{ $bulletsProgress }}/3] Voortgang</span>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white group-hover:text-rose-600 dark:group-hover:text-rose-400 transition">+50 Scherpe Kogels 💣</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Laad je wapen door voor aanslagen en liquidaties op vijanden in de TBS kliniek. Momenteel in bezit: <strong class="text-rose-600 dark:text-rose-400">{{ number_format($character->bullets ?? 0, 0, ',', '.') }} kogels</strong>. Bekijk 3 korte sponsor ads voor +50 kogels.
                    </p>

                    <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between text-[11px] font-bold mb-1">
                            <span class="text-slate-500 dark:text-slate-400">Voortgang:</span>
                            <span class="text-rose-600 dark:text-rose-400 font-extrabold">{{ $bulletsProgress }} / 3 Ads</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden border border-slate-200 dark:border-slate-700">
                            <div class="bg-rose-600 h-full rounded-full transition-all duration-300" style="width: {{ min(100, ($bulletsProgress / 3) * 100) }}%;"></div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @disabled($cannotWatchStandardAd) onclick="window.tbsOpenSponsorAd('bullets')" class="w-full min-h-11 py-2 px-3 rounded-xl bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500 text-white font-black text-xs shadow-xs transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer text-center leading-tight disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-play text-xs"></i>
                        <span>Kijk Ad voor Kogels ({{ $bulletsProgress + 1 }}/3{{ $bulletsProgress === 2 ? ' - Claim!' : '' }})</span>
                    </button>
                </div>
            </div>

            <!-- Card 2: 12u Kogelwerend Vest -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700/80 rounded-2xl p-4 sm:p-5 shadow-xs flex flex-col justify-between transition-all duration-200 group">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-xl mb-3 group-hover:scale-105 transition shadow-2xs">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 px-2 py-0.5 rounded-full">3 Ads &bull; [{{ $vestProgress }}/3] &bull; 12 Uur</span>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition">Kogelwerend Vest 🛡️</h3>
                    @php
                        $vestSecs = $character->getVestRemainingSeconds();
                        $vestFormatted = sprintf('%02d:%02d', intdiv($vestSecs, 3600), intdiv($vestSecs % 3600, 60));
                    @endphp
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Verhoogt je verdediging met +150% en zorgt voor 45% ricochet-backfire gevaar voor schutters! Status: <strong class="{{ $character->hasVest() ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500 dark:text-slate-400' }}">{{ $character->hasVest() ? 'Actief (' . $vestFormatted . 'u)' : 'Niet actief' }}</strong>.
                    </p>

                    <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between text-[11px] font-bold mb-1">
                            <span class="text-slate-500 dark:text-slate-400">Voortgang:</span>
                            <span class="text-purple-600 dark:text-purple-400 font-extrabold">{{ $vestProgress }} / 3 Ads</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden border border-slate-200 dark:border-slate-700">
                            <div class="bg-purple-600 h-full rounded-full transition-all duration-300" style="width: {{ min(100, ($vestProgress / 3) * 100) }}%;"></div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @disabled($cannotWatchStandardAd) onclick="window.tbsOpenSponsorAd('vest')" class="w-full min-h-11 py-2 px-3 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs shadow-xs transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer text-center leading-tight disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-shield-halved text-xs"></i>
                        <span>Kijk Ad voor Vest ({{ $vestProgress + 1 }}/3{{ $vestProgress === 2 ? ' - Claim!' : '' }})</span>
                    </button>
                </div>
            </div>

            <!-- Card 3: Coma Ontwaken -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700/80 rounded-2xl p-4 sm:p-5 shadow-xs flex flex-col justify-between transition-all duration-200 group">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/70 flex items-center justify-center text-xl mb-3 group-hover:scale-105 transition shadow-2xs">
                        <i class="fa-solid fa-heart-pulse"></i>
                    </div>
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/70 px-2 py-0.5 rounded-full">3 Ads &bull; [{{ $comaProgress }}/3] &bull; IC Hulp</span>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition">Coma Ontwaken 💉</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Neergeschoten of ricochet gehad? Ontwaak direct uit het Intensive Care Coma en herstel naar 50% HP door 3 sponsor ads te kijken!
                    </p>

                    <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between text-[11px] font-bold mb-1">
                            <span class="text-slate-500 dark:text-slate-400">Voortgang:</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-extrabold">{{ $comaProgress }} / 3 Ads</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden border border-slate-200 dark:border-slate-700">
                            <div class="bg-emerald-600 h-full rounded-full transition-all duration-300" style="width: {{ min(100, ($comaProgress / 3) * 100) }}%;"></div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 space-y-2">
                    @if($isComaOrCodeBlauw)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <form method="POST" action="{{ route('cooldown.skip') }}" class="w-full">
                                @csrf
                                <input type="hidden" name="action" value="coma">
                                <button type="submit" @disabled(($character->stars ?? 0) < \App\Models\Character::REVIVE_SERUM_STARS) class="btn-star-gold w-full min-h-11 py-2 px-3 rounded-xl font-black text-xs shadow-xs transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer text-center leading-tight disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fa-solid fa-star text-xs"></i>
                                    <span>Ontwaken ({{ \App\Models\Character::REVIVE_SERUM_STARS }} ⭐)</span>
                                </button>
                            </form>
                            <button type="button" @disabled($adsLeftToday <= 0) onclick="window.tbsOpenSponsorAd('coma_revive', () => { window.location.reload(); })" class="w-full min-h-11 py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white font-black text-xs shadow-xs transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer text-center leading-tight disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fa-solid fa-syringe text-xs"></i>
                                <span>Kijk Ad ({{ $comaProgress + 1 }}/3{{ $comaProgress === 2 ? ' - Claim!' : '' }})</span>
                            </button>
                        </div>
                    @else
                        <button type="button" disabled class="w-full min-h-11 py-2 px-3 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 font-black text-xs shadow-xs transition flex items-center justify-center gap-1.5 cursor-not-allowed text-center leading-tight">
                            <i class="fa-solid fa-check text-xs text-emerald-500"></i>
                            <span>Je bent niet in Coma</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ONGOING SPONSOR REWARDS (HERHAALBAAR) -->
    <div class="space-y-3">
        <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2">
            <i class="fa-solid fa-infinity text-purple-600 dark:text-purple-400"></i> Doorlopende Beloningen:
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">

            <!-- Card 1: Respect +1 🔥 -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700/80 rounded-2xl p-5 shadow-xs flex flex-col justify-between transition-all duration-200 group relative overflow-hidden">
                <div class="relative z-10">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-xl mb-3 group-hover:scale-105 transition shadow-2xs">
                        <i class="fa-solid fa-fire text-amber-500"></i>
                    </div>
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 px-2 py-0.5 rounded-full font-black">Directe Beloning</span>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white group-hover:text-purple-600 transition">+1 Respect 🔥</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Verhoog direct jouw aanzien en klim in de algemene ranglijst van de kliniek. 1 ad = direct +1 Respect!
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 relative z-10">
                    <button type="button" @disabled($cannotWatchStandardAd) onclick="window.tbsOpenSponsorAd('respect')" class="w-full min-h-11 py-2 px-3 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs shadow-xs transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer text-center leading-tight disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-fire text-xs"></i>
                        <span>Kijk Ad voor +1 Respect</span>
                    </button>
                </div>
            </div>

            <!-- Card 2: 14-Daagse Loterij Ticket (15 Ads vereist) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700/80 rounded-2xl p-5 shadow-xs flex flex-col justify-between transition-all duration-200 group">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200/80 dark:border-amber-800/70 flex items-center justify-center text-xl mb-3 group-hover:scale-105 transition shadow-2xs">
                        <i class="fa-solid fa-ticket text-amber-500"></i>
                    </div>
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/70 px-2 py-0.5 rounded-full font-black">15 Ads per Lot • Onbeperkt</span>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition">14-Daagse Loterij Ticket</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Bekijk 15 sponsor ads en ontvang direct 1 gratis lot voor de actieve 14-daagse loterijtrekking met gigantische jackpot!
                    </p>

                    <!-- Voortgangsbalk (0/15 t/m 14/15) -->
                    <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between text-[11px] font-bold mb-1">
                            <span class="text-slate-500 dark:text-slate-400">Voortgang:</span>
                            <span class="text-amber-600 dark:text-amber-400 font-extrabold">{{ $character->ad_lottery_progress ?? 0 }} / 15 Ads</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden border border-slate-200 dark:border-slate-700">
                            <div class="bg-gradient-to-r from-amber-500 to-yellow-500 h-full rounded-full transition-all duration-300" 
                                 style="width: {{ min(100, (($character->ad_lottery_progress ?? 0) / 15) * 100) }}%;"></div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @disabled($cannotWatchStandardAd) onclick="window.tbsOpenSponsorAd('lottery_ticket')" class="btn-star-gold w-full min-h-11 py-2 px-3 rounded-xl font-black text-xs shadow-xs transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer text-center leading-tight disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-ticket text-xs"></i>
                        <span>Kijk Ad voor Lot ({{ ($character->ad_lottery_progress ?? 0) + 1 }}/15{{ ($character->ad_lottery_progress ?? 0) === 14 ? ' - Claim Lot!' : '' }})</span>
                    </button>
                </div>
            </div>

            <!-- Card 3: Masker +1 (3 Ads vereist) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700/80 rounded-2xl p-5 shadow-xs flex flex-col justify-between transition-all duration-200 group">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-xl mb-3 group-hover:scale-105 transition shadow-2xs">
                        <i class="fa-solid fa-masks-theater text-purple-600 dark:text-purple-400"></i>
                    </div>
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 px-2 py-0.5 rounded-full font-black">3 Ads per Masker</span>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white group-hover:text-purple-700 dark:group-hover:text-purple-400 transition">+1 Bivakmuts / Masker</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Bekijk 3 sponsor ads en ontvang een gratis masker voor zakkenrollen en solo/bende heists.
                    </p>

                    <!-- Voortgangsbalk (0/3, 1/3, 2/3) -->
                    <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between text-[11px] font-bold mb-1">
                            <span class="text-slate-500 dark:text-slate-400">Voortgang:</span>
                            <span class="text-purple-600 dark:text-purple-400 font-extrabold">{{ $character->ad_mask_progress ?? 0 }} / 3 Ads</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden border border-slate-200 dark:border-slate-700">
                            <div class="bg-purple-600 h-full rounded-full transition-all duration-300" 
                                 style="width: {{ min(100, (($character->ad_mask_progress ?? 0) / 3) * 100) }}%;"></div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @disabled($cannotWatchStandardAd) onclick="window.tbsOpenSponsorAd('mask')" class="w-full min-h-11 py-2 px-3 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs shadow-xs transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer text-center leading-tight disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-masks-theater text-xs"></i>
                        <span>Kijk Ad voor Masker ({{ ($character->ad_mask_progress ?? 0) + 1 }}/3{{ ($character->ad_mask_progress ?? 0) === 2 ? ' - Claim!' : '' }})</span>
                    </button>
                </div>
            </div>

            <!-- Card 4: Geheime Kluiscode Voucher (3 Ads vereist) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700/80 rounded-2xl p-5 shadow-xs flex flex-col justify-between transition-all duration-200 group">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-xl mb-3 group-hover:scale-105 transition shadow-2xs">
                        <i class="fa-solid fa-key text-purple-600 dark:text-purple-400"></i>
                    </div>
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 px-2 py-0.5 rounded-full font-black">3 Ads per Code</span>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition">Geheime Kluiscode Voucher</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Bekijk 3 sponsor ads en ontvang een voucher in je inbox t.w.v. <strong>€5.000, 2 Sterren ⭐ en 2 Kluismunten</strong>!
                    </p>

                    <!-- Voortgangsbalk (0/3, 1/3, 2/3) -->
                    <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between text-[11px] font-bold mb-1">
                            <span class="text-slate-500 dark:text-slate-400">Voortgang:</span>
                            <span class="text-purple-600 dark:text-purple-400 font-extrabold">{{ $character->ad_vault_progress ?? 0 }} / 3 Ads</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden border border-slate-200 dark:border-slate-700">
                            <div class="bg-purple-600 h-full rounded-full transition-all duration-300" 
                                 style="width: {{ min(100, (($character->ad_vault_progress ?? 0) / 3) * 100) }}%;"></div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @disabled($cannotWatchStandardAd) onclick="window.tbsOpenSponsorAd('vault_code')" class="w-full min-h-11 py-2 px-3 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs shadow-xs transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer text-center leading-tight disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-key text-xs"></i>
                        <span>Kijk Ad voor Kluiscode ({{ ($character->ad_vault_progress ?? 0) + 1 }}/3{{ ($character->ad_vault_progress ?? 0) === 2 ? ' - Claim!' : '' }})</span>
                    </button>
                </div>
            </div>

            <!-- Card 5: Cash €2.500 -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700/80 rounded-2xl p-5 shadow-xs flex flex-col justify-between transition-all duration-200 group">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/70 flex items-center justify-center text-xl mb-3 group-hover:scale-105 transition shadow-2xs">
                        <i class="fa-solid fa-money-bill-wave text-emerald-500"></i>
                    </div>
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/70 px-2 py-0.5 rounded-full font-black">Directe Beloning</span>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white group-hover:text-emerald-600 transition">€ 2.500 Zakgeld</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Direct contant zakgeld op zak om frikandelbroodjes, energie-shakes of wapens te kopen.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @disabled($cannotWatchStandardAd) onclick="window.tbsOpenSponsorAd('cash')" class="w-full min-h-11 py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white font-black text-xs shadow-xs transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer text-center leading-tight disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-play text-xs"></i>
                        <span>Kijk Ad voor €2.500</span>
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- Extra Information Banner: 10-ad bonus code vouchers -->
    <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs text-slate-700 dark:text-slate-300 shadow-xs">
        <div class="flex items-start gap-3.5">
            <div class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 flex items-center justify-center text-base shrink-0 font-bold border border-purple-200/80 dark:border-purple-800/70">
                <i class="fa-solid fa-bullhorn"></i>
            </div>
            <div class="space-y-1">
                <h4 class="font-extrabold text-slate-900 dark:text-white text-sm flex flex-wrap items-center gap-2">
                    <span>Elke 10 Ads: Exclusieve Geheime Kluiscode Voucher in je Inbox!</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-purple-100 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                        {{ $totalAds % 10 }} / 10 Ads
                    </span>
                </h4>
                <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                    Als extra beloning ontvang je bij elke 10 bekeken advertenties automatisch een unieke kluiscode voucher in je in-game berichtenbox. Wissel deze code in op de MedicijnKluis voor <strong>€ 15.000 bankgeld en 3 Sterren ⭐</strong>!
                </p>
            </div>
        </div>
        <div class="w-full sm:w-44 shrink-0">
            <div class="flex items-center justify-between text-[11px] font-bold mb-1">
                <span class="text-slate-500 dark:text-slate-400">Volgende Voucher:</span>
                <span class="text-purple-700 dark:text-purple-400 font-extrabold">Nog {{ 10 - ($totalAds % 10) }} ads</span>
            </div>
            <div class="w-full bg-slate-200/80 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                <div class="bg-purple-600 h-full rounded-full transition-all duration-300" style="width: {{ (($totalAds % 10) / 10) * 100 }}%;"></div>
            </div>
        </div>
    </div>

</div>


@endsection
