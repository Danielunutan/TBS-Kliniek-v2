@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'kopen' }">

    <!-- Clean TBS Kliniek Top Banner -->
    <div class="rounded-2xl shadow-xs p-6 sm:p-8 text-slate-800 relative overflow-hidden bg-white border border-slate-200">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 bg-amber-50 text-amber-800 text-xs font-bold px-3 py-1 rounded-full border border-amber-200 mb-2">
                    <i class="fa-solid fa-crown text-amber-500"></i> Exclusief Lidmaatschap
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">TBS VIP Lounge & Punten Shop</h1>
                <p class="text-slate-500 text-xs sm:text-sm mt-1 max-w-xl leading-relaxed">
                    Word VIP lid via onze Discord voor <strong>€7,50 per maand</strong>. Ontvang elk actief speeluur <strong>5 VIP Punten</strong> en wissel ze in voor unieke bonussen en privileges!
                </p>
            </div>
            
            <!-- VIP Status Card -->
            <div class="shrink-0 bg-slate-50 rounded-2xl p-4 border border-slate-200 flex items-center gap-4 shadow-2xs">
                <div class="w-12 h-12 rounded-xl {{ $character->isVip() ? 'bg-amber-100 text-amber-600 border border-amber-200' : 'bg-slate-100 text-slate-400 border border-slate-200' }} flex items-center justify-center text-xl font-black shadow-xs">
                    <i class="fa-solid fa-crown"></i>
                </div>
                <div>
                    <div class="text-[10px] uppercase font-bold text-slate-400">Jouw VIP Saldo</div>
                    <div class="text-xl font-black text-amber-600 font-mono flex items-center gap-1.5">
                        <span>{{ number_format($character->vip_points ?? 0, 0, ',', '.') }}</span>
                        <span class="text-xs font-bold text-slate-600">Punten</span>
                    </div>
                    <div class="text-[10px] font-bold mt-0.5">
                        @if($character->isVip())
                            <span class="text-emerald-600 font-black flex items-center gap-1">
                                <i class="fa-solid fa-circle-check"></i> Actief VIP Lid
                            </span>
                        @else
                            <span class="text-slate-400">Nog geen actief VIP lid</span>
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
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs p-2 space-y-1 text-xs font-bold">
                <button type="button" @click="activeTab = 'kopen'" 
                        :class="activeTab === 'kopen' ? 'bg-amber-50 text-amber-800 border-amber-200 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 border-transparent'"
                        class="w-full flex items-center justify-between p-3.5 rounded-xl border transition cursor-pointer">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-crown text-amber-500 text-sm"></i>
                        <span>VIP Worden (€7,50 / mnd)</span>
                    </span>
                    <i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i>
                </button>

                <button type="button" @click="activeTab = 'uitgeven'" 
                        :class="activeTab === 'uitgeven' ? 'bg-purple-50 text-purple-700 border-purple-200 shadow-2xs' : 'text-slate-600 hover:bg-slate-50 border-transparent'"
                        class="w-full flex items-center justify-between p-3.5 rounded-xl border transition cursor-pointer">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-gift text-purple-600 text-sm"></i>
                        <span>VIP Punten Inwisselen</span>
                    </span>
                    <i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i>
                </button>
            </div>

            <!-- Discord Quick Box -->
            <div class="bg-gradient-to-br from-[#5865F2]/10 to-[#5865F2]/5 border border-[#5865F2]/30 rounded-2xl p-5 shadow-xs text-center space-y-3">
                <div class="w-10 h-10 mx-auto rounded-full bg-[#5865F2] text-white flex items-center justify-center text-lg shadow-sm">
                    <i class="fa-brands fa-discord"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Officiële Discord Server</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Meld je aan voor VIP, giveaways en bende-gesprekken.</p>
                </div>
                <a href="https://discord.gg/tbs" target="_blank" rel="noopener noreferrer" 
                   class="block w-full py-2.5 px-4 rounded-xl bg-[#5865F2] hover:bg-[#4752c4] text-white font-extrabold text-xs shadow-xs transition active:scale-[0.99]">
                    <i class="fa-brands fa-discord mr-1.5"></i> Open discord.gg/tbs
                </a>
            </div>
        </div>

        <!-- Right Content Column -->
        <div class="lg:col-span-8">
            
            <!-- TAB 1: VIP KOPEN VIA DISCORD -->
            <div x-show="activeTab === 'kopen'" class="space-y-6">
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-black uppercase tracking-wider mb-2">
                            VIP Lidmaatschap
                        </span>
                        <h2 class="text-xl font-black text-slate-900">Word VIP voor €7,50 per Maand</h2>
                        <p class="text-xs text-slate-500 mt-1">
                            Steun de doorontwikkeling van TBS Kliniek en ontvang exclusieve voordelen, uurbeloningen en aanzien in de hele inrichting!
                        </p>
                    </div>

                    <!-- Steps to Purchase -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-center space-y-2">
                            <div class="w-8 h-8 mx-auto rounded-full bg-purple-600 text-white flex items-center justify-center font-black text-xs">
                                1
                            </div>
                            <h4 class="font-extrabold text-xs text-slate-900">Join Discord</h4>
                            <p class="text-[11px] text-slate-500">Ga naar <a href="https://discord.gg/tbs" target="_blank" class="text-[#5865F2] font-bold underline">discord.gg/tbs</a> en sluit je aan bij de community.</p>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-center space-y-2">
                            <div class="w-8 h-8 mx-auto rounded-full bg-purple-600 text-white flex items-center justify-center font-black text-xs">
                                2
                            </div>
                            <h4 class="font-extrabold text-xs text-slate-900">Maak een Ticket</h4>
                            <p class="text-[11px] text-slate-500">Open een ticket in het kanaal <strong>#vip-tickets</strong> of <strong>#support</strong>.</p>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-center space-y-2">
                            <div class="w-8 h-8 mx-auto rounded-full bg-purple-600 text-white flex items-center justify-center font-black text-xs">
                                3
                            </div>
                            <h4 class="font-extrabold text-xs text-slate-900">Betaal €7,50 & Speel</h4>
                            <p class="text-[11px] text-slate-500">Rond de betaling af via een betaalverzoek en ontvang direct je VIP status & uurbeloning!</p>
                        </div>
                    </div>

                    <!-- VIP Perks List -->
                    <div class="p-5 rounded-2xl bg-amber-50/70 border border-amber-200 space-y-3">
                        <h3 class="text-xs font-black uppercase tracking-wider text-amber-900 flex items-center gap-2">
                            <i class="fa-solid fa-crown text-amber-600"></i>
                            <span>Wat krijg je als VIP lid?</span>
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            <div class="flex items-start gap-2.5 text-slate-700">
                                <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5"></i>
                                <span><strong>+5 VIP Punten</strong> per uur dat je actief inlogt en speelt!</span>
                            </div>
                            <div class="flex items-start gap-2.5 text-slate-700">
                                <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5"></i>
                                <span>Exclusieve <strong>Gouden VIP Kroon</strong> op je dossier en ranglijsten.</span>
                            </div>
                            <div class="flex items-start gap-2.5 text-slate-700">
                                <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5"></i>
                                <span>Toegang tot de <strong>VIP Punten Shop</strong> voor gratis herstel & koffers.</span>
                            </div>
                            <div class="flex items-start gap-2.5 text-slate-700">
                                <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5"></i>
                                <span>Voorrang bij support en inspraak op updates in de Discord lounge.</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-center pt-2">
                        <a href="https://discord.gg/tbs" target="_blank" rel="noopener noreferrer" 
                           class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs uppercase tracking-wider shadow-sm transition active:scale-[0.99]">
                            <i class="fa-brands fa-discord text-sm"></i>
                            <span>Ticket Aanmaken op discord.gg/tbs (€7,50 pm)</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- TAB 2: VIP PUNTEN INWISSELEN -->
            <div x-show="activeTab === 'uitgeven'" class="space-y-6">
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-purple-50 text-purple-700 border border-purple-200 text-[10px] font-black uppercase tracking-wider mb-2">
                            Beloningen Winkel
                        </span>
                        <h2 class="text-xl font-black text-slate-900">VIP Punten Inwisselen</h2>
                        <p class="text-xs text-slate-500 mt-1">
                            Wissel je verdiende VIP punten in voor krachtige in-game voordelen. Punten worden direct afgeschreven van je saldo.
                        </p>
                    </div>

                    <div class="space-y-3.5">
                        <!-- Option 1: 24h Cash Protection -->
                        <form action="{{ route('vip.redeem') }}" method="POST" class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-100/60 transition">
                            @csrf
                            <input type="hidden" name="reward_type" value="cash_protection">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0 text-base shadow-2xs">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-xs text-slate-900">24 Uur Cash Bescherming</h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Bescherm je contante geld 24 uur tegen plunderingen in gevechten.</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0 self-end sm:self-auto">
                                <span class="text-xs font-black text-purple-700 font-mono">20 Punten</span>
                                <button type="submit" @if(($character->vip_points ?? 0) < 20) disabled @endif
                                        class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-extrabold text-xs shadow-xs transition cursor-pointer">
                                    Claim
                                </button>
                            </div>
                        </form>

                        <!-- Option 2: Full Health & Energy -->
                        <form action="{{ route('vip.redeem') }}" method="POST" class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-100/60 transition">
                            @csrf
                            <input type="hidden" name="reward_type" value="energy_restore">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 text-base shadow-2xs">
                                    <i class="fa-solid fa-bolt"></i>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-xs text-slate-900">Direct Volledig Herstel</h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Vul je Energie en Gezondheid direct 100% aan zonder te wachten.</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0 self-end sm:self-auto">
                                <span class="text-xs font-black text-purple-700 font-mono">10 Punten</span>
                                <button type="submit" @if(($character->vip_points ?? 0) < 10) disabled @endif
                                        class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-extrabold text-xs shadow-xs transition cursor-pointer">
                                    Claim
                                </button>
                            </div>
                        </form>

                        <!-- Option 3: Respect Boost -->
                        <form action="{{ route('vip.redeem') }}" method="POST" class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-100/60 transition">
                            @csrf
                            <input type="hidden" name="reward_type" value="respect_boost">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center shrink-0 text-base shadow-2xs">
                                    <i class="fa-solid fa-award"></i>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-xs text-slate-900">+50 Kliniek Respect</h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Verhoog direct je respect en stijg op de ranglijst van de kliniek.</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0 self-end sm:self-auto">
                                <span class="text-xs font-black text-purple-700 font-mono">15 Punten</span>
                                <button type="submit" @if(($character->vip_points ?? 0) < 15) disabled @endif
                                        class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-extrabold text-xs shadow-xs transition cursor-pointer">
                                    Claim
                                </button>
                            </div>
                        </form>

                        <!-- Option 4: VIP Buitpakket -->
                        <form action="{{ route('vip.redeem') }}" method="POST" class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-100/60 transition">
                            @csrf
                            <input type="hidden" name="reward_type" value="vip_lootbag">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 text-base shadow-2xs">
                                    <i class="fa-solid fa-box-open"></i>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-xs text-slate-900">VIP Buitpakket (2x XXL Case + €500.000)</h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Ontvang 2 gigantische XXL koffers en een half miljoen aan contant geld.</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0 self-end sm:self-auto">
                                <span class="text-xs font-black text-purple-700 font-mono">25 Punten</span>
                                <button type="submit" @if(($character->vip_points ?? 0) < 25) disabled @endif
                                        class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-extrabold text-xs shadow-xs transition cursor-pointer">
                                    Claim
                                </button>
                            </div>
                        </form>

                        <!-- Option 5: Stars Pack -->
                        <form action="{{ route('vip.redeem') }}" method="POST" class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-100/60 transition">
                            @csrf
                            <input type="hidden" name="reward_type" value="stars_pack">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-yellow-100 text-yellow-600 flex items-center justify-center shrink-0 text-base shadow-2xs">
                                    <i class="fa-solid fa-star"></i>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-xs text-slate-900">100 Exclusieve Sterren</h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Direct 100 sterren voor bende capaciteit of zeldzame winkelitems.</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0 self-end sm:self-auto">
                                <span class="text-xs font-black text-purple-700 font-mono">30 Punten</span>
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
