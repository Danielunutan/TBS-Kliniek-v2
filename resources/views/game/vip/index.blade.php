@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ 
    activeTab: '{{ session('vip_tab', request('tab', 'pakketten')) }}',
    selectedColor: '{{ old('custom_name_color', $character->getCustomNameColor() ?: '#f59e0b') }}',
    sparkleEnabled: {{ $character->isSparkleActive() ? 'true' : 'false' }},
    setTab(tab) {
        this.activeTab = tab;
        try {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url.toString());
        } catch(e) {}
    }
}">

    <!-- Top Hero Banner (Rich Purple TBS Aesthetics) -->
    <div class="rounded-3xl shadow-xl p-6 sm:p-8 text-slate-800 dark:text-slate-100 relative overflow-hidden bg-gradient-to-br from-white via-purple-50/40 to-white dark:from-[#110726] dark:via-[#190a36] dark:to-[#0f0521] border border-purple-200/80 dark:border-purple-900/60">
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-purple-500/10 dark:bg-purple-600/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 w-64 h-64 bg-amber-500/10 dark:bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 bg-purple-100/80 dark:bg-purple-950/80 text-purple-800 dark:text-purple-200 text-xs font-black px-3.5 py-1.5 rounded-full border border-purple-200 dark:border-purple-800/80 shadow-2xs">
                    <i class="fa-solid fa-crown text-amber-500"></i>
                    <span>Officiële VIP Club & Donateurs Hub</span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-black tracking-tight text-slate-900 dark:text-white">
                    TBS V.I.P. & Supporter Lounge
                </h1>
                <p class="text-slate-600 dark:text-slate-300 text-xs sm:text-sm max-w-2xl leading-relaxed">
                    100% reclamevrij spelen! Steun het onderhoud, snelle servers en nieuwe wekelijkse updates van TBS Kliniek. Ontvang exclusieve Sterren, XXL Koffers, uurbonussen en unieke opvallende effecten op je naam!
                </p>

                <!-- Identifier Copy Badge -->
                <div class="pt-2 flex flex-wrap items-center gap-2">
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">Jouw koppelcode voor Buy Me a Coffee:</span>
                    <button type="button" 
                            onclick="navigator.clipboard.writeText('TBS-{{ $character->id }}'); alert('Koppelcode TBS-{{ $character->id }} gekopieerd naar klembord!');"
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-purple-600 text-white font-mono text-xs font-black hover:bg-purple-700 transition cursor-pointer shadow-xs">
                        <i class="fa-regular fa-copy text-[11px]"></i>
                        <span>TBS-{{ $character->id }}</span>
                        <span class="text-[10px] opacity-80">({{ $character->name }})</span>
                    </button>
                </div>
            </div>
            
            <!-- VIP Status Card -->
            <div class="shrink-0 bg-white/80 dark:bg-purple-950/50 backdrop-blur-md rounded-2xl p-5 border border-purple-200/80 dark:border-purple-800/60 shadow-lg flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl {{ $character->isVip() ? 'bg-gradient-to-br from-amber-400 to-amber-600 text-white ring-4 ring-amber-400/30' : 'bg-slate-100 dark:bg-purple-900/40 text-slate-400 border border-slate-200 dark:border-purple-800' }} flex items-center justify-center text-2xl font-black shadow-md">
                    <i class="fa-solid fa-crown"></i>
                </div>
                <div class="space-y-1">
                    <div class="text-[10px] uppercase font-black tracking-wider text-slate-400 dark:text-purple-300">Jouw Status</div>
                    <div class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                        @if($character->isVip())
                            <span class="text-amber-500 font-extrabold flex items-center gap-1">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
                                <span>Tier {{ $character->getSupporterTier() }} VIP</span>
                            </span>
                        @else
                            <span class="text-slate-500 dark:text-slate-400 text-sm">Reguliere Patiënt</span>
                        @endif
                    </div>
                    <div class="text-[11px] font-bold text-slate-500 dark:text-purple-300/80 flex items-center gap-2">
                        <span class="text-amber-600 dark:text-amber-400 font-black">{{ number_format($character->vip_points ?? 0, 0, ',', '.') }} VIP Punten</span>
                        <span>•</span>
                        <span class="text-purple-600 dark:text-purple-400 font-black">{{ $character->stars ?? 0 }} Sterren</span>
                    </div>
                    @if($character->isVip() && $character->vip_expires_at)
                        <div class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                            Actief tot {{ $character->vip_expires_at->format('d-m-Y H:i') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Main Grid: Navigation & Content -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left Navigation Sidebar -->
        <div class="lg:col-span-4 space-y-4">
            <div class="bg-white dark:bg-[#110726] border border-slate-200 dark:border-purple-900/60 rounded-2xl overflow-hidden shadow-xs p-2 space-y-1 text-xs font-bold">
                <!-- Tab 1: Pakketten & Lidmaatschappen -->
                <button type="button" @click="setTab('pakketten')" 
                        :class="activeTab === 'pakketten' ? 'bg-purple-600 text-white shadow-md' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-purple-950/60'"
                        class="w-full flex items-center justify-between p-3.5 rounded-xl transition cursor-pointer">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-crown text-amber-400 text-sm"></i>
                        <span>VIP Lidmaatschappen</span>
                    </span>
                    <i class="fa-solid fa-chevron-right text-[10px] opacity-70"></i>
                </button>

                <!-- Tab 2: Bakkie Koffie (Vrije Donaties) -->
                <button type="button" @click="setTab('koffie')" 
                        :class="activeTab === 'koffie' ? 'bg-purple-600 text-white shadow-md' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-purple-950/60'"
                        class="w-full flex items-center justify-between p-3.5 rounded-xl transition cursor-pointer">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-mug-hot text-amber-500 text-sm"></i>
                        <span>Bakkie Koffie (Steun de Server)</span>
                    </span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-400/20 text-amber-700 dark:text-amber-300 font-extrabold">+Sterren</span>
                </button>

                <!-- Tab 3: VIP Styling -->
                <button type="button" @click="setTab('styling')" 
                        :class="activeTab === 'styling' ? 'bg-purple-600 text-white shadow-md' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-purple-950/60'"
                        class="w-full flex items-center justify-between p-3.5 rounded-xl transition cursor-pointer">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-wand-magic-sparkles text-pink-500 text-sm"></i>
                        <span>Naamkleur & Glitter Sparkle</span>
                    </span>
                    @if($character->canCustomizeNameColor() || $character->canUseSparkleEffect())
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 font-extrabold">Beschikbaar</span>
                    @endif
                </button>

                <!-- Tab 4: VIP Punten Inwisselen -->
                <button type="button" @click="setTab('uitgeven')" 
                        :class="activeTab === 'uitgeven' ? 'bg-purple-600 text-white shadow-md' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-purple-950/60'"
                        class="w-full flex items-center justify-between p-3.5 rounded-xl transition cursor-pointer">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-gift text-purple-400 text-sm"></i>
                        <span>VIP Punten Inwisselen</span>
                    </span>
                    <span class="text-[10px] text-amber-500 font-mono">{{ $character->vip_points ?? 0 }} pts</span>
                </button>
            </div>

            <!-- Discord Quick Box -->
            <div class="bg-gradient-to-br from-[#5865F2]/10 via-[#5865F2]/5 to-transparent border border-[#5865F2]/30 dark:border-[#5865F2]/40 rounded-2xl p-5 shadow-xs text-center space-y-3 bg-white dark:bg-[#110726]">
                <div class="w-11 h-11 mx-auto rounded-full bg-[#5865F2] text-white flex items-center justify-center text-xl shadow-sm">
                    <i class="fa-brands fa-discord"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">Koppel je Discord</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Koppel eenmalig je account voor <strong>3 dagen gratis VIP</strong> & <strong>€2.500 cash</strong>!
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
                            <i class="fa-solid fa-circle-check mr-1"></i> Discord Bonus Geclaimd
                        </div>
                    @endif

                    <a href="https://discord.gg/tbs" target="_blank" rel="noopener noreferrer" 
                       class="block w-full py-2 px-4 rounded-xl bg-[#5865F2] hover:bg-[#4752c4] text-white font-extrabold text-xs shadow-xs transition active:scale-[0.99]">
                        <i class="fa-brands fa-discord mr-1.5"></i> Praat mee op Discord
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Content Column -->
        <div class="lg:col-span-8 space-y-6">

            <!-- TAB 1: VIP PAKKETTEN (BUY ME A COFFEE) -->
            <div x-show="activeTab === 'pakketten'" class="space-y-6">
                <!-- Info Notice -->
                <div class="bg-gradient-to-r from-purple-900/90 to-purple-800 rounded-2xl p-4 sm:p-5 text-white flex flex-col sm:flex-row items-center justify-between gap-4 shadow-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-xl shrink-0">
                            <i class="fa-solid fa-bolt text-amber-300"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-sm">Automatische In-Game Verwerking</h3>
                            <p class="text-xs text-purple-200 leading-relaxed">
                                Betalingen verlopen veilig via Buy Me a Coffee. Vermeld je in-game naam (<strong>{{ $character->name }}</strong>) of code <strong>TBS-{{ $character->id }}</strong> bij het afrekenen.
                            </p>
                        </div>
                    </div>
                    <a href="https://buymeacoffee.com/tbskliniek/membership" target="_blank" rel="noopener noreferrer"
                       class="px-5 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-purple-950 font-black text-xs uppercase tracking-wider shrink-0 transition shadow-md">
                        <i class="fa-solid fa-arrow-up-right-from-square mr-1"></i> Direct naar Pagina
                    </a>
                </div>

                <!-- 4 Live Packages Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    
                    <!-- Tier 1: TBS V.I.P. Lid (€6/mnd) -->
                    <div class="rounded-2xl overflow-hidden bg-white dark:bg-[#110726] border border-slate-200 dark:border-purple-900/60 shadow-lg flex flex-col justify-between group hover:border-purple-500 transition">
                        <div>
                            <div class="relative h-44 overflow-hidden bg-purple-950">
                                <img src="/images/tiers/tier1_vip.jpg" alt="TBS V.I.P. Lid" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                                <div class="absolute top-3 left-3 bg-purple-600/90 backdrop-blur-sm text-white text-[10px] font-black uppercase px-2.5 py-1 rounded-md">
                                    Tier 1 • Starter VIP
                                </div>
                                <div class="absolute bottom-3 left-3 right-3 flex items-end justify-between text-white">
                                    <div>
                                        <h3 class="font-black text-lg">TBS V.I.P. Lid</h3>
                                        <div class="text-xs text-purple-200">Ideaal om te starten</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xl font-black text-amber-400">€6</div>
                                        <div class="text-[10px] text-slate-300">per maand</div>
                                    </div>
                                </div>
                            </div>
                            <div class="p-5 space-y-3 text-xs">
                                <ul class="space-y-2 text-slate-600 dark:text-slate-300 font-medium">
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> <strong>100 Sterren</strong> direct op je saldo</li>
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> <strong>2x Maskers</strong> voor kluiskraak</li>
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> <strong>€2.500 Bankgeld</strong> elk uur op je rekening</li>
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> <strong>1.000 Gevechten per dag</strong> op de Luchtplaats</li>
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> <strong>50% Reiskorting</strong> & 3% Bankbelasting</li>
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> <strong>2x Dagelijks draaien</strong> aan het Rad</li>
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> Gouden VIP Kroon & Discord VIP rol</li>
                                </ul>
                            </div>
                        </div>
                        <div class="p-5 pt-0">
                            <a href="https://buymeacoffee.com/tbskliniek/membership" target="_blank" rel="noopener noreferrer"
                               class="w-full block text-center py-3 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs uppercase tracking-wider transition shadow-md">
                                Word V.I.P. Lid (€6/mnd)
                            </a>
                        </div>
                    </div>

                    <!-- Tier 2: TBS Gangster Elite (€14/mnd) -->
                    <div class="rounded-2xl overflow-hidden bg-white dark:bg-[#110726] border-2 border-purple-500 dark:border-purple-500 shadow-xl flex flex-col justify-between group relative">
                        <div class="absolute -top-3 right-4 z-10 bg-gradient-to-r from-amber-500 to-amber-600 text-purple-950 font-black text-[10px] uppercase px-3 py-0.5 rounded-full shadow-md">
                            Populair
                        </div>
                        <div>
                            <div class="relative h-44 overflow-hidden bg-purple-950">
                                <img src="/images/tiers/tier2_elite.jpg" alt="TBS Gangster Elite" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                                <div class="absolute top-3 left-3 bg-purple-600/90 backdrop-blur-sm text-white text-[10px] font-black uppercase px-2.5 py-1 rounded-md">
                                    Tier 2 • Gangster Elite
                                </div>
                                <div class="absolute bottom-3 left-3 right-3 flex items-end justify-between text-white">
                                    <div>
                                        <h3 class="font-black text-lg">Gangster Elite</h3>
                                        <div class="text-xs text-purple-200">Voor de echte bazen</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xl font-black text-amber-400">€14</div>
                                        <div class="text-[10px] text-slate-300">per maand</div>
                                    </div>
                                </div>
                            </div>
                            <div class="p-5 space-y-3 text-xs">
                                <ul class="space-y-2 text-slate-600 dark:text-slate-300 font-medium">
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> <strong>250 Sterren</strong> direct op je saldo</li>
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> <strong>1x XXL Zaak Kist</strong> met exclusieve buit</li>
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> <strong>5x Maskers</strong> voor kluiskraak</li>
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> <strong>Eigen Kleurnaam Kiezen</strong> op de hele site</li>
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> <strong>€7.500 Bankgeld</strong> elk uur op je rekening</li>
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> Alle voordelen van Tier 1 inbegrepen</li>
                                </ul>
                            </div>
                        </div>
                        <div class="p-5 pt-0">
                            <a href="https://buymeacoffee.com/tbskliniek/membership" target="_blank" rel="noopener noreferrer"
                               class="w-full block text-center py-3 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-black text-xs uppercase tracking-wider transition shadow-md">
                                Kies Gangster Elite (€14/mnd)
                            </a>
                        </div>
                    </div>

                    <!-- Tier 3: TBS Kliniek Baas (€24/mnd) -->
                    <div class="rounded-2xl overflow-hidden bg-white dark:bg-[#110726] border border-slate-200 dark:border-purple-900/60 shadow-lg flex flex-col justify-between group hover:border-purple-500 transition">
                        <div>
                            <div class="relative h-44 overflow-hidden bg-purple-950">
                                <img src="/images/tiers/tier3_baas.jpg" alt="TBS Kliniek Baas" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                                <div class="absolute top-3 left-3 bg-purple-600/90 backdrop-blur-sm text-white text-[10px] font-black uppercase px-2.5 py-1 rounded-md">
                                    Tier 3 • Kliniek Baas
                                </div>
                                <div class="absolute bottom-3 left-3 right-3 flex items-end justify-between text-white">
                                    <div>
                                        <h3 class="font-black text-lg">TBS Kliniek Baas</h3>
                                        <div class="text-xs text-purple-200">De absolute top</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xl font-black text-amber-400">€24</div>
                                        <div class="text-[10px] text-slate-300">per maand</div>
                                    </div>
                                </div>
                            </div>
                            <div class="p-5 space-y-3 text-xs">
                                <ul class="space-y-2 text-slate-600 dark:text-slate-300 font-medium">
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> <strong>600 Sterren</strong> direct op je saldo</li>
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> <strong>3x XXL Zaak Kisten</strong> met megabuit</li>
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> <strong>12x Maskers</strong> voor kluiskraak</li>
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> <strong>Eigen Kleurnaam + Glitter Sparkle</strong> effect</li>
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> <strong>€20.000 Bankgeld</strong> elk uur op je rekening</li>
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> Exclusieve Baas Badge & VIP Chat status</li>
                                </ul>
                            </div>
                        </div>
                        <div class="p-5 pt-0">
                            <a href="https://buymeacoffee.com/tbskliniek/membership" target="_blank" rel="noopener noreferrer"
                               class="w-full block text-center py-3 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs uppercase tracking-wider transition shadow-md">
                                Word Kliniek Baas (€24/mnd)
                            </a>
                        </div>
                    </div>

                    <!-- Tier 4: VIP LIFETIME (€125 eenmalig) -->
                    <div class="rounded-2xl overflow-hidden bg-gradient-to-br from-amber-500/10 via-purple-950/20 to-purple-950/40 dark:from-[#211105] dark:via-[#190a36] dark:to-[#0f0521] border-2 border-amber-400/80 dark:border-amber-500/60 shadow-xl flex flex-col justify-between group">
                        <div>
                            <div class="p-6 pb-4 border-b border-amber-400/20">
                                <div class="flex items-center justify-between">
                                    <span class="px-3 py-1 rounded-full bg-amber-400 text-purple-950 font-black text-[10px] uppercase tracking-wider shadow-xs">
                                        Levenslang & Nooit Verlengen
                                    </span>
                                    <i class="fa-solid fa-gem text-amber-400 text-2xl"></i>
                                </div>
                                <h3 class="font-black text-2xl text-slate-900 dark:text-white mt-3">VIP LIFETIME</h3>
                                <p class="text-xs text-amber-600 dark:text-amber-300 font-bold mt-0.5">Eén keer betalen, voor altijd VIP</p>
                                <div class="mt-4 flex items-baseline gap-2">
                                    <span class="text-3xl font-black text-slate-900 dark:text-white">€125</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400 font-bold">eenmalig</span>
                                </div>
                            </div>
                            <div class="p-6 space-y-3 text-xs">
                                <ul class="space-y-2.5 text-slate-700 dark:text-slate-200 font-bold">
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-crown text-amber-400"></i> <strong>Voor Altijd VIP (99 Jaar)</strong></li>
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> <strong>1.500 Sterren</strong> direct toegekend</li>
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> <strong>10x XXL Zaak Kisten</strong> direct in bezit</li>
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> <strong>30x Maskers</strong> voor kluiskraak</li>
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> <strong>€50.000 Bankgeld</strong> elk uur op je rekening</li>
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> Alle Kleurnaam & Glitter Sparkle opties</li>
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> Exclusieve Lifetime Legend Badge op je dossier</li>
                                </ul>
                            </div>
                        </div>
                        <div class="p-6 pt-0">
                            <a href="https://buymeacoffee.com/tbskliniek/membership" target="_blank" rel="noopener noreferrer"
                               class="w-full block text-center py-3.5 rounded-xl bg-gradient-to-r from-amber-500 via-amber-400 to-amber-500 text-purple-950 font-black text-xs uppercase tracking-wider hover:brightness-110 transition shadow-lg">
                                Neem Lifetime VIP (€125)
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            <!-- TAB 2: BAKKIE KOFFIE (DONATIES) -->
            <div x-show="activeTab === 'koffie'" class="space-y-6">
                <div class="bg-white dark:bg-[#110726] border border-slate-200 dark:border-purple-900/60 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
                    <div class="border-b border-slate-100 dark:border-purple-900/60 pb-5">
                        <div class="inline-flex items-center gap-2 bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-200 text-xs font-black px-3 py-1 rounded-full border border-amber-200 dark:border-amber-800/60 mb-2">
                            <i class="fa-solid fa-mug-hot text-amber-500"></i>
                            <span>Vrijwillige Donaties & Server Support</span>
                        </div>
                        <h2 class="text-2xl font-black text-slate-900 dark:text-white">Trakteer op een Bakkie Koffie</h2>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-1 max-w-2xl leading-relaxed">
                            Wil je geen maandelijks abonnement maar wel de server steunen en direct extra Sterren, Maskers en Kogels ontvangen? Trakteer het ontwikkelteam op een bakkie koffie via Buy Me a Coffee!
                        </p>
                    </div>

                    <!-- Coffee Reward Options Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- 1 Coffee -->
                        <div class="p-5 rounded-2xl bg-purple-50/50 dark:bg-purple-950/40 border border-purple-200/80 dark:border-purple-800/60 text-center space-y-3 flex flex-col justify-between">
                            <div class="space-y-2">
                                <div class="text-3xl">☕</div>
                                <h3 class="font-black text-base text-slate-900 dark:text-white">1x Bakkie Koffie</h3>
                                <div class="text-lg font-black text-amber-600 dark:text-amber-400">€3,00</div>
                                <ul class="text-xs text-slate-600 dark:text-slate-300 space-y-1.5 text-left pt-2 font-medium">
                                    <li class="flex items-center gap-1.5"><i class="fa-solid fa-star text-amber-500 text-[10px]"></i> <strong>+25 Sterren</strong></li>
                                    <li class="flex items-center gap-1.5"><i class="fa-solid fa-mask text-purple-500 text-[10px]"></i> <strong>+1 Masker</strong></li>
                                    <li class="flex items-center gap-1.5"><i class="fa-solid fa-circle-dot text-rose-500 text-[10px]"></i> <strong>+50 Kogels</strong></li>
                                    <li class="flex items-center gap-1.5"><i class="fa-solid fa-heart text-pink-500 text-[10px]"></i> Donateur Badge</li>
                                </ul>
                            </div>
                            <a href="https://buymeacoffee.com/tbskliniek" target="_blank" rel="noopener noreferrer"
                               class="block w-full py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs shadow-xs transition">
                                Doneer 1 Koffie (€3)
                            </a>
                        </div>

                        <!-- 3 Coffees -->
                        <div class="p-5 rounded-2xl bg-amber-50/50 dark:bg-amber-950/30 border-2 border-amber-400 dark:border-amber-500/60 text-center space-y-3 flex flex-col justify-between relative shadow-md">
                            <div class="absolute -top-3 right-4 bg-amber-500 text-purple-950 font-black text-[9px] uppercase px-2.5 py-0.5 rounded-full">
                                Populairste Koffie
                            </div>
                            <div class="space-y-2">
                                <div class="text-3xl">☕☕☕</div>
                                <h3 class="font-black text-base text-slate-900 dark:text-white">3x Bakkie Koffie</h3>
                                <div class="text-lg font-black text-amber-600 dark:text-amber-400">€9,00</div>
                                <ul class="text-xs text-slate-600 dark:text-slate-300 space-y-1.5 text-left pt-2 font-medium">
                                    <li class="flex items-center gap-1.5"><i class="fa-solid fa-star text-amber-500 text-[10px]"></i> <strong>+85 Sterren</strong> (+10 bonus!)</li>
                                    <li class="flex items-center gap-1.5"><i class="fa-solid fa-mask text-purple-500 text-[10px]"></i> <strong>+3 Maskers</strong></li>
                                    <li class="flex items-center gap-1.5"><i class="fa-solid fa-briefcase text-indigo-500 text-[10px]"></i> <strong>1x XXL Zaak Kist</strong></li>
                                    <li class="flex items-center gap-1.5"><i class="fa-solid fa-circle-dot text-rose-500 text-[10px]"></i> <strong>+200 Kogels</strong></li>
                                </ul>
                            </div>
                            <a href="https://buymeacoffee.com/tbskliniek" target="_blank" rel="noopener noreferrer"
                               class="block w-full py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-purple-950 font-black text-xs shadow-xs transition">
                                Doneer 3 Koffies (€9)
                            </a>
                        </div>

                        <!-- 5 Coffees -->
                        <div class="p-5 rounded-2xl bg-purple-50/50 dark:bg-purple-950/40 border border-purple-200/80 dark:border-purple-800/60 text-center space-y-3 flex flex-col justify-between">
                            <div class="space-y-2">
                                <div class="text-3xl">☕☕☕☕☕</div>
                                <h3 class="font-black text-base text-slate-900 dark:text-white">5x Bakkie Koffie</h3>
                                <div class="text-lg font-black text-amber-600 dark:text-amber-400">€15,00</div>
                                <ul class="text-xs text-slate-600 dark:text-slate-300 space-y-1.5 text-left pt-2 font-medium">
                                    <li class="flex items-center gap-1.5"><i class="fa-solid fa-star text-amber-500 text-[10px]"></i> <strong>+160 Sterren</strong> (+35 bonus!)</li>
                                    <li class="flex items-center gap-1.5"><i class="fa-solid fa-mask text-purple-500 text-[10px]"></i> <strong>+6 Maskers</strong></li>
                                    <li class="flex items-center gap-1.5"><i class="fa-solid fa-briefcase text-indigo-500 text-[10px]"></i> <strong>2x XXL Zaak Kisten</strong></li>
                                    <li class="flex items-center gap-1.5"><i class="fa-solid fa-circle-dot text-rose-500 text-[10px]"></i> <strong>+500 Kogels</strong></li>
                                </ul>
                            </div>
                            <a href="https://buymeacoffee.com/tbskliniek" target="_blank" rel="noopener noreferrer"
                               class="block w-full py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs shadow-xs transition">
                                Doneer 5 Koffies (€15)
                            </a>
                        </div>
                    </div>

                    <!-- Identifier Reminder Box -->
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-purple-950/50 border border-slate-200 dark:border-purple-800/60 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-circle-info text-purple-600 dark:text-purple-400 text-lg shrink-0"></i>
                            <div class="text-xs text-slate-600 dark:text-slate-300">
                                Voer in het donatiebericht je spelersnaam <strong>{{ $character->name }}</strong> of code <strong>TBS-{{ $character->id }}</strong> in. Onze webhook koppelt de sterren en beloningen direct aan jouw account!
                            </div>
                        </div>
                        <a href="https://buymeacoffee.com/tbskliniek" target="_blank" rel="noopener noreferrer"
                           class="px-4 py-2 rounded-xl bg-amber-400 hover:bg-amber-300 text-purple-950 font-black text-xs shrink-0 transition">
                            Naar Buy Me a Coffee
                        </a>
                    </div>
                </div>
            </div>

            <!-- TAB 3: VIP STYLING (NAAMKLEUR & GLITTER) -->
            <div x-show="activeTab === 'styling'" class="space-y-6">
                <div class="bg-white dark:bg-[#110726] border border-slate-200 dark:border-purple-900/60 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
                    <div class="border-b border-slate-100 dark:border-purple-900/60 pb-5">
                        <div class="inline-flex items-center gap-2 bg-pink-50 dark:bg-pink-950/60 text-pink-800 dark:text-pink-200 text-xs font-black px-3 py-1 rounded-full border border-pink-200 dark:border-pink-800/60 mb-2">
                            <i class="fa-solid fa-wand-magic-sparkles text-pink-500"></i>
                            <span>V.I.P. Karakter Personalisatie</span>
                        </div>
                        <h2 class="text-2xl font-black text-slate-900 dark:text-white">Naamkleur & Glitter Sparkle</h2>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-1 max-w-2xl leading-relaxed">
                            Val direct op in de chat, op het plein, in de bende en op de ranglijst met je eigen unieke naamkleur of glittereffect!
                        </p>
                    </div>

                    <!-- Live Name Preview Box -->
                    <div class="p-6 rounded-2xl bg-gradient-to-r from-slate-900 to-purple-950 border border-purple-800 text-center space-y-2">
                        <div class="text-[10px] uppercase tracking-widest font-black text-purple-300">Live Voorbeeld van Jouw Naam</div>
                        <div class="text-2xl sm:text-3xl font-black py-2">
                            <span :class="sparkleEnabled ? 'vip-sparkle-name' : ''" 
                                  :style="!sparkleEnabled && selectedColor ? 'color: ' + selectedColor : ''">
                                {{ $character->name }}
                            </span>
                        </div>
                        <div class="text-[11px] text-slate-400 font-medium">Zo wordt je naam getoond in de chat en door de hele game.</div>
                    </div>

                    @if($character->canCustomizeNameColor() || $character->canUseSparkleEffect())
                        <form method="POST" action="{{ route('vip.styling') }}" class="space-y-6">
                            @csrf

                            <!-- Color Chooser (Tier 2+) -->
                            <div class="space-y-3">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                    1. Kies Jouw Exclusieve Naamkleur
                                </label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    @php
                                        $colors = [
                                            ['hex' => '#f59e0b', 'name' => 'Goudgeel', 'class' => 'bg-amber-500'],
                                            ['hex' => '#a855f7', 'name' => 'TBS Neon Paars', 'class' => 'bg-purple-500'],
                                            ['hex' => '#10b981', 'name' => 'Smaragd Groen', 'class' => 'bg-emerald-500'],
                                            ['hex' => '#ef4444', 'name' => 'Bloedrood', 'class' => 'bg-rose-500'],
                                            ['hex' => '#06b6d4', 'name' => 'Cyber Cyaan', 'class' => 'bg-cyan-500'],
                                            ['hex' => '#ec4899', 'name' => 'Neon Roze', 'class' => 'bg-pink-500'],
                                            ['hex' => '#3b82f6', 'name' => 'Elektrisch Blauw', 'class' => 'bg-blue-500'],
                                            ['hex' => '#e2e8f0', 'name' => 'Platina Wit', 'class' => 'bg-slate-200'],
                                        ];
                                    @endphp
                                    @foreach($colors as $color)
                                        <label class="p-3 rounded-xl border-2 cursor-pointer flex items-center gap-2.5 transition"
                                               :class="selectedColor === '{{ $color['hex'] }}' ? 'border-purple-600 bg-purple-50 dark:bg-purple-950/60 shadow-sm' : 'border-slate-200 dark:border-purple-900/60 hover:bg-slate-50 dark:hover:bg-purple-950/30'">
                                            <input type="radio" name="custom_name_color" value="{{ $color['hex'] }}" x-model="selectedColor" class="sr-only">
                                            <span class="w-4 h-4 rounded-full {{ $color['class'] }} ring-1 ring-black/20 shrink-0"></span>
                                            <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 truncate">{{ $color['name'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Sparkle Toggle (Tier 3+) -->
                            <div class="space-y-3 pt-4 border-t border-slate-100 dark:border-purple-900/60">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                    2. Glitter Sparkle Shimmer Effect
                                </label>
                                @if($character->canUseSparkleEffect())
                                    <div class="p-4 rounded-2xl bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800 flex items-center justify-between gap-4">
                                        <div class="space-y-1">
                                            <div class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                                                <i class="fa-solid fa-sparkles text-pink-500"></i>
                                                <span>Geanimeerde Regenboog & Glitter Gloed</span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                                Activeer een levendig bewegend glans- en glittereffect op je naam (exclusief voor Tier 3 en Lifetime).
                                            </p>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                            <input type="checkbox" name="sparkle_enabled" value="1" x-model="sparkleEnabled" class="sr-only peer">
                                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                                        </label>
                                    </div>
                                @else
                                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-purple-950/20 border border-slate-200 dark:border-purple-900/40 text-xs text-slate-500 dark:text-slate-400 flex items-center gap-3">
                                        <i class="fa-solid fa-lock text-amber-500 text-base shrink-0"></i>
                                        <span>Glitter Sparkle is exclusief beschikbaar voor <strong>Tier 3 (TBS Kliniek Baas)</strong> en <strong>VIP LIFETIME</strong> donateurs.</span>
                                    </div>
                                @endif
                            </div>

                            <button type="submit" 
                                    class="w-full py-3.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs uppercase tracking-wider transition shadow-md">
                                Instellingen Opslaan
                            </button>
                        </form>
                    @else
                        <div class="p-6 rounded-2xl bg-slate-50 dark:bg-purple-950/30 border border-slate-200 dark:border-purple-900/60 text-center space-y-3">
                            <div class="w-12 h-12 mx-auto rounded-full bg-purple-100 dark:bg-purple-900/60 text-purple-600 dark:text-purple-300 flex items-center justify-center text-xl">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <h3 class="font-extrabold text-sm text-slate-900 dark:text-white">Nog Niet Beschikbaar</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                                Je hebt minimaal een <strong>Tier 2 (Gangster Elite)</strong> of hoger VIP lidmaatschap nodig om je naamkleur aan te passen.
                            </p>
                            <button type="button" @click="setTab('pakketten')" 
                                    class="px-5 py-2.5 rounded-xl bg-purple-600 text-white font-extrabold text-xs shadow-xs hover:bg-purple-700 transition">
                                Bekijk VIP Pakketten
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- TAB 4: VIP PUNTEN INWISSELEN -->
            <div x-show="activeTab === 'uitgeven'" class="space-y-6">
                <div class="bg-white dark:bg-[#110726] border border-slate-200 dark:border-purple-900/60 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
                    <div class="border-b border-slate-100 dark:border-purple-900/60 pb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <div class="inline-flex items-center gap-2 bg-purple-50 dark:bg-purple-950/60 text-purple-800 dark:text-purple-200 text-xs font-black px-3 py-1 rounded-full border border-purple-200 dark:border-purple-800/60 mb-2">
                                <i class="fa-solid fa-gift text-purple-500"></i>
                                <span>Verdiend met Actief Spelen</span>
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 dark:text-white">VIP Punten Inwisselen</h2>
                            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-1">
                                Elk uur dat je actief speelt als VIP lid spaar je <strong>+5 VIP Punten</strong>. Wissel ze hier in!
                            </p>
                        </div>
                        <div class="shrink-0 text-left sm:text-right">
                            <span class="text-xs text-slate-400 font-bold block">Beschikbaar Saldo</span>
                            <span class="text-xl font-black text-amber-600 dark:text-amber-400 font-mono">{{ number_format($character->vip_points ?? 0, 0, ',', '.') }} Punten</span>
                        </div>
                    </div>

                    <!-- Rewards Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @php
                            $redeemOptions = [
                                [
                                    'type' => 'cash_protection',
                                    'name' => '24 Uur Cash Bescherming',
                                    'cost' => 20,
                                    'desc' => 'Je contante geld is 24 uur beschermd tegen diefstal bij aanvallen.',
                                    'icon' => 'fa-shield-halved',
                                    'iconColor' => 'text-emerald-500 bg-emerald-50 dark:bg-emerald-950/60',
                                ],
                                [
                                    'type' => 'energy_restore',
                                    'name' => 'Direct Volledig Herstel',
                                    'cost' => 10,
                                    'desc' => '100% Energie en Gezondheid direct maximaal hersteld.',
                                    'icon' => 'fa-bolt',
                                    'iconColor' => 'text-amber-500 bg-amber-50 dark:bg-amber-950/60',
                                ],
                                [
                                    'type' => 'respect_boost',
                                    'name' => '+50 Kliniek Respect',
                                    'cost' => 15,
                                    'desc' => 'Direct +50 extra respect op je dossier.',
                                    'icon' => 'fa-fire',
                                    'iconColor' => 'text-rose-500 bg-rose-50 dark:bg-rose-950/60',
                                ],
                                [
                                    'type' => 'vip_extend_24h',
                                    'name' => '+24 Uur VIP Status',
                                    'cost' => 20,
                                    'desc' => 'Verleng je huidige VIP status direct met 24 uur.',
                                    'icon' => 'fa-crown',
                                    'iconColor' => 'text-purple-500 bg-purple-50 dark:bg-purple-950/60',
                                ],
                                [
                                    'type' => 'vip_lootbag',
                                    'name' => 'VIP Buitpakket (XXL Cases + €500.000)',
                                    'cost' => 25,
                                    'desc' => '2x XXL Zaak Kisten en €500.000 contant geld.',
                                    'icon' => 'fa-briefcase',
                                    'iconColor' => 'text-indigo-500 bg-indigo-50 dark:bg-indigo-950/60',
                                ],
                                [
                                    'type' => 'stars_pack',
                                    'name' => '100 Exclusieve Sterren',
                                    'cost' => 30,
                                    'desc' => 'Direct +100 sterren voor winkelaankopen en upgrades.',
                                    'icon' => 'fa-star',
                                    'iconColor' => 'text-amber-500 bg-amber-50 dark:bg-amber-950/60',
                                ],
                            ];
                        @endphp

                        @foreach($redeemOptions as $opt)
                            <div class="p-5 rounded-2xl bg-slate-50 dark:bg-purple-950/40 border border-slate-200 dark:border-purple-800/60 flex flex-col justify-between gap-4">
                                <div class="flex items-start gap-3.5">
                                    <div class="w-10 h-10 rounded-xl {{ $opt['iconColor'] }} flex items-center justify-center text-lg shrink-0">
                                        <i class="fa-solid {{ $opt['icon'] }}"></i>
                                    </div>
                                    <div class="space-y-1">
                                        <h4 class="font-extrabold text-sm text-slate-900 dark:text-white">{{ $opt['name'] }}</h4>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">{{ $opt['desc'] }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-2 border-t border-slate-200/60 dark:border-purple-900/60">
                                    <span class="text-xs font-black text-amber-600 dark:text-amber-400 flex items-center gap-1 font-mono">
                                        <span>{{ $opt['cost'] }} VIP Punten</span>
                                    </span>
                                    <form method="POST" action="{{ route('vip.redeem') }}">
                                        @csrf
                                        <input type="hidden" name="reward_type" value="{{ $opt['type'] }}">
                                        <button type="submit" 
                                                {{ ($character->vip_points ?? 0) < $opt['cost'] ? 'disabled' : '' }}
                                                class="px-4 py-1.5 rounded-lg text-xs font-black transition {{ ($character->vip_points ?? 0) >= $opt['cost'] ? 'bg-purple-600 hover:bg-purple-700 text-white shadow-xs cursor-pointer' : 'bg-slate-200 dark:bg-purple-950/80 text-slate-400 cursor-not-allowed' }}">
                                            Inwisselen
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
