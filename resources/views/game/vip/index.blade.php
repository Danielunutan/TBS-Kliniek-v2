@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'kopen' }">

    <!-- Top Hero Banner (Clean TBS Style / Dark Mode Ready) -->
    <div class="rounded-2xl shadow-xs p-6 sm:p-8 text-slate-800 dark:text-slate-100 relative overflow-hidden bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-200 text-xs font-bold px-3 py-1 rounded-full border border-amber-200 dark:border-amber-800/60 mb-2">
                    <i class="fa-solid fa-crown text-amber-500"></i> Exclusief & 100% Gratis Lidmaatschap
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">TBS VIP Lounge & Punten Shop</h1>
                <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-1 max-w-xl leading-relaxed">
                    Verdien gratis VIP via de Sponsorkamer of de officiële Discord! Ontvang elk actief speeluur <strong>5 VIP Punten</strong> en wissel ze in voor unieke bonussen, cashbescherming en privileges.
                </p>
            </div>
            
            <!-- VIP Status Card -->
            <div class="shrink-0 bg-slate-50 dark:bg-slate-800/70 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 flex items-center gap-4 shadow-2xs">
                <div class="w-12 h-12 rounded-xl {{ $character->isVip() ? 'bg-amber-100 dark:bg-amber-950/80 text-amber-600 dark:text-amber-400 border border-amber-300 dark:border-amber-700' : 'bg-slate-100 dark:bg-slate-700/60 text-slate-400 border border-slate-200 dark:border-slate-600' }} flex items-center justify-center text-xl font-black shadow-xs">
                    <i class="fa-solid fa-crown"></i>
                </div>
                <div>
                    <div class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-400">Jouw VIP Saldo</div>
                    <div class="text-xl font-black text-amber-600 dark:text-amber-400 font-mono flex items-center gap-1.5">
                        <span>{{ number_format($character->vip_points ?? 0, 0, ',', '.') }}</span>
                        <span class="text-xs font-bold text-slate-600 dark:text-slate-300">Punten</span>
                    </div>
                    <div class="text-[10px] font-bold mt-0.5">
                        @if($character->isVip())
                            <span class="text-emerald-600 dark:text-emerald-400 font-black flex items-center gap-1">
                                <i class="fa-solid fa-circle-check"></i> Actief VIP Lid
                            </span>
                            @if($character->vip_expires_at)
                                <span class="text-[9px] text-slate-400 dark:text-slate-500 block">
                                    Tot {{ $character->vip_expires_at->format('d-m-Y H:i') }}
                                </span>
                            @endif
                        @else
                            <span class="text-slate-400 dark:text-slate-500">Nog geen actief VIP lid</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left Navigation -->
        <div class="lg:col-span-4 space-y-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs p-2 space-y-1 text-xs font-bold">
                <button type="button" @click="activeTab = 'kopen'" 
                        :class="activeTab === 'kopen' ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-200 font-bold border-transparent shadow-2xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 border-transparent'"
                        class="w-full flex items-center justify-between p-3.5 rounded-xl border transition cursor-pointer">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-crown text-amber-500 text-sm"></i>
                        <span>VIP Status (100% Gratis via Ads)</span>
                    </span>
                    <i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i>
                </button>

                <button type="button" @click="activeTab = 'uitgeven'" 
                        :class="activeTab === 'uitgeven' ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold border-transparent shadow-2xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 border-transparent'"
                        class="w-full flex items-center justify-between p-3.5 rounded-xl border transition cursor-pointer">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-gift text-purple-600 dark:text-purple-400 text-sm"></i>
                        <span>VIP Punten Inwisselen</span>
                    </span>
                    <i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i>
                </button>
            </div>

            <!-- Discord Quick Box -->
            <div class="bg-gradient-to-br from-[#5865F2]/10 via-[#5865F2]/5 to-transparent border border-[#5865F2]/30 dark:border-[#5865F2]/40 rounded-2xl p-5 shadow-xs text-center space-y-3 bg-white dark:bg-slate-900">
                <div class="w-11 h-11 mx-auto rounded-full bg-[#5865F2] text-white flex items-center justify-center text-xl shadow-sm">
                    <i class="fa-brands fa-discord"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">Officiële Discord Server</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Koppel je Discord voor <strong>3 dagen gratis VIP</strong> & <strong>€2.500 cash</strong>, giveaways en updates!
                    </p>
                </div>
                
                <div class="space-y-2 pt-1">
                    @if(empty($hasDiscordReward))
                        <a href="{{ route('discord.verify') }}" 
                           class="block w-full py-2.5 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs shadow-xs transition active:scale-[0.99]">
                            <i class="fa-solid fa-link mr-1.5"></i> Koppel Discord (+3 Dagen VIP)
                        </a>
                    @else
                        <div class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 py-1.5 px-3 rounded-lg border border-emerald-200 dark:border-emerald-800/50">
                            <i class="fa-solid fa-circle-check mr-1"></i> Discord Bonus Reeds Geclaimd
                        </div>
                    @endif

                    <a href="https://discord.gg/tbs" target="_blank" rel="noopener noreferrer" 
                       class="block w-full py-2 px-4 rounded-xl bg-[#5865F2] hover:bg-[#4752c4] text-white font-extrabold text-xs shadow-xs transition active:scale-[0.99]">
                        <i class="fa-brands fa-discord mr-1.5"></i> Open discord.gg/tbs
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Content Column -->
        <div class="lg:col-span-8">
            
            <!-- TAB 1: VIP VIA ADVERTENTIES & STATUS -->
            <div x-show="activeTab === 'kopen'" class="space-y-6">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-6">
                    <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 text-[10px] font-black uppercase tracking-wider mb-2">
                            100% Gratis • Geen Kosten
                        </span>
                        <h2 class="text-xl font-black text-slate-900 dark:text-white">VIP Worden & Behouden — 100% Gratis!</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                            In TBS Kliniek betaal je <strong>nooit echt geld</strong> voor VIP. VIP status en alle exclusieve privileges ontgrendel je eenvoudig en gratis door sponsor-advertenties te bekijken in de Sponsorkamer (24u VIP per 250 ads) of door je account eenmalig te koppelen aan onze Discord server (3 dagen gratis VIP).
                        </p>
                    </div>

                    <!-- Live Milestone Tracker Box -->
                    <div class="p-5 rounded-2xl bg-gradient-to-br from-purple-500/10 via-purple-500/5 to-amber-500/10 border border-purple-200 dark:border-purple-800/60 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center font-black text-base shadow-xs shrink-0">
                                    <i class="fa-solid fa-tv"></i>
                                </div>
                                <div>
                                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-1.5">
                                        <span>Sponsorkamer Mijlpaal Voortgang</span>
                                        <span class="text-[10px] text-purple-600 dark:text-purple-400 font-bold font-mono">({{ $totalAds ?? 0 }} ads bekeken)</span>
                                    </h3>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                        Elke mijlpaal van 250 advertenties activeert automatisch <strong>24 uur gratis VIP</strong>!
                                    </p>
                                </div>
                            </div>
                            <div class="text-left sm:text-right shrink-0">
                                <span class="text-xs font-black text-purple-700 dark:text-purple-400">{{ $vipProgress ?? 0 }}% Voltooid</span>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 block font-medium">Nog {{ $adsUntilVip ?? 250 }} ads tot 24u VIP</span>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="w-full bg-slate-200 dark:bg-slate-800 rounded-full h-3 p-0.5 overflow-hidden">
                            <div class="bg-gradient-to-r from-purple-600 to-amber-500 h-full rounded-full transition-all duration-500 shadow-2xs" 
                                 style="width: {{ $vipProgress ?? 0 }}%;"></div>
                        </div>

                        <!-- Status Callout -->
                        @if($character->isVip())
                            <div class="text-[11px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl px-3 py-2 flex items-center gap-2">
                                <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400"></i>
                                <span>Jouw VIP status is momenteel <strong>ACTIEF</strong> tot <strong>{{ $character->vip_expires_at?->format('d-m-Y H:i') ?? 'Onbeperkt' }}</strong>! Elke volgende 250 ads verlengt jouw VIP direct met nog eens +24 uur.</span>
                            </div>
                        @else
                            <div class="text-[11px] font-bold text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 flex items-center gap-2">
                                <i class="fa-solid fa-circle-info text-purple-600 dark:text-purple-400"></i>
                                <span>Je hebt momenteel nog geen actieve VIP status. Bekijk video's in de Sponsorkamer om direct 24 uur VIP gratis te ontgrendelen!</span>
                            </div>
                        @endif
                    </div>

                    <!-- Steps to Unlock via Ads -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-center space-y-2">
                            <div class="w-8 h-8 mx-auto rounded-full bg-purple-600 text-white flex items-center justify-center font-black text-xs shadow-xs">
                                1
                            </div>
                            <h4 class="font-extrabold text-xs text-slate-900 dark:text-white">Kijk Sponsor Ads</h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Ga naar de <a href="{{ route('sponsor.index') }}" class="text-purple-600 dark:text-purple-400 font-bold underline">Sponsorkamer</a> en bekijk korte advertenties voor cash, maskers en kluiscodes.</p>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-center space-y-2">
                            <div class="w-8 h-8 mx-auto rounded-full bg-purple-600 text-white flex items-center justify-center font-black text-xs shadow-xs">
                                2
                            </div>
                            <h4 class="font-extrabold text-xs text-slate-900 dark:text-white">Ontgrendel Gratis VIP</h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Bij elke mijlpaal van 250 ads activeert het systeem automatisch <strong>24 uur gratis VIP status</strong> op jouw account.</p>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-center space-y-2">
                            <div class="w-8 h-8 mx-auto rounded-full bg-purple-600 text-white flex items-center justify-center font-black text-xs shadow-xs">
                                3
                            </div>
                            <h4 class="font-extrabold text-xs text-slate-900 dark:text-white">Geniet van Voordelen</h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Ontvang elk uur +5 VIP punten, 1.000 gevechten/dag, 50% reiskorting en exclusieve shops zónder een cent te betalen!</p>
                        </div>
                    </div>

                    <!-- VIP Perks List -->
                    <div class="p-5 rounded-2xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 space-y-3">
                        <h3 class="text-xs font-black uppercase tracking-wider text-amber-900 dark:text-amber-200 flex items-center gap-2">
                            <i class="fa-solid fa-crown text-amber-600 dark:text-amber-400"></i>
                            <span>Wat krijg je als VIP lid?</span>
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            <div class="flex items-start gap-2.5 text-slate-700 dark:text-slate-300">
                                <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5"></i>
                                <span><strong>+5 VIP Punten</strong> per uur dat je actief inlogt en speelt!</span>
                            </div>
                            <div class="flex items-start gap-2.5 text-slate-700 dark:text-slate-300">
                                <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5"></i>
                                <span>Exclusieve <strong>Gouden VIP Kroon</strong> op je dossier en ranglijsten.</span>
                            </div>
                            <div class="flex items-start gap-2.5 text-slate-700 dark:text-slate-300">
                                <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5"></i>
                                <span><strong>1.000 Gevechten per Dag</strong> op De Luchtplaats (i.p.v. max 100).</span>
                            </div>
                            <div class="flex items-start gap-2.5 text-slate-700 dark:text-slate-300">
                                <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5"></i>
                                <span><strong>50% Reiskorting</strong> tussen alle paviljoens en afdelingen.</span>
                            </div>
                            <div class="flex items-start gap-2.5 text-slate-700 dark:text-slate-300">
                                <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5"></i>
                                <span><strong>3% Bankbelasting</strong> (i.p.v. 5%) bij het opnemen van contant geld.</span>
                            </div>
                            <div class="flex items-start gap-2.5 text-slate-700 dark:text-slate-300">
                                <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5"></i>
                                <span><strong>2x Dagelijks draaien</strong> aan het Rad van Fortuin.</span>
                            </div>
                            <div class="flex items-start gap-2.5 text-slate-700 dark:text-slate-300">
                                <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5"></i>
                                <span><strong>Extra Depot Capaciteit</strong> (+3 wagens en +50 opslagruimte).</span>
                            </div>
                            <div class="flex items-start gap-2.5 text-slate-700 dark:text-slate-300">
                                <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5"></i>
                                <span>Toegang tot de <strong>VIP Punten Shop</strong> voor herstel, koffers & cash protection.</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-center pt-2">
                        <a href="{{ route('sponsor.index') }}" 
                           class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs uppercase tracking-wider shadow-md transition active:scale-[0.99]">
                            <i class="fa-solid fa-tv text-sm"></i>
                            <span>Naar de Sponsorkamer (Kijk Ads & Ontgrendel Gratis VIP)</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- TAB 2: VIP PUNTEN INWISSELEN -->
            <div x-show="activeTab === 'uitgeven'" class="space-y-6">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-6">
                    <div class="border-b border-slate-100 dark:border-slate-800 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/60 text-[10px] font-black uppercase tracking-wider mb-2">
                                Beloningen Winkel
                            </span>
                            <h2 class="text-xl font-black text-slate-900 dark:text-white">VIP Punten Inwisselen</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                Wissel je verdiende VIP punten in voor krachtige privileges. Punten worden direct afgeschreven van je saldo.
                            </p>
                        </div>
                        <div class="shrink-0 bg-purple-50 dark:bg-purple-950/50 border border-purple-200 dark:border-purple-800/60 rounded-xl px-3.5 py-2 text-right">
                            <span class="text-[10px] font-bold text-slate-400 block uppercase">Jouw Saldo</span>
                            <span class="text-base font-black text-purple-700 dark:text-purple-300 font-mono">{{ number_format($character->vip_points ?? 0, 0, ',', '.') }} Punten</span>
                        </div>
                    </div>

                    <div class="space-y-3.5">
                        <!-- Option: 24 Uur VIP Status Verlenging (20 Punten) -->
                        <form action="{{ route('vip.redeem') }}" method="POST" class="p-4 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-100/60 dark:hover:bg-slate-800 transition">
                            @csrf
                            <input type="hidden" name="reward_type" value="vip_extend_24h">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-950/80 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 text-base shadow-2xs border border-amber-200 dark:border-amber-800">
                                    <i class="fa-solid fa-crown"></i>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-xs text-slate-900 dark:text-white">+24 Uur VIP Status Verlenging</h4>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Activeer of verleng je VIP lidmaatschap direct met 24 uur volledige privileges.</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0 self-end sm:self-auto">
                                <span class="text-xs font-black text-purple-700 dark:text-purple-400 font-mono">20 Punten</span>
                                <button type="submit" @if(($character->vip_points ?? 0) < 20) disabled @endif
                                        class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-extrabold text-xs shadow-xs transition cursor-pointer">
                                    Claim
                                </button>
                            </div>
                        </form>

                        <!-- Option: 24h Cash Protection (20 Punten) -->
                        <form action="{{ route('vip.redeem') }}" method="POST" class="p-4 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-100/60 dark:hover:bg-slate-800 transition">
                            @csrf
                            <input type="hidden" name="reward_type" value="cash_protection">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 text-base shadow-2xs border border-indigo-200 dark:border-indigo-800">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-xs text-slate-900 dark:text-white">24 Uur Cash Bescherming</h4>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Bescherm je contante geld 24 uur tegen plunderingen in gevechten.</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0 self-end sm:self-auto">
                                <span class="text-xs font-black text-purple-700 dark:text-purple-400 font-mono">20 Punten</span>
                                <button type="submit" @if(($character->vip_points ?? 0) < 20) disabled @endif
                                        class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-extrabold text-xs shadow-xs transition cursor-pointer">
                                    Claim
                                </button>
                            </div>
                        </form>

                        <!-- Option: Full Health & Energy (10 Punten) -->
                        <form action="{{ route('vip.redeem') }}" method="POST" class="p-4 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-100/60 dark:hover:bg-slate-800 transition">
                            @csrf
                            <input type="hidden" name="reward_type" value="energy_restore">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-950/80 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 text-base shadow-2xs border border-emerald-200 dark:border-emerald-800">
                                    <i class="fa-solid fa-bolt"></i>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-xs text-slate-900 dark:text-white">Direct Volledig Herstel</h4>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Vul je Energie en Gezondheid direct 100% aan zonder wachttijd.</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0 self-end sm:self-auto">
                                <span class="text-xs font-black text-purple-700 dark:text-purple-400 font-mono">10 Punten</span>
                                <button type="submit" @if(($character->vip_points ?? 0) < 10) disabled @endif
                                        class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-extrabold text-xs shadow-xs transition cursor-pointer">
                                    Claim
                                </button>
                            </div>
                        </form>

                        <!-- Option: Respect Boost (15 Punten) -->
                        <form action="{{ route('vip.redeem') }}" method="POST" class="p-4 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-100/60 dark:hover:bg-slate-800 transition">
                            @csrf
                            <input type="hidden" name="reward_type" value="respect_boost">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-950/80 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0 text-base shadow-2xs border border-purple-200 dark:border-purple-800">
                                    <i class="fa-solid fa-award"></i>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-xs text-slate-900 dark:text-white">+50 Kliniek Respect</h4>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Verhoog direct je respect en stijg op de algemene ranglijst van de kliniek.</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0 self-end sm:self-auto">
                                <span class="text-xs font-black text-purple-700 dark:text-purple-400 font-mono">15 Punten</span>
                                <button type="submit" @if(($character->vip_points ?? 0) < 15) disabled @endif
                                        class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-extrabold text-xs shadow-xs transition cursor-pointer">
                                    Claim
                                </button>
                            </div>
                        </form>

                        <!-- Option: VIP Buitpakket (25 Punten) -->
                        <form action="{{ route('vip.redeem') }}" method="POST" class="p-4 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-100/60 dark:hover:bg-slate-800 transition">
                            @csrf
                            <input type="hidden" name="reward_type" value="vip_lootbag">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-950/80 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 text-base shadow-2xs border border-amber-200 dark:border-amber-800">
                                    <i class="fa-solid fa-box-open"></i>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-xs text-slate-900 dark:text-white">VIP Buitpakket (2x XXL Case + €500.000)</h4>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Ontvang 2 gigantische XXL koffers en een half miljoen aan contant geld.</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0 self-end sm:self-auto">
                                <span class="text-xs font-black text-purple-700 dark:text-purple-400 font-mono">25 Punten</span>
                                <button type="submit" @if(($character->vip_points ?? 0) < 25) disabled @endif
                                        class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-extrabold text-xs shadow-xs transition cursor-pointer">
                                    Claim
                                </button>
                            </div>
                        </form>

                        <!-- Option: Stars Pack (30 Punten) -->
                        <form action="{{ route('vip.redeem') }}" method="POST" class="p-4 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-100/60 dark:hover:bg-slate-800 transition">
                            @csrf
                            <input type="hidden" name="reward_type" value="stars_pack">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-yellow-100 dark:bg-yellow-950/80 text-yellow-600 dark:text-yellow-400 flex items-center justify-center shrink-0 text-base shadow-2xs border border-yellow-200 dark:border-yellow-800">
                                    <i class="fa-solid fa-star"></i>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-xs text-slate-900 dark:text-white">100 Exclusieve Sterren</h4>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Direct 100 sterren voor bende capaciteit of zeldzame winkelitems.</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0 self-end sm:self-auto">
                                <span class="text-xs font-black text-purple-700 dark:text-purple-400 font-mono">30 Punten</span>
                                <button type="submit" @if(($character->vip_points ?? 0) < 30) disabled @endif
                                        class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-extrabold text-xs shadow-xs transition cursor-pointer">
                                    Claim
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
