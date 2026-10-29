@extends('layouts.app')

@section('content')
@php
    $vipColorCatalog = \App\Models\Character::nameColorCatalog();
    $vipGradientCatalog = \App\Models\Character::nameGradientCatalog();
    $vipGlowCatalog = \App\Models\Character::nameGlowCatalog();
@endphp
<div class="space-y-6" x-data="{ 
    activeTab: '{{ session('vip_tab', request('tab', 'pakketten')) }}',
    selectedColor: '{{ old('custom_name_color', $character->getCustomNameColor() ?: '') }}',
    selectedGradient: '{{ old('custom_name_gradient', $character->getCustomNameGradient() ?: 'none') }}',
    selectedGlow: '{{ old('custom_name_glow', $character->getCustomNameGlow() ?: 'none') }}',
    sparkleEnabled: {{ $character->isSparkleActive() ? 'true' : 'false' }},
    gradientMap: {{ Js::from(collect($vipGradientCatalog)->map(fn($g) => ['css' => $g['css'], 'glow' => $g['glow']])) }},
    colorGlowMap: {{ Js::from(collect($vipColorCatalog)->map(fn($c) => $c['glow'])) }},
    showDonateModal: false,
    donateUrl: 'https://buymeacoffee.com/tbskliniek/membership',
    donateTierTitle: 'TBS V.I.P. Lid (€6/mnd)',
    copiedName: false,
    openDonate(url, title) {
        this.donateUrl = url;
        this.donateTierTitle = title;
        this.copiedName = false;
        this.showDonateModal = true;
    },
    copyName() {
        navigator.clipboard.writeText('{{ $character->name }}');
        this.copiedName = true;
        setTimeout(() => { this.copiedName = false; }, 3000);
    },
    setTab(tab) {
        this.activeTab = tab;
        try {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url.toString());
        } catch(e) {}
    },
    getPreviewNameClass() {
        let cls = [];
        if (this.selectedGradient !== 'none' || this.sparkleEnabled) {
            cls.push('tbs-name-gradient', 'tbs-name-shimmer');
            if (this.sparkleEnabled) cls.push('vip-sparkle-name');
        } else if (!this.selectedColor) {
            cls.push('text-white');
        }
        if (this.selectedGlow === 'subtle') cls.push('tbs-name-glow-subtle');
        if (this.selectedGlow === 'neon') cls.push('tbs-name-glow-neon');
        if (this.selectedGlow === 'pulse') cls.push('tbs-name-glow-pulse');
        return cls.join(' ');
    },
    getPreviewNameStyle() {
        let styles = [];
        if (this.selectedGradient !== 'none' && this.gradientMap[this.selectedGradient]) {
            const g = this.gradientMap[this.selectedGradient];
            styles.push('--tbs-name-gradient: ' + g.css);
            styles.push('--tbs-glow-color: ' + g.glow);
        } else if (this.sparkleEnabled) {
            const baseHex = this.selectedColor || '#c084fc';
            const glowRgba = this.colorGlowMap[baseHex] || 'rgba(192, 132, 252, 0.6)';
            styles.push(`--tbs-name-gradient: linear-gradient(90deg, ${baseHex}, #fef08a, #f472b6, ${baseHex})`);
            styles.push('--tbs-glow-color: ' + glowRgba);
        } else if (this.selectedColor) {
            const glowRgba = this.colorGlowMap[this.selectedColor] || 'rgba(168, 85, 247, 0.55)';
            styles.push('color: ' + this.selectedColor + ' !important');
            if (this.selectedGlow !== 'none') {
                styles.push('--tbs-glow-color: ' + glowRgba);
            }
        } else if (this.selectedGlow !== 'none') {
            styles.push('--tbs-glow-color: rgba(168, 85, 247, 0.55)');
        }
        return styles.join('; ');
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

                <!-- 1 Week VIP voor 500 Sterren In-Game Deal -->
                <div class="rounded-3xl p-6 bg-gradient-to-br from-amber-500/10 via-purple-950/40 to-[#110726] border-2 border-amber-400/60 shadow-xl flex flex-col md:flex-row items-center justify-between gap-5 relative overflow-hidden">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-amber-400 text-purple-950 flex items-center justify-center text-2xl font-black shrink-0 shadow-md">
                            ⭐
                        </div>
                        <div class="space-y-1">
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-amber-400/20 text-amber-300 border border-amber-400/40 text-[10px] font-black uppercase">
                                In-Game Valuta • 100% Gratis Sparen
                            </div>
                            <h3 class="text-lg font-black text-white">1 Week V.I.P. Status voor 500 Sterren</h3>
                            <p class="text-xs text-slate-300 max-w-xl leading-relaxed">
                                Heb je Gouden Sterren gespaard via het Rad van Fortuin, de 7-dagen streak of level mijlpalen? Wissel 500 Sterren direct in voor 7 Dagen volwaardige VIP status en voordelen!
                            </p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('shop.buy-star-item') }}" class="shrink-0 w-full md:w-auto">
                        @csrf
                        <input type="hidden" name="item_type" value="vip_week">
                        <button type="submit" 
                                {{ ($character->stars ?? 0) < 500 ? 'disabled' : '' }}
                                class="w-full md:w-auto px-6 py-3 rounded-xl font-black text-xs uppercase tracking-wider transition flex items-center justify-center gap-2 {{ ($character->stars ?? 0) >= 500 ? 'bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-purple-950 shadow-lg cursor-pointer transform hover:-translate-y-0.5' : 'bg-slate-700 text-slate-400 cursor-not-allowed' }}">
                            <i class="fa-solid fa-crown"></i>
                            <span>Activeer 1 Week VIP (500 ⭐)</span>
                        </button>
                    </form>
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
                            <button type="button" @click="openDonate('https://buymeacoffee.com/tbskliniek/membership', 'TBS V.I.P. Lid (€6/mnd)')"
                               class="w-full block text-center py-3 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs uppercase tracking-wider transition shadow-md cursor-pointer">
                                Word V.I.P. Lid (€6/mnd)
                            </button>
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
                            <button type="button" @click="openDonate('https://buymeacoffee.com/tbskliniek/membership', 'TBS Gangster Elite (€14/mnd)')"
                               class="w-full block text-center py-3 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-black text-xs uppercase tracking-wider transition shadow-md cursor-pointer">
                                Kies Gangster Elite (€14/mnd)
                            </button>
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
                            <button type="button" @click="openDonate('https://buymeacoffee.com/tbskliniek/membership', 'TBS Kliniek Baas (€24/mnd)')"
                               class="w-full block text-center py-3 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs uppercase tracking-wider transition shadow-md cursor-pointer">
                                Word Kliniek Baas (€24/mnd)
                            </button>
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
                            <button type="button" @click="openDonate('https://buymeacoffee.com/tbskliniek/membership', 'VIP LIFETIME (€125 eenmalig)')"
                               class="btn-star-gold w-full block text-center py-3.5 rounded-xl text-slate-900 dark:text-white font-black text-xs uppercase tracking-wider hover:brightness-110 transition shadow-lg cursor-pointer">
                                Neem Lifetime VIP (€125)
                            </button>
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
                            <button type="button" @click="openDonate('https://buymeacoffee.com/tbskliniek', '1x Bakkie Koffie (€3)')"
                               class="block w-full py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs shadow-xs transition cursor-pointer">
                                Doneer 1 Koffie (€3)
                            </button>
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
                            <button type="button" @click="openDonate('https://buymeacoffee.com/tbskliniek', '3x Bakkie Koffie (€9)')"
                               class="btn-star-gold block w-full py-2.5 rounded-xl text-slate-900 dark:text-white font-black text-xs shadow-xs transition cursor-pointer">
                                Doneer 3 Koffies (€9)
                            </button>
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
                            <button type="button" @click="openDonate('https://buymeacoffee.com/tbskliniek', '5x Bakkie Koffie (€15)')"
                               class="block w-full py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs shadow-xs transition cursor-pointer">
                                Doneer 5 Koffies (€15)
                            </button>
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
                        <button type="button" @click="openDonate('https://buymeacoffee.com/tbskliniek', 'Bakkie Koffie Donatie')"
                           class="px-4 py-2 rounded-xl bg-amber-400 hover:bg-amber-300 text-purple-950 font-black text-xs shrink-0 transition cursor-pointer">
                            Naar Buy Me a Coffee
                        </button>
                    </div>
                </div>
            </div>

            <!-- TAB 3: VIP STYLING (NAAMKLEUR, KLEUROVERLOOP & GLOW) -->
            <div x-show="activeTab === 'styling'" class="space-y-6">
                <div class="bg-white dark:bg-[#110726] border border-slate-200 dark:border-purple-900/60 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
                    <div class="border-b border-slate-100 dark:border-purple-900/60 pb-5">
                        <div class="inline-flex items-center gap-2 bg-pink-50 dark:bg-pink-950/60 text-pink-800 dark:text-pink-200 text-xs font-black px-3 py-1 rounded-full border border-pink-200 dark:border-pink-800/60 mb-2">
                            <i class="fa-solid fa-wand-magic-sparkles text-pink-500"></i>
                            <span>V.I.P. Karakter Personalisatie</span>
                        </div>
                        <h2 class="text-2xl font-black text-slate-900 dark:text-white">Naamkleur, Kleuroverloop & Glow</h2>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-1 max-w-2xl leading-relaxed">
                            Val direct op in de chat, op het plein, in de bende en op de ranglijst met je eigen unieke naamkleur, kleuroverloop, neon glow en glitter shimmer!
                        </p>
                    </div>

                    <!-- Live Name Preview Box -->
                    <div class="p-6 sm:p-8 rounded-2xl border-2 border-purple-500/60 shadow-2xl text-center space-y-2 relative overflow-hidden" 
                         style="background-color: #0f0728; background-image: radial-gradient(circle at 50% 50%, #250b4a 0%, #0c0418 100%);">
                        <div class="text-[10px] uppercase tracking-widest font-black text-purple-300">Live Voorbeeld van Jouw Naam & Badge</div>
                        <div class="py-2 flex items-center justify-center gap-2.5 flex-wrap">
                            <span class="text-2xl sm:text-4xl font-black px-1"
                                  :class="getPreviewNameClass()" 
                                  :style="getPreviewNameStyle()">
                                {{ $character->name }}
                            </span>
                            <template x-if="sparkleEnabled">
                                <i class="fa-solid fa-wand-magic-sparkles text-amber-400 text-lg animate-pulse"></i>
                            </template>
                            @if($character->vip_badge)
                                <span class="px-2.5 py-0.5 rounded-md text-xs font-black uppercase border inline-flex items-center gap-1 {{ $character->vip_badge['class'] }}">
                                    <i class="fa-solid {{ $character->vip_badge['icon'] }}"></i>
                                    <span>{{ $character->vip_badge['short_label'] }}</span>
                                </span>
                            @endif
                        </div>
                        <div class="text-[11px] text-purple-200/70 font-medium">Zo wordt je naam getoond in de chat, op het plein en door de hele kliniek.</div>
                    </div>

                    @if($character->canCustomizeNameColor() || $character->canUseSparkleEffect())
                        <form method="POST" action="{{ route('vip.styling') }}" class="space-y-6">
                            @csrf

                            <!-- 1. Kleuroverloop (Gradients) -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                        1. Kies Jouw Kleuroverloop (Gradient)
                                    </label>
                                    <span class="text-[10px] font-bold text-purple-600 dark:text-purple-400">Vloeiend bewegend kleureffect</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    @foreach($vipGradientCatalog as $gKey => $grad)
                                        <label class="p-3 rounded-xl border-2 cursor-pointer flex items-center justify-between gap-2.5 transition"
                                               :class="selectedGradient === '{{ $gKey }}' ? 'border-purple-600 bg-purple-50 dark:bg-purple-950/60 shadow-sm' : 'border-slate-200 dark:border-purple-900/60 hover:bg-slate-50 dark:hover:bg-purple-950/30'">
                                            <input type="radio" name="custom_name_gradient" value="{{ $gKey }}" x-model="selectedGradient" class="sr-only">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                @if($gKey === 'none')
                                                    <span class="w-7 h-7 rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-500 flex items-center justify-center text-xs shrink-0">
                                                        <i class="fa-solid fa-droplet"></i>
                                                    </span>
                                                @else
                                                    <span class="w-7 h-7 rounded-lg shrink-0 shadow-xs border border-white/20" style="background: {{ $grad['css'] }}; background-size: 200% auto;"></span>
                                                @endif
                                                <div class="min-w-0">
                                                    <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 block truncate">{{ $grad['name'] }}</span>
                                                    <span class="text-[10px] text-slate-400 font-bold uppercase">{{ $grad['badge'] }}</span>
                                                </div>
                                            </div>
                                            <i class="fa-solid fa-circle-check text-purple-600 dark:text-purple-400 text-xs shrink-0" x-show="selectedGradient === '{{ $gKey }}'"></i>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- 2. Effen Kleur (Tier 2+) -->
                            <div class="space-y-3 pt-4 border-t border-slate-100 dark:border-purple-900/60">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                        2. Kies Jouw Exclusieve Effen Naamkleur
                                    </label>
                                    <button type="button" @click="selectedColor = ''; selectedGradient = 'none';" class="text-[10px] font-bold text-slate-400 hover:text-rose-500 cursor-pointer">
                                        Reset kleur
                                    </button>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    @foreach($vipColorCatalog as $hex => $color)
                                        <label class="p-3 rounded-xl border-2 cursor-pointer flex items-center gap-2.5 transition"
                                               :class="selectedColor === '{{ $hex }}' ? 'border-purple-600 bg-purple-50 dark:bg-purple-950/60 shadow-sm' : 'border-slate-200 dark:border-purple-900/60 hover:bg-slate-50 dark:hover:bg-purple-950/30'">
                                            <input type="radio" name="custom_name_color" value="{{ $hex }}" x-model="selectedColor" class="sr-only">
                                            <span class="w-4 h-4 rounded-full {{ $color['class'] }} ring-1 ring-black/20 shrink-0"></span>
                                            <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 truncate">{{ $color['name'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- 3. Naam Glow & Lichteffect -->
                            <div class="space-y-3 pt-4 border-t border-slate-100 dark:border-purple-900/60">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                    3. Kies Naam Glow & Lichteffect
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($vipGlowCatalog as $glowKey => $glowInfo)
                                        <label class="p-3 rounded-xl border-2 cursor-pointer flex items-center justify-between gap-2.5 transition"
                                               :class="selectedGlow === '{{ $glowKey }}' ? 'border-purple-600 bg-purple-50 dark:bg-purple-950/60 shadow-sm' : 'border-slate-200 dark:border-purple-900/60 hover:bg-slate-50 dark:hover:bg-purple-950/30'">
                                            <input type="radio" name="custom_name_glow" value="{{ $glowKey }}" x-model="selectedGlow" class="sr-only">
                                            <div class="min-w-0">
                                                <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 block">{{ $glowInfo['name'] }}</span>
                                                <span class="text-[10px] text-slate-400 block truncate">{{ $glowInfo['desc'] }}</span>
                                            </div>
                                            <i class="fa-solid fa-circle-check text-purple-600 dark:text-purple-400 text-xs shrink-0" x-show="selectedGlow === '{{ $glowKey }}'"></i>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- 4. Sparkle Toggle (Tier 3+) -->
                            <div class="space-y-3 pt-4 border-t border-slate-100 dark:border-purple-900/60">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                    4. Glitter Sparkle Shimmer Effect
                                </label>
                                @if($character->canUseSparkleEffect())
                                    <div class="p-4 rounded-2xl bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800 flex items-center justify-between gap-4">
                                        <div class="space-y-1">
                                            <div class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                                                <i class="fa-solid fa-sparkles text-pink-500"></i>
                                                <span>Geanimeerde Glitter Shimmer & Sparkle Icoon</span>
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
                                    class="w-full py-3.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs uppercase tracking-wider transition shadow-md cursor-pointer">
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
                                    'type' => 'week_vip_stars',
                                    'name' => '+1 Week VIP Status',
                                    'cost' => 500,
                                    'currency' => 'stars',
                                    'desc' => 'Activeer of verleng direct 7 dagen VIP status voor 500 Gouden Sterren!',
                                    'icon' => 'fa-star',
                                    'iconColor' => 'text-amber-500 bg-amber-50 dark:bg-amber-950/60',
                                ],
                                [
                                    'type' => 'vip_lootbag',
                                    'name' => 'VIP Buitpakket (XXL Cases + €500.000)',
                                    'cost' => 25,
                                    'currency' => 'vip_points',
                                    'desc' => '2x XXL Zaak Kisten en €500.000 contant geld.',
                                    'icon' => 'fa-briefcase',
                                    'iconColor' => 'text-indigo-500 bg-indigo-50 dark:bg-indigo-950/60',
                                ],
                                [
                                    'type' => 'stars_pack',
                                    'name' => '100 Exclusieve Sterren',
                                    'cost' => 30,
                                    'currency' => 'vip_points',
                                    'desc' => 'Direct +100 sterren voor winkelaankopen en upgrades.',
                                    'icon' => 'fa-star',
                                    'iconColor' => 'text-amber-500 bg-amber-50 dark:bg-amber-950/60',
                                ],
                            ];
                        @endphp

                        @foreach($redeemOptions as $opt)
                            @php 
                                $isStars = ($opt['currency'] ?? 'vip_points') === 'stars';
                                $canAfford = $isStars 
                                    ? (($character->stars ?? 0) >= $opt['cost']) 
                                    : (($character->vip_points ?? 0) >= $opt['cost']);
                            @endphp
                            <div class="p-5 rounded-2xl bg-slate-50 dark:bg-purple-950/40 border border-slate-200 dark:border-purple-800/60 flex flex-col justify-between gap-4">
                                <div class="flex items-start gap-3.5">
                                    <div class="w-10 h-10 rounded-xl {{ $opt['iconColor'] }} flex items-center justify-center text-lg shrink-0">
                                        <i class="fa-solid {{ $opt['icon'] }}"></i>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h4 class="font-extrabold text-sm text-slate-900 dark:text-white">{{ $opt['name'] }}</h4>
                                            @if($opt['type'] === 'cash_protection' && $character->hasCashProtection())
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 flex items-center gap-1">
                                                    <i class="fa-solid fa-shield"></i> Actief (tot {{ $character->cash_protection_until->format('d-m H:i') }})
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">{{ $opt['desc'] }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-2 border-t border-slate-200/60 dark:border-purple-900/60">
                                    <span class="text-xs font-black {{ $isStars ? 'text-amber-500' : 'text-amber-600 dark:text-amber-400' }} flex items-center gap-1 font-mono">
                                        <span>{{ $opt['cost'] }} {{ $isStars ? 'Sterren ⭐' : 'VIP Punten' }}</span>
                                    </span>
                                    <form method="POST" action="{{ route('vip.redeem') }}">
                                        @csrf
                                        <input type="hidden" name="reward_type" value="{{ $opt['type'] }}">
                                        <button type="submit" 
                                                {{ ! $canAfford ? 'disabled' : '' }}
                                                class="px-4 py-1.5 rounded-lg text-xs font-black transition {{ $canAfford ? 'bg-purple-600 hover:bg-purple-700 text-white shadow-xs cursor-pointer' : 'bg-slate-200 dark:bg-purple-950/80 text-slate-400 cursor-not-allowed' }}">
                                            {{ ($opt['type'] === 'cash_protection' && $character->hasCashProtection()) ? 'Verlengen (+24u)' : 'Inwisselen' }}
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

    <!-- BUY ME A COFFEE DONATIE MODAL (STAP 1 & STAP 2) -->
    <div x-show="showDonateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" @click="showDonateModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-[#110726] border-2 border-purple-500 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg p-6 sm:p-8 space-y-6">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-purple-900/60 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-400 text-purple-950 flex items-center justify-center text-lg font-black shrink-0 shadow-sm">
                            ☕
                        </div>
                        <div>
                            <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white" x-text="donateTierTitle"></h3>
                            <p class="text-xs text-purple-600 dark:text-purple-300 font-bold">Voltooi je steun in 2 eenvoudige stappen</p>
                        </div>
                    </div>
                    <button type="button" @click="showDonateModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition p-1 cursor-pointer">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <!-- STAP 1: KOPPELING / NOTITIE -->
                <div class="p-5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border-2 border-amber-400 dark:border-amber-500/60 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-amber-400 text-purple-950 text-[10px] font-black uppercase tracking-wider">
                            Stap 1 • Essentieel
                        </span>
                        <span class="text-xs font-black text-amber-700 dark:text-amber-400">Automatische Koppeling</span>
                    </div>
                    <p class="text-xs text-slate-700 dark:text-slate-200 font-medium leading-relaxed">
                        Vul bij Buy Me a Coffee in het <strong>BERICHT-veld ("Say something nice")</strong> en <strong>NIET als naam</strong> exact jouw spelersnaam of koppelcode in:
                    </p>
                    
                    <div class="flex items-center gap-2 bg-white dark:bg-slate-950 p-2.5 rounded-xl border border-amber-300 dark:border-amber-600/60 shadow-inner">
                        <div class="font-mono font-black text-base text-purple-700 dark:text-amber-400 px-2 select-all flex-1 truncate">
                            {{ $character->name }}
                        </div>
                        <button type="button" @click="copyName()" 
                                class="px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-purple-950 font-black text-xs transition cursor-pointer flex items-center gap-1.5 shadow-sm shrink-0">
                            <i class="fa-solid" :class="copiedName ? 'fa-check text-emerald-800' : 'fa-copy'"></i>
                            <span x-text="copiedName ? 'Gekopieerd!' : 'Kopieer'"></span>
                        </button>
                    </div>
                    <p class="text-[11px] text-amber-800 dark:text-amber-300 font-bold">
                        ⚠️ Belangrijk: Het veld "Name" mag je eigen voornaam zijn, maar het veld <u>Say something nice (Bericht)</u> moet jouw spelersnaam of code bevatten! Onze webhook leest namelijk het bericht-veld uit.
                    </p>
                </div>

                <!-- STAP 2: GA NAAR BUY ME A COFFEE -->
                <div class="space-y-3">
                    <div class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                        Stap 2 • Veilig Betalen
                    </div>
                    <a :href="donateUrl" target="_blank" rel="noopener noreferrer"
                       @click="showDonateModal = false"
                       class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-700 hover:from-purple-500 hover:to-indigo-600 text-white font-black text-sm text-center flex items-center justify-center gap-2 shadow-lg transition transform hover:-translate-y-0.5 cursor-pointer">
                        <span>Ga Verder naar Buy Me a Coffee</span>
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                </div>

                <div class="text-center pt-1">
                    <button type="button" @click="showDonateModal = false" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition font-bold cursor-pointer">
                        Sluiten
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
