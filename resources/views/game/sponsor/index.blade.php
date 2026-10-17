@extends('layouts.app')

@section('title', 'Sponsorkamer - Bekijk Ads voor Gratis VIP & Beloningen')

@section('content')
<div class="space-y-6">

    <!-- Top Hero Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xs relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 bg-purple-50 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 text-xs font-bold px-3 py-1 rounded-full border border-purple-200 dark:border-purple-800/60 mb-2">
                    <i class="fa-solid fa-tv text-purple-600 dark:text-purple-400"></i> TBS Sponsorkamer & Gratis VIP
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Kijk & Verdien</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-xl leading-relaxed">
                    Help mee de TBS Kliniek gratis online te houden! Bekijk korte sponsor advertenties van 15 seconden en ontvang direct zakgeld, maskers, strafvermindering en ontgrendel bij 50 views <strong>24 uur gratis VIP status</strong>!
                </p>
            </div>

            <!-- Stats Chips -->
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <div class="px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 shadow-2xs">
                    <span class="text-slate-400 dark:text-slate-400 block text-[10px] uppercase tracking-wider">Vandaag Bekeken</span>
                    <span class="text-slate-900 dark:text-white font-extrabold text-sm">{{ $adsToday }} / 500</span>
                </div>
                <div class="px-3.5 py-2 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200 dark:border-purple-800 text-xs font-bold text-purple-700 dark:text-purple-300 shadow-2xs">
                    <span class="text-purple-400 block text-[10px] uppercase tracking-wider">Totaal Bekeken</span>
                    <span class="text-purple-900 dark:text-purple-200 font-black text-sm">{{ $totalAds }} ads</span>
                </div>
            </div>
        </div>
    </div>

    <!-- VIP MILESTONE PROGRESS BAR (50 Ads = 24h VIP) -->
    <div class="bg-white dark:bg-slate-900 border border-purple-200/90 dark:border-purple-800/60 rounded-2xl p-6 shadow-xs relative overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-300 dark:border-amber-700/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl shrink-0 shadow-2xs">
                    <i class="fa-solid fa-crown text-amber-500"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        <span>24 Uur Gratis VIP Voortgang</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60">
                            {{ $totalAds % 50 }} / 50 Ads
                        </span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Bekijk in totaal 50 advertenties en ontvang automatisch een <strong>volledige 24-uurs VIP status</strong> (incl. 5 VIP punten/uur & extra spins)!
                    </p>
                </div>
            </div>

            <div class="text-right shrink-0">
                <span class="text-xs font-extrabold text-purple-700 dark:text-purple-400">{{ $vipProgress }}% Voltooid</span>
                <span class="text-[11px] text-slate-400 block">Nog {{ $adsUntilVip }} ads tot 24h VIP</span>
            </div>
        </div>

        <!-- Progress Tracker Bar -->
        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-3.5 p-0.5 border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="bg-purple-600 h-full rounded-full transition-all duration-500 shadow-xs" 
                 style="width: {{ $vipProgress }}%;"></div>
        </div>

        @if($character->isVip())
        <div class="mt-3 text-xs text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl px-3 py-2 flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400"></i>
            <span>Jouw VIP status is momenteel actief tot <strong>{{ $character->vip_expires_at?->format('d-m-Y H:i') ?? 'Onbeperkt' }}</strong>! Extra 50 ads verlengen jouw VIP met +24 uur.</span>
        </div>
        @endif
    </div>

    <!-- REWARD SELECTION GRID -->
    <div>
        <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-400 mb-3 flex items-center gap-2">
            <i class="fa-solid fa-gift text-purple-600 dark:text-purple-400"></i> Kies jouw directe beloning per advertentie:
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

            <!-- Card 1: Cooldown Reset ⚡ -->
            <div class="bg-white dark:bg-slate-900 border border-purple-200 dark:border-purple-800/60 hover:border-purple-400 rounded-2xl p-5 shadow-xs flex flex-col justify-between transition group relative overflow-hidden">
                <div class="relative z-10">
                    <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 flex items-center justify-center text-xl mb-3 group-hover:scale-105 transition">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-300 bg-purple-100 dark:bg-purple-950/80 px-2 py-0.5 rounded">Populair</span>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white group-hover:text-purple-700 dark:group-hover:text-purple-400 transition">Reset Alle Cooldowns</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Wis direct alle wachttijden op Hosselen, Zakkenrollen, Kluisjes, Smokkelen en Behandeling!
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 relative z-10">
                    <button type="button" onclick="window.tbsOpenSponsorAd('cooldown')" 
                            class="w-full py-2.5 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-extrabold text-xs shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-bolt text-xs"></i>
                        <span>Kijk Ad (Reset Cooldowns)</span>
                    </button>
                </div>
            </div>

            <!-- Card 2: Cash €2.500 -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-purple-300 rounded-2xl p-5 shadow-xs flex flex-col justify-between transition group">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800 flex items-center justify-center text-xl mb-3 group-hover:scale-105 transition">
                        <i class="fa-solid fa-money-bill-wave"></i>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white group-hover:text-purple-700 dark:group-hover:text-purple-400 transition">€ 2.500 Zakgeld</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Direct contant zakgeld op zak om frikandelbroodjes, energie-shakes of wapens te kopen.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="window.tbsOpenSponsorAd('cash')" 
                            class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-play text-xs"></i>
                        <span>Kijk Ad voor €2.500</span>
                    </button>
                </div>
            </div>

            <!-- Card 3: Strafvermindering -15 Min -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-purple-300 rounded-2xl p-5 shadow-xs flex flex-col justify-between transition group">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-800 flex items-center justify-center text-xl mb-3 group-hover:scale-105 transition">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white group-hover:text-purple-700 dark:group-hover:text-purple-400 transition">-15 Min Isoleercel</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Zit je vast in de isoleercel? Scheer 15 minuten van je straftijd af of kom direct vervroegd vrij!
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="window.tbsOpenSponsorAd('prison')" 
                            class="w-full py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-lock-open text-xs"></i>
                        <span>Kijk Ad voor -15 Min</span>
                    </button>
                </div>
            </div>

            <!-- Card 4: Masker +1 -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-purple-300 rounded-2xl p-5 shadow-xs flex flex-col justify-between transition group">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-100 dark:border-purple-800 flex items-center justify-center text-xl mb-3 group-hover:scale-105 transition">
                        <i class="fa-solid fa-masks-theater"></i>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white group-hover:text-purple-700 dark:group-hover:text-purple-400 transition">+1 Bivakmuts / Masker</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Onmisbaar om ongemerkt te zakkenrollen op de binnenplaats en wakers te ontwijken.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="window.tbsOpenSponsorAd('mask')" 
                            class="w-full py-2.5 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-mask text-xs"></i>
                        <span>Kijk Ad voor Masker</span>
                    </button>
                </div>
            </div>

            <!-- Card 5: Kluismunt +1 -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-purple-300 rounded-2xl p-5 shadow-xs flex flex-col justify-between transition group">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-800 flex items-center justify-center text-xl mb-3 group-hover:scale-105 transition">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white group-hover:text-purple-700 dark:group-hover:text-purple-400 transition">+1 Kluismunt</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Verzamel kluismunten om hints te kopen bij de MedicijnKluis of vouchers te claimen.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="window.tbsOpenSponsorAd('token')" 
                            class="w-full py-2.5 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-key text-xs"></i>
                        <span>Kijk Ad voor Munt</span>
                    </button>
                </div>
            </div>

            <!-- Card 6: Ster +1 -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-amber-300 rounded-2xl p-5 shadow-xs flex flex-col justify-between transition group">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800 flex items-center justify-center text-xl mb-3 group-hover:scale-105 transition">
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white group-hover:text-amber-600 transition">+1 Gratis Ster ⭐</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Verdien gratis Sterren om premium functies te ontgrendelen of direct vrij te kopen.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="window.tbsOpenSponsorAd('star')" 
                            class="w-full py-2.5 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-star text-xs"></i>
                        <span>Kijk Ad voor Ster ⭐</span>
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- Robins Ads Actief Banner -->
    <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-2xl p-5 shadow-xs flex items-center gap-3.5 text-xs text-emerald-900 dark:text-emerald-200">
        <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center text-lg shrink-0">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="space-y-0.5">
            <h4 class="font-extrabold text-emerald-950 dark:text-emerald-100 text-sm">Robins Ads Rewarded Network Actief</h4>
            <p class="text-emerald-800 dark:text-emerald-300 leading-relaxed">
                Advertenties zijn live gekoppeld via Robins Ads met server-geverifieerde beloningen en video interstitials. Bekijk advertenties en ontvang direct cooldown resets, contanten, kluismunten of spaar door voor 24 uur gratis VIP!
            </p>
        </div>
    </div>

    <!-- Extra Information Banner: 10-ad bonus code vouchers -->
    <div class="bg-indigo-50/70 dark:bg-slate-900 border border-indigo-200 dark:border-indigo-900/60 rounded-2xl p-5 flex items-start gap-3.5 text-xs text-indigo-900 dark:text-indigo-200">
        <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 flex items-center justify-center text-base shrink-0 font-bold">
            <i class="fa-solid fa-bullhorn"></i>
        </div>
        <div class="space-y-1">
            <h4 class="font-extrabold text-indigo-950 dark:text-indigo-100 text-sm">Elke 10 Ads: Exclusieve Geheime Kluiscode Voucher in je Inbox!</h4>
            <p class="text-indigo-800 dark:text-indigo-300 leading-relaxed">
                Als extra beloning ontvang je bij elke 10 bekeken advertenties automatisch een unieke kluiscode voucher in je in-game berichtenbox. Wissel deze code in op de MedicijnKluis voor <strong>€ 5.000 bankgeld, 2 Sterren ⭐ en 2 Kluismunten</strong>!
            </p>
        </div>
    </div>

</div>
@endsection
