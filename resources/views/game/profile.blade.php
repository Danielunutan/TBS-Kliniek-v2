@extends('layouts.app')

@section('content')
@php
    $nameColorCatalog = \App\Models\Character::nameColorCatalog();
    $nameGradientCatalog = \App\Models\Character::nameGradientCatalog();
    $nameGlowCatalog = \App\Models\Character::nameGlowCatalog();
@endphp
<div class="space-y-6" x-data="{ 
    avatarModalOpen: false, 
    bioModalOpen: false, 
    titleModalOpen: false,
    customizeModalOpen: false,
    activeCustomTab: '{{ request('custom_tab', 'sticker') }}',
    staffWarnModalOpen: false,
    staffBanModalOpen: false,
    activeTab: '{{ session('profile_tab', request('tab', 'overzicht')) }}',
    selectedSticker: '{{ $profileCharacter->profile_sticker ?? 'none' }}',
    selectedTheme: '{{ $profileCharacter->profile_bg_theme ?? 'default' }}',
    selectedColor: '{{ $profileCharacter->profile_color ?: 'purple' }}',
    selectedTitle: '{{ $profileCharacter->title ?? 'Modelpatiënt' }}',
    selectedBorder: '{{ $profileCharacter->equipped_border ?? 'none' }}',
    selectedNameColor: '{{ $profileCharacter->getCustomNameColor() ?? '' }}',
    selectedNameGradient: '{{ $profileCharacter->getCustomNameGradient() ?? 'none' }}',
    selectedNameGlow: '{{ $profileCharacter->getCustomNameGlow() ?? 'none' }}',
    selectedSparkle: {{ $profileCharacter->isSparkleActive() ? 'true' : 'false' }},
    gradientMap: {{ Js::from(collect($nameGradientCatalog)->map(fn($g) => ['css' => $g['css'], 'glow' => $g['glow']])) }},
    colorGlowMap: {{ Js::from(collect($nameColorCatalog)->map(fn($c) => $c['glow'])) }},
    init() {
        if (!'{{ session('profile_tab') }}' && !'{{ request('tab') }}' && sessionStorage.getItem('tbs_profile_tab')) {
            this.activeTab = sessionStorage.getItem('tbs_profile_tab');
        }
    },
    setActiveTab(tab) {
        this.activeTab = tab;
        sessionStorage.setItem('tbs_profile_tab', tab);
    },
    getPreviewNameClass() {
        let cls = [];
        if (this.selectedNameGradient !== 'none' || this.selectedSparkle) {
            cls.push('tbs-name-gradient', 'tbs-name-shimmer');
            if (this.selectedSparkle) cls.push('vip-sparkle-name');
        } else if (!this.selectedNameColor) {
            cls.push('text-white');
        }
        if (this.selectedNameGlow === 'subtle') cls.push('tbs-name-glow-subtle');
        if (this.selectedNameGlow === 'neon') cls.push('tbs-name-glow-neon');
        if (this.selectedNameGlow === 'pulse') cls.push('tbs-name-glow-pulse');
        return cls.join(' ');
    },
    getPreviewNameStyle() {
        let styles = [];
        if (this.selectedNameGradient !== 'none' && this.gradientMap[this.selectedNameGradient]) {
            const g = this.gradientMap[this.selectedNameGradient];
            styles.push('--tbs-name-gradient: ' + g.css);
            styles.push('--tbs-glow-color: ' + g.glow);
        } else if (this.selectedSparkle) {
            const baseHex = this.selectedNameColor || '#c084fc';
            const glowRgba = this.colorGlowMap[baseHex] || 'rgba(192, 132, 252, 0.6)';
            styles.push(`--tbs-name-gradient: linear-gradient(90deg, ${baseHex}, #fef08a, #f472b6, ${baseHex})`);
            styles.push('--tbs-glow-color: ' + glowRgba);
        } else if (this.selectedNameColor) {
            const glowRgba = this.colorGlowMap[this.selectedNameColor] || 'rgba(168, 85, 247, 0.55)';
            styles.push('color: ' + this.selectedNameColor + ' !important');
            if (this.selectedNameGlow !== 'none') {
                styles.push('--tbs-glow-color: ' + glowRgba);
            }
        } else if (this.selectedNameGlow !== 'none') {
            styles.push('--tbs-glow-color: rgba(168, 85, 247, 0.55)');
        }
        return styles.join('; ');
    }
}">

@php
    $accentColor = $profileCharacter->profile_color ?: 'purple';
    $colorMaps = [
        'purple' => [
            'bar' => 'bg-purple-600',
            'ring' => 'ring-purple-500',
            'border' => 'border-purple-600',
            'badge_bg' => 'bg-purple-50 dark:bg-purple-950/50',
            'badge_text' => 'text-purple-700 dark:text-purple-300',
            'badge_border' => 'border-purple-200 dark:border-purple-800',
            'tab_active' => 'border-purple-600 text-purple-700 dark:text-purple-400 font-extrabold',
            'accent_text' => 'text-purple-600 dark:text-purple-400',
        ],
        'rose' => [
            'bar' => 'bg-rose-600',
            'ring' => 'ring-rose-500',
            'border' => 'border-rose-600',
            'badge_bg' => 'bg-rose-50 dark:bg-rose-950/50',
            'badge_text' => 'text-rose-700 dark:text-rose-300',
            'badge_border' => 'border-rose-200 dark:border-rose-800',
            'tab_active' => 'border-rose-600 text-rose-700 dark:text-rose-400 font-extrabold',
            'accent_text' => 'text-rose-600 dark:text-rose-400',
        ],
        'emerald' => [
            'bar' => 'bg-emerald-600',
            'ring' => 'ring-emerald-500',
            'border' => 'border-emerald-600',
            'badge_bg' => 'bg-emerald-50 dark:bg-emerald-950/50',
            'badge_text' => 'text-emerald-700 dark:text-emerald-300',
            'badge_border' => 'border-emerald-200 dark:border-emerald-800',
            'tab_active' => 'border-emerald-600 text-emerald-700 dark:text-emerald-400 font-extrabold',
            'accent_text' => 'text-emerald-600 dark:text-emerald-400',
        ],
        'amber' => [
            'bar' => 'bg-amber-500',
            'ring' => 'ring-amber-500',
            'border' => 'border-amber-500',
            'badge_bg' => 'bg-amber-50 dark:bg-amber-950/50',
            'badge_text' => 'text-amber-700 dark:text-amber-300',
            'badge_border' => 'border-amber-200 dark:border-amber-800',
            'tab_active' => 'border-amber-500 text-amber-700 dark:text-amber-400 font-extrabold',
            'accent_text' => 'text-amber-500 dark:text-amber-400',
        ],
        'blue' => [
            'bar' => 'bg-blue-600',
            'ring' => 'ring-blue-500',
            'border' => 'border-blue-600',
            'badge_bg' => 'bg-blue-50 dark:bg-blue-950/50',
            'badge_text' => 'text-blue-700 dark:text-blue-300',
            'badge_border' => 'border-blue-200 dark:border-blue-800',
            'tab_active' => 'border-blue-600 text-blue-700 dark:text-blue-400 font-extrabold',
            'accent_text' => 'text-blue-600 dark:text-blue-400',
        ],
        'indigo' => [
            'bar' => 'bg-indigo-600',
            'ring' => 'ring-indigo-500',
            'border' => 'border-indigo-600',
            'badge_bg' => 'bg-indigo-50 dark:bg-indigo-950/50',
            'badge_text' => 'text-indigo-700 dark:text-indigo-300',
            'badge_border' => 'border-indigo-200 dark:border-indigo-800',
            'tab_active' => 'border-indigo-600 text-indigo-700 dark:text-indigo-400 font-extrabold',
            'accent_text' => 'text-indigo-600 dark:text-indigo-400',
        ],
        'dark' => [
            'bar' => 'bg-slate-800',
            'ring' => 'ring-slate-600',
            'border' => 'border-slate-800',
            'badge_bg' => 'bg-slate-100 dark:bg-slate-800',
            'badge_text' => 'text-slate-800 dark:text-slate-200',
            'badge_border' => 'border-slate-300 dark:border-slate-700',
            'tab_active' => 'border-slate-900 dark:border-white text-slate-900 dark:text-white font-extrabold',
            'accent_text' => 'text-slate-700 dark:text-slate-300',
        ],
    ];
    $theme = $colorMaps[$accentColor] ?? $colorMaps['purple'];
@endphp

    @if(Auth::user()->canModerate() && $profileCharacter->id !== Auth::user()->character->id)
    <!-- STAFF MODERATOR CONSOLE BANNER -->
    <div class="bg-white dark:bg-slate-900 border border-purple-200 dark:border-purple-800/80 rounded-2xl p-4 sm:p-5 shadow-xs text-slate-900 dark:text-white">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full bg-purple-100 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 text-[10px] font-black uppercase tracking-wider border border-purple-200 dark:border-purple-800/60 flex items-center gap-1.5">
                        <i class="fa-solid fa-shield-halved text-purple-600 dark:text-purple-400"></i> Moderator Console
                    </span>
                    @if($profileCharacter->user?->is_banned)
                        <span class="px-2.5 py-0.5 rounded-full bg-rose-100 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 text-[10px] font-black uppercase tracking-wider border border-rose-200 dark:border-rose-800 flex items-center gap-1">
                            <i class="fa-solid fa-ban"></i> Account Verbannen
                        </span>
                    @endif
                </div>
                <h4 class="text-sm font-black text-slate-900 dark:text-white">
                    Passende Maatregelen voor {{ $profileCharacter->name }}
                </h4>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Directe sancties uitvoeren naar aanleiding van rapportages of overtredingen.
                </p>
            </div>

            <div class="flex items-center flex-wrap gap-2 shrink-0">
                <!-- Waarschuwen Knop -->
                <button type="button" @click="staffWarnModalOpen = true" 
                        class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs transition cursor-pointer shadow-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Waarschuwing Geven</span>
                </button>

                <!-- Verbannen Knop -->
                @if(! $profileCharacter->user?->is_banned)
                    <button type="button" @click="staffBanModalOpen = true" 
                            class="px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-black text-xs transition cursor-pointer shadow-xs flex items-center gap-1.5">
                        <i class="fa-solid fa-ban"></i>
                        <span>Speler Verbannen</span>
                    </button>
                @else
                    <form action="{{ route('admin.moderation.unban', $profileCharacter->user) }}" method="POST" class="inline"
                          onsubmit="return confirm('Weet je zeker dat je de ban van {{ addslashes($profileCharacter->name) }} wilt opheffen?');">
                        @csrf
                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs transition cursor-pointer shadow-xs flex items-center gap-1.5">
                            <i class="fa-solid fa-lock-open"></i>
                            <span>Ban Opheffen</span>
                        </button>
                    </form>
                @endif

                <a href="{{ route('admin.moderation.index', ['tab' => 'reports']) }}" 
                   class="px-3.5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-black text-xs transition cursor-pointer shadow-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-flag text-amber-300"></i>
                    <span>Meldingen</span>
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Profile Header Card (Customized Theme & Background) -->
    <div class="{{ $activeTheme['bg_class'] }} rounded-2xl shadow-sm border overflow-hidden relative transition-all duration-300">
        <!-- Ambient Glowing Background Light -->
        <div class="absolute -right-16 -top-16 w-64 h-64 {{ $activeTheme['glow_class'] }} rounded-full blur-3xl pointer-events-none"></div>

        <!-- Dynamic Theme Accent Bar -->
        <div class="h-2 w-full {{ $activeTheme['bar_class'] }}"></div>

        <div class="p-6 sm:p-8 flex flex-col md:flex-row items-center md:items-start gap-6 relative z-10">
            
            @php
                $profileBorder = $profileCharacter->getEquippedBorderInfo();
            @endphp

            <!-- Avatar with Dynamic Player Accent Ring / Halloween Border Frame -->
            <div class="relative shrink-0 group">
                <div class="relative w-32 h-32 rounded-2xl transition-transform duration-300 group-hover:scale-105 {{ $profileBorder ? $profileBorder['ring'] . ' ' . $profileBorder['glow'] . ' ' . $profileBorder['border'] . ' border-2 bg-slate-950/20' : 'ring-4 ' . $theme['ring'] . '/30 border-4 border-slate-100 dark:border-slate-800 bg-slate-100 dark:bg-slate-900 shadow-sm' }}">
                    <img src="{{ $profileCharacter->avatar_url }}" alt="{{ $profileCharacter->name }}" class="w-full h-full rounded-2xl object-cover bg-slate-100 dark:bg-slate-900">
                    
                    <!-- Online Status Indicator (top right) -->
                    <span class="absolute top-2 right-2 w-4 h-4 rounded-full border-2 border-white dark:border-slate-900 shadow-xs z-10 {{ $profileCharacter->isOnline() ? 'bg-emerald-500 ring-2 ring-emerald-300/50' : 'bg-slate-300' }}" title="{{ $profileCharacter->online_status_label }}"></span>
                </div>

                <!-- Halloween Frame Corner Ornament (e.g. 🎃 for pumpkin_glow, 🩸, 👻, 👑) -->
                @if($profileBorder)
                <div class="absolute -bottom-2.5 -right-2.5 z-20 flex items-center justify-center w-9 h-9 rounded-2xl bg-gradient-to-br {{ $profileBorder['badge_gradient'] }} text-lg shadow-lg border-2 border-white dark:border-slate-900 transform hover:scale-125 transition-transform duration-200 select-none cursor-help animate-bounce" style="animation-duration: 2.5s;" title="{{ $profileBorder['name'] }} Frame actief!">
                    <span>{{ $profileBorder['emoji'] }}</span>
                </div>
                @endif

                @if(Auth::user()->character->id === $profileCharacter->id)
                <!-- Camera Button moved to bottom-left to avoid covering the frame ornament -->
                <button @click="avatarModalOpen = true" type="button" class="absolute -bottom-2 -left-2 z-20 bg-slate-900 text-white p-2.5 rounded-xl hover:bg-indigo-600 transition shadow-md border-2 border-white dark:border-slate-800 flex items-center justify-center text-xs cursor-pointer" title="Kies een andere profielfoto">
                    <i class="fa-solid fa-camera"></i>
                </button>
                @endif
            </div>

            <!-- Profile Info & Actions -->
            <div class="flex-1 w-full text-center md:text-left">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-3">
                    <div>
                        @php $isDarkCard = ($activeTheme['key'] ?? 'default') !== 'default'; @endphp
                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-2.5">
                            <h1 class="text-2xl font-black tracking-tight flex items-center gap-1.5">
                                <span class="px-1 -mx-1 {{ $profileCharacter->getNameCssClasses($isDarkCard ? 'text-white' : 'text-slate-900 dark:text-white') }}"
                                      style="{{ $profileCharacter->getNameInlineStyle() }}">
                                    {{ $profileCharacter->name }}
                                </span>
                                @if($profileCharacter->isSparkleActive())
                                    <i class="fa-solid fa-wand-magic-sparkles text-amber-400 text-sm animate-pulse" title="VIP Glitter Shimmer"></i>
                                @endif
                            </h1>
                            <span class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold px-2 py-0.5 rounded-md border border-slate-200 dark:border-slate-700">
                                Level {{ $profileCharacter->level }}
                            </span>

                            @if($profileCharacter->user && $profileCharacter->user->isStaff())
                                @php $staffBadge = $profileCharacter->user->role_badge; @endphp
                                <span class="{{ $staffBadge['bg'] }} {{ $staffBadge['text'] }} border {{ $staffBadge['border'] }} text-xs font-black uppercase tracking-wider px-2.5 py-0.5 rounded-lg inline-flex items-center gap-1.5 shadow-2xs">
                                    <i class="fa-solid {{ $staffBadge['icon'] }}"></i> {{ $staffBadge['label'] }}
                                </span>
                            @endif

                            @if($profileCharacter->isVip())
                                @php $pViBadge = $profileCharacter->vip_badge; @endphp
                                @if($pViBadge)
                                    <span class="{{ $pViBadge['class'] }} text-xs font-black uppercase tracking-wider px-2.5 py-0.5 rounded-lg inline-flex items-center gap-1.5 shadow-sm border {{ $pViBadge['glow'] }}">
                                        <i class="fa-solid {{ $pViBadge['icon'] }}"></i> {{ $pViBadge['label'] }}
                                    </span>
                                @endif
                            @endif

                            @if($profileBorder)
                            <a href="{{ route('halloween.shop') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-black bg-gradient-to-r from-orange-500/15 via-amber-500/15 to-orange-500/10 text-orange-700 dark:text-orange-300 border border-orange-400/50 hover:border-orange-500 hover:scale-105 transition shadow-xs" title="Halloween Frame: {{ $profileBorder['name'] }}">
                                <span class="text-sm">{{ $profileBorder['emoji'] }}</span>
                                <span>{{ $profileBorder['name'] }}</span>
                            </a>
                            @elseif($isOwnProfile && count($unlockedBorders ?? []) > 0)
                            <button type="button" @click="customizeModalOpen = true; activeCustomTab = 'frame'" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl border border-dashed border-orange-300 dark:border-orange-800 text-orange-600 dark:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-950/30 transition text-[11px] font-bold cursor-pointer" title="Kies een Halloween avatar frame">
                                <span>🎃</span>
                                <span>Frame Kiezen</span>
                            </button>
                            @endif

                            <!-- Active Dossier Title Badge with Accent Color -->
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-extrabold {{ $theme['badge_bg'] }} {{ $theme['badge_text'] }} border {{ $theme['badge_border'] }} shadow-2xs">
                                <i class="fa-solid fa-award text-amber-500 text-[11px]"></i>
                                <span>{{ $profileCharacter->title ?? 'Modelpatiënt' }}</span>
                                @if($isOwnProfile)
                                <button @click="titleModalOpen = true" type="button" class="ml-1 text-slate-400 hover:{{ $theme['badge_text'] }} transition cursor-pointer" title="Titel wijzigen">
                                    <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                                </button>
                                @endif
                            </span>

                            <!-- STICKER SHOWCASE VAKJE -->
                            @if($activeSticker)
                                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-xl {{ $activeSticker['badge_class'] }} border shadow-2xs transition hover:scale-105 cursor-help" title="Sticker: {{ $activeSticker['name'] }} — {{ $activeSticker['description'] }}">
                                    <i class="fa-solid {{ $activeSticker['icon'] }} text-xs"></i>
                                    <span class="text-xs font-black">{{ $activeSticker['name'] }}</span>
                                </div>
                            @elseif($isOwnProfile)
                                <button type="button" @click="customizeModalOpen = true; activeCustomTab = 'sticker'" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl border border-dashed border-slate-300 dark:border-slate-700 text-slate-400 hover:text-purple-600 hover:border-purple-400 transition text-[11px] font-bold cursor-pointer" title="Kies een verzamelde sticker voor je profiel">
                                    <i class="fa-solid fa-plus text-[9px]"></i>
                                    <span>Sticker Plakken</span>
                                </button>
                            @endif
                        </div>
                        <p class="text-xs font-semibold text-slate-400 mt-1 uppercase tracking-wider flex items-center flex-wrap justify-center md:justify-start gap-1.5">
                            <span>Patiënt</span>
                            <span>&bull;</span>
                            <span>TBS Kliniek</span>
                            <span>&bull;</span>
                            @if($profileCharacter->gang)
                                <a href="{{ route('gangs.show', $profileCharacter->gang->id) }}" class="text-purple-600 dark:text-purple-400 hover:underline font-bold flex items-center gap-1" title="Bekijk bende {{ $profileCharacter->gang->name }}">
                                    <i class="fa-solid fa-shield-halved text-[10px]"></i>
                                    <span>{{ $profileCharacter->gang->name }} [{{ $profileCharacter->gang->tag }}]</span>
                                </a>
                            @else
                                <span>Geen Bende</span>
                            @endif
                            <span>&bull;</span>
                            <span class="inline-flex items-center gap-1 text-teal-600 dark:text-teal-400 font-bold lowercase first-letter:uppercase">
                                <i class="fa-solid fa-map-location-dot text-[11px]"></i>
                                {{ $profileCharacter->current_location ?? 'Paviljoen 1 (Gesloten)' }}
                            </span>
                        </p>
                    </div>
                    
                    @if(Auth::user()->character->id === $profileCharacter->id)
                    <!-- Edit Actions: Prominent Personalisatie Knopje Rechtsboven -->
                    <div class="flex flex-wrap gap-2 justify-center md:justify-end items-center">
                        <button @click="activeCustomTab = 'name_style'; customizeModalOpen = true" type="button" class="px-3.5 py-2.5 text-xs font-black bg-purple-100 hover:bg-purple-200 dark:bg-purple-950/70 dark:hover:bg-purple-900/80 text-purple-800 dark:text-purple-200 border border-purple-300 dark:border-purple-700/60 rounded-xl transition shadow-2xs flex items-center gap-1.5 cursor-pointer active:scale-95">
                            <i class="fa-solid fa-sparkles text-pink-500"></i>
                            <span>Naamkleur & Glow</span>
                        </button>
                        <button @click="customizeModalOpen = true" type="button" class="px-4 py-2.5 text-xs font-black bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white rounded-xl transition shadow-md flex items-center gap-2 cursor-pointer active:scale-95">
                            <i class="fa-solid fa-wand-magic-sparkles text-amber-300"></i>
                            <span>Profiel Personaliseren</span>
                        </button>
                    </div>
                    @else
                    <!-- Interaction Actions (Friend, Message, Direct Theft & Fight) -->
                    <div class="flex flex-wrap gap-2 justify-center md:justify-end items-center">
                        <!-- Friend Request Action Button -->
                        @if($friendStatus === 'accepted')
                            <span class="px-3.5 py-2 text-xs font-black bg-purple-50 text-purple-700 border border-purple-200 rounded-xl flex items-center gap-1.5 shadow-2xs">
                                <i class="fa-solid fa-user-check text-purple-600"></i>
                                <span>Vrienden</span>
                            </span>
                        @elseif($friendStatus === 'pending_sent')
                            <span class="px-3.5 py-2 text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 rounded-xl flex items-center gap-1.5 shadow-2xs">
                                <i class="fa-solid fa-clock text-amber-500"></i>
                                <span>Verzoek Verstuurd</span>
                            </span>
                        @elseif($friendStatus === 'pending_received' && $pendingRequestRecord)
                            <form action="{{ route('friends.accept', $pendingRequestRecord) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3.5 py-2 text-xs font-black bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-check"></i>
                                    <span>Accepteer Verzoek</span>
                                </button>
                            </form>
                        @else
                            <form action="{{ route('friends.add') }}" method="POST">
                                @csrf
                                <input type="hidden" name="friend_name" value="{{ $profileCharacter->name }}">
                                <button type="submit" class="px-3.5 py-2 text-xs font-black bg-purple-600 hover:bg-purple-700 text-white rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-user-plus text-amber-300"></i>
                                    <span>Vriend Toevoegen</span>
                                </button>
                            </form>
                        @endif

                        <!-- Live Direct Chat / Privébericht -->
                        <button type="button" 
                                onclick="if(window.openTBSDirectChat){ window.openTBSDirectChat({{ $profileCharacter->id }}, @js($profileCharacter->name), @js($profileCharacter->avatar_url)); } else { window.location.href='{{ route('chat.index', ['tab' => 'direct', 'recipient' => $profileCharacter->id]) }}'; }" 
                                class="px-3.5 py-2 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-2xs transition flex items-center gap-1.5 cursor-pointer" 
                                title="Start live chat of stuur privébericht">
                            <i class="fa-solid fa-comments"></i>
                            <span>Privé Chat</span>
                        </button>

                        <!-- Give Respect (+1 Sigaret) Button -->
                        @if(! $hasGivenRespectToday)
                        <form action="{{ route('profile.respect', $profileCharacter->name) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3.5 py-2 text-xs font-black bg-amber-500 hover:bg-amber-600 text-white rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer" title="Bied een sigaret & respectpunt aan (1x per 24 uur)">
                                <i class="fa-solid fa-smoking text-amber-100"></i>
                                <span>Geef Respect (+1)</span>
                            </button>
                        </form>
                        @else
                        <span class="px-3.5 py-2 text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 rounded-xl flex items-center gap-1.5 shadow-2xs" title="Je hebt deze patiënt vandaag al een sigaret/respect gegeven">
                            <i class="fa-solid fa-check text-amber-600"></i>
                            <span>Respect Gegeven</span>
                        </span>
                        @endif

                        <!-- Direct Steal Button leading to Respectlijst Arena suspense screen -->
                        <form action="{{ route('fight.attack') }}" method="POST" class="inline-block">
                            @csrf
                            <input type="hidden" name="opponent_id" value="{{ $profileCharacter->id }}">
                            <input type="hidden" name="from_profile" value="1">
                            
                            <button type="submit" 
                                    class="px-4 py-2 text-xs font-black rounded-xl bg-amber-500 hover:bg-amber-600 active:scale-95 text-white transition shadow-xs flex items-center gap-2 cursor-pointer"
                                    title="Overmeester en beroof {{ $profileCharacter->name }} van contant geld (20 energie)">
                                <i class="fa-solid fa-mask"></i>
                                <span>Beroof Patiënt (€{{ number_format($profileCharacter->cash, 0, ',', '.') }})</span>
                            </button>
                        </form>

                        <!-- Report Profile Button -->
                        <button type="button" 
                                @click="window.tbsOpenReportModal({ type: 'character', id: {{ $profileCharacter->id }}, name: '{{ addslashes($profileCharacter->name) }}' })"
                                class="px-3.5 py-2 text-xs font-bold bg-slate-100 hover:bg-rose-50 text-slate-500 hover:text-rose-600 rounded-xl border border-slate-200 hover:border-rose-300 transition flex items-center gap-1.5 cursor-pointer shadow-2xs"
                                title="Rapporteer deze speler bij de moderatie">
                            <i class="fa-solid fa-flag text-rose-500"></i>
                            <span>Rapporteer</span>
                        </button>
                    </div>
                    @endif
                </div>

                <!-- XP Bar -->
                @php
                    $lvlService = app(\App\Services\LevelService::class);
                    $reqXp = $lvlService->getXpRequiredForNextLevel($profileCharacter->level);
                    $pctXp = min(100, max(0, round(($profileCharacter->xp / max(1, $reqXp)) * 100)));
                    $isDarkCard = ($activeTheme['key'] ?? 'default') !== 'default';
                @endphp
                <div class="flex flex-col gap-1.5 mb-4 md:w-3/4">
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-bold {{ $isDarkCard ? 'text-slate-300' : 'text-slate-400 dark:text-slate-400' }} uppercase text-[10px] tracking-wider">Voortgang naar Level {{ $profileCharacter->level + 1 }}</span>
                        <span class="font-black {{ $isDarkCard ? 'text-white' : 'text-slate-700 dark:text-white' }}">{{ number_format($profileCharacter->xp, 0, ',', '.') }} / {{ number_format($reqXp, 0, ',', '.') }} XP ({{ $pctXp }}%)</span>
                    </div>
                    <div class="h-2.5 {{ $isDarkCard ? 'bg-black/40 border-white/10' : 'bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700' }} rounded-full overflow-hidden border p-0.5 shadow-inner">
                        <div class="h-full bg-gradient-to-r from-purple-500 to-indigo-500 rounded-full transition-all duration-500 shadow-sm" style="width: {{ $pctXp }}%"></div>
                    </div>
                </div>

                <!-- Status quote -->
                <div class="{{ $isDarkCard ? 'bg-white/10 border-white/15 text-white/90' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200/80 dark:border-slate-700/80 text-slate-700 dark:text-slate-200' }} border rounded-xl px-4 py-2 inline-flex items-center text-left text-xs font-medium backdrop-blur-xs shadow-2xs">
                    <i class="fa-solid fa-quote-left text-purple-400 mr-2 opacity-75"></i>
                    <span>{{ $profileCharacter->status_message ?: 'Geen statusbericht ingesteld. "Kliniekleven is overleven."' }}</span>
                </div>
            </div>
        </div>

        <!-- Stats Bar (Sleek Glassmorphic Badges) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 border-t {{ $isDarkCard ? 'border-white/10 bg-black/30' : 'border-slate-200/80 dark:border-white/10 bg-slate-50/70 dark:bg-black/30' }} backdrop-blur-md">
            
            <!-- 1: Respect -->
            <div class="p-3 sm:py-4 sm:px-5 flex items-center justify-center gap-3 border-r border-b lg:border-b-0 {{ $isDarkCard ? 'border-white/10 hover:bg-white/5' : 'border-slate-200/80 dark:border-white/10 hover:bg-black/5 dark:hover:bg-white/5' }} transition-colors group">
                <div class="w-10 h-10 rounded-xl bg-indigo-500/15 border border-indigo-500/30 text-indigo-500 dark:text-indigo-400 flex items-center justify-center text-base shrink-0 group-hover:scale-110 transition-transform shadow-xs">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div class="text-left min-w-0">
                    <span class="text-[10px] font-black tracking-widest uppercase {{ $isDarkCard ? 'text-slate-400' : 'text-slate-400 dark:text-slate-400' }} block leading-tight">
                        Respect
                    </span>
                    <span class="text-base sm:text-xl font-black {{ $isDarkCard ? 'text-white' : 'text-slate-900 dark:text-white' }} tracking-tight font-['Outfit'] block truncate">
                        {{ number_format($profileCharacter->respect, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- 2: Peuk Fooi (+1) -->
            <div class="p-3 sm:py-4 sm:px-5 flex items-center justify-center gap-3 border-b lg:border-b-0 lg:border-r {{ $isDarkCard ? 'border-white/10 hover:bg-white/5' : 'border-slate-200/80 dark:border-white/10 hover:bg-black/5 dark:hover:bg-white/5' }} transition-colors group">
                <div class="w-10 h-10 rounded-xl bg-amber-500/15 border border-amber-500/30 text-amber-500 dark:text-amber-400 flex items-center justify-center text-base shrink-0 group-hover:scale-110 transition-transform shadow-xs">
                    <i class="fa-solid fa-smoking"></i>
                </div>
                <div class="text-left min-w-0">
                    <span class="text-[10px] font-black tracking-widest uppercase {{ $isDarkCard ? 'text-slate-400' : 'text-slate-400 dark:text-slate-400' }} block leading-tight">
                        Peuk Fooi (+1)
                    </span>
                    <span class="text-base sm:text-xl font-black text-amber-500 dark:text-amber-400 tracking-tight font-['Outfit'] block truncate">
                        {{ number_format($totalRespectsReceived, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- 3: Contant Geld -->
            <div class="p-3 sm:py-4 sm:px-5 flex items-center justify-center gap-3 border-r {{ $isDarkCard ? 'border-white/10 hover:bg-white/5' : 'border-slate-200/80 dark:border-white/10 hover:bg-black/5 dark:hover:bg-white/5' }} transition-colors group">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-500 dark:text-emerald-400 flex items-center justify-center text-base shrink-0 group-hover:scale-110 transition-transform shadow-xs">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div class="text-left min-w-0">
                    <span class="text-[10px] font-black tracking-widest uppercase {{ $isDarkCard ? 'text-slate-400' : 'text-slate-400 dark:text-slate-400' }} block leading-tight">
                        Contant
                    </span>
                    <span class="text-base sm:text-xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight font-['Outfit'] block truncate">
                        €{{ number_format($profileCharacter->cash, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- 4: Banktegoed -->
            <div class="p-3 sm:py-4 sm:px-5 flex items-center justify-center gap-3 {{ $isDarkCard ? 'hover:bg-white/5' : 'hover:bg-black/5 dark:hover:bg-white/5' }} transition-colors group">
                <div class="w-10 h-10 rounded-xl bg-sky-500/15 border border-sky-500/30 text-sky-500 dark:text-sky-400 flex items-center justify-center text-base shrink-0 group-hover:scale-110 transition-transform shadow-xs">
                    <i class="fa-solid fa-building-columns"></i>
                </div>
                <div class="text-left min-w-0">
                    <span class="text-[10px] font-black tracking-widest uppercase {{ $isDarkCard ? 'text-slate-400' : 'text-slate-400 dark:text-slate-400' }} block leading-tight">
                        Banktegoed
                    </span>
                    <span class="text-base sm:text-xl font-black {{ $isDarkCard ? 'text-white' : 'text-slate-900 dark:text-white' }} tracking-tight font-['Outfit'] block truncate">
                        €{{ number_format($profileCharacter->bank, 0, ',', '.') }}
                    </span>
                </div>
            </div>

        </div>

        <!-- Navigation Tabs with Dynamic Accent Color -->
        <div class="flex {{ $isDarkCard ? 'border-t border-white/10 bg-black/20' : 'border-t border-slate-200/80 dark:border-white/10 bg-slate-50/40 dark:bg-black/20' }} px-6 gap-6 overflow-x-auto">
            <button @click="setActiveTab('overzicht')" :class="activeTab === 'overzicht' ? '{{ $theme['tab_active'] }}' : 'border-transparent {{ $isDarkCard ? 'text-slate-300 hover:text-white' : 'text-slate-500 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}'" class="py-3.5 text-xs font-bold border-b-2 transition flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                <i class="fa-solid fa-id-card"></i> Dossier Overzicht
            </button>
            <button @click="setActiveTab('psychiatrisch')" :class="activeTab === 'psychiatrisch' ? '{{ $theme['tab_active'] }}' : 'border-transparent {{ $isDarkCard ? 'text-slate-300 hover:text-white' : 'text-slate-500 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}'" class="py-3.5 text-xs font-bold border-b-2 transition flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                <i class="fa-solid fa-clipboard-user text-rose-500"></i> Psychiatrisch Rapport
            </button>
            <button @click="setActiveTab('medailles')" :class="activeTab === 'medailles' ? '{{ $theme['tab_active'] }}' : 'border-transparent {{ $isDarkCard ? 'text-slate-300 hover:text-white' : 'text-slate-500 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}'" class="py-3.5 text-xs font-bold border-b-2 transition flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                <i class="fa-solid fa-medal text-amber-500"></i> Medailles & Prestaties
            </button>
            <button @click="setActiveTab('vrienden')" :class="activeTab === 'vrienden' ? '{{ $theme['tab_active'] }}' : 'border-transparent {{ $isDarkCard ? 'text-slate-300 hover:text-white' : 'text-slate-500 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}'" class="py-3.5 text-xs font-bold border-b-2 transition flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                <i class="fa-solid fa-user-group text-purple-500"></i> Vriendenlijst ({{ $friendsCount }})
            </button>
            <button @click="setActiveTab('gastenboek')" :class="activeTab === 'gastenboek' ? '{{ $theme['tab_active'] }}' : 'border-transparent {{ $isDarkCard ? 'text-slate-300 hover:text-white' : 'text-slate-500 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}'" class="py-3.5 text-xs font-bold border-b-2 transition flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                <i class="fa-solid fa-book-open text-indigo-500"></i> Gastenboek ({{ $guestbookEntries->total() }})
            </button>
        </div>
    </div>

    <!-- Tab 1: Overzicht & Statistieken -->
    <div x-show="activeTab === 'overzicht'" class="grid grid-cols-1 md:grid-cols-2 gap-6">

        @if(Auth::id() === $profileCharacter->user_id)
        <!-- Discord Koppeling & Beloningen Banner -->
        <div class="md:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-[#5865F2] text-white flex items-center justify-center text-2xl shadow-sm shrink-0">
                    <i class="fa-brands fa-discord"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-black text-slate-900 dark:text-white font-['Outfit']">Discord Koppeling & Server Rollen</span>
                        @if(Auth::user()->discord_id)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300">
                                <i class="fa-solid fa-check text-[9px]"></i> Gekoppeld
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-300">
                                + €2.500 & 3 Dagen VIP
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        @if(Auth::user()->discord_id)
                            Je account is verbonden met Discord ID: <code class="font-mono text-purple-700 bg-purple-50 dark:bg-purple-950/60 dark:text-purple-300 px-1 py-0.5 rounded">{{ Auth::user()->discord_id }}</code>. Jouw spelersrol en VIP-status worden automatisch gesynchroniseerd.
                        @else
                            Koppel je Discord en ontvang direct <strong>€2.500 zakgeld</strong>, <strong>3 dagen gratis VIP</strong> én de <strong>TBS-Kliniek.nl</strong> rol in de Discord server!
                        @endif
                    </p>
                </div>
            </div>

            <div class="shrink-0 w-full md:w-auto">
                @if(Auth::user()->discord_id)
                    <form method="POST" action="{{ route('discord.sync_roles') }}">
                        @csrf
                        <button type="submit" class="w-full md:w-auto inline-flex items-center justify-center gap-2 bg-[#5865F2] hover:bg-[#4752C4] text-white font-extrabold text-xs py-2.5 px-4 rounded-xl transition shadow-xs cursor-pointer">
                            <i class="fa-solid fa-rotate"></i>
                            <span>Rollen Syncen</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('discord.redirect') }}" class="w-full md:w-auto inline-flex items-center justify-center gap-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs py-2.5 px-5 rounded-xl transition shadow-xs cursor-pointer">
                        <i class="fa-solid fa-gift"></i>
                        <span>Koppel Discord & Claim Bonus</span>
                    </a>
                @endif
            </div>
        </div>
        @endif

        <!-- Attributen & Stats -->
        @php
            $statCatalog = \App\Models\Character::statDescriptionsCatalog();
        @endphp
        <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 p-6" x-data="{ openStatInfo: null }">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Statistieken &amp; Vaardigheden</h3>
                <span class="text-[10px] font-bold text-slate-400">Klik op <i class="fa-solid fa-circle-info text-purple-500"></i> voor uitleg</span>
            </div>
            
            <div class="space-y-2.5">
                @php
                    $profileStatsList = [
                        ['key' => 'strength', 'label' => 'Kracht', 'icon' => 'fa-hand-fist text-rose-500', 'base' => $profileCharacter->strength, 'eff' => $profileCharacter->getEffectiveStrength()],
                        ['key' => 'defense', 'label' => 'Verdediging', 'icon' => 'fa-shield-halved text-indigo-500', 'base' => $profileCharacter->defense, 'eff' => $profileCharacter->getEffectiveDefense()],
                        ['key' => 'stamina', 'label' => 'Conditie / Stamina', 'icon' => 'fa-bolt text-emerald-500', 'base' => $profileCharacter->stamina, 'eff' => $profileCharacter->getEffectiveStamina()],
                        ['key' => 'intelligence', 'label' => 'Intelligentie', 'icon' => 'fa-brain text-purple-500', 'base' => $profileCharacter->intelligence ?? 10, 'eff' => $profileCharacter->getEffectiveIntelligence()],
                        ['key' => 'charisma', 'label' => 'Charisma', 'icon' => 'fa-face-smile-wink text-amber-500', 'base' => $profileCharacter->charisma ?? 10, 'eff' => $profileCharacter->getEffectiveCharisma()],
                        ['key' => 'discipline', 'label' => 'Discipline', 'icon' => 'fa-scale-balanced text-sky-500', 'base' => $profileCharacter->discipline ?? 10, 'eff' => $profileCharacter->getEffectiveDiscipline()],
                    ];
                @endphp

                @foreach($profileStatsList as $sRow)
                @php
                    $sDesc = $statCatalog[$sRow['key']] ?? ['short' => '', 'detail' => ''];
                    $bonus = max(0, (int) $sRow['eff'] - (int) $sRow['base']);
                @endphp
                <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-100">
                    <div class="flex justify-between items-center text-xs">
                        <button type="button"
                                @click="openStatInfo = (openStatInfo === '{{ $sRow['key'] }}' ? null : '{{ $sRow['key'] }}')"
                                title="{{ $sDesc['detail'] }}"
                                class="font-bold text-slate-700 flex items-center gap-2 cursor-pointer text-left group">
                            <i class="fa-solid {{ $sRow['icon'] }} w-4"></i>
                            <span>{{ $sRow['label'] }}</span>
                            <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-purple-600 transition text-[11px]"></i>
                        </button>
                        <div class="flex items-center gap-1.5">
                            <span class="font-extrabold text-slate-900">{{ $sRow['eff'] }}</span>
                            @if($bonus > 0)
                                <span class="text-[10px] font-bold text-emerald-600" title="Basis: {{ $sRow['base'] }} + {{ $bonus }} uitrusting">(+{{ $bonus }})</span>
                            @endif
                        </div>
                    </div>
                    <div x-show="openStatInfo === '{{ $sRow['key'] }}'" x-cloak class="mt-2 pt-2 border-t border-slate-200/80 text-[11px] text-slate-500 leading-relaxed">
                        {{ $sDesc['detail'] }}
                    </div>
                </div>
                @endforeach

                <div class="flex justify-between items-center text-xs p-2.5 bg-slate-50 rounded-lg border border-slate-100">
                    <span class="font-bold text-slate-600 flex items-center gap-2.5">
                        <i class="fa-solid fa-bullseye text-orange-500 w-4"></i> Voltooide Missies
                    </span>
                    <span class="font-extrabold text-slate-900">{{ $profileCharacter->missions_completed }}</span>
                </div>
                <div class="flex justify-between items-center text-xs p-2.5 bg-slate-50 rounded-lg border border-slate-100">
                    <span class="font-bold text-slate-600 flex items-center gap-2.5">
                        <i class="fa-solid fa-trophy text-amber-500 w-4"></i> Gevechten Gewonnen / Verloren
                    </span>
                    <span class="font-extrabold text-slate-900">{{ $profileCharacter->fights_won }}W / {{ $profileCharacter->fights_lost }}V</span>
                </div>
            </div>
        </div>

        <!-- Persoonlijke Bio -->
        <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Patiëntendossier & Biografie</h3>
                    @if(Auth::user()->character->id === $profileCharacter->id)
                    <button @click="bioModalOpen = true" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                        <i class="fa-solid fa-pen"></i> Bewerken
                    </button>
                    @endif
                </div>

                <div class="bg-slate-50 p-5 rounded-xl border border-slate-100 text-xs text-slate-600 leading-relaxed min-h-[140px]">
                    @if($profileCharacter->bio)
                        {!! nl2br(e($profileCharacter->bio)) !!}
                    @else
                        <p class="text-slate-400 italic">Deze patiënt heeft nog geen persoonlijke biografie geschreven. Klik op 'Bio Bewerken' om jouw verhaal te delen!</p>
                    @endif
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 text-[11px] text-slate-400 flex flex-wrap items-center justify-between gap-2">
                <span>Geregistreerd in kliniek: {{ $profileCharacter->created_at ? \Illuminate\Support\Carbon::parse($profileCharacter->created_at)->format('d-m-Y') : now()->format('d-m-Y') }}</span>
                <span>Status: 
                    @if($profileCharacter->isOnline())
                        <strong class="text-emerald-600 font-extrabold inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>Nu Online</strong>
                    @else
                        <span class="text-slate-500 font-medium">{{ $profileCharacter->last_active_at ? 'Laatst gezien ' . $profileCharacter->last_active_at->diffForHumans() : 'Nog niet actief geweest' }}</span>
                    @endif
                </span>
            </div>
        </div>

        <!-- Vrienden Preview Card in Overview -->
        <div class="md:col-span-2 bg-white rounded-xl shadow-xs border border-slate-200/80 p-6">
            <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-black">
                        <i class="fa-solid fa-user-group"></i>
                    </span>
                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Vrienden & Medepatiënten ({{ $friendsCount }})</h3>
                </div>
                <button type="button" @click="setActiveTab('vrienden')" class="text-xs font-extrabold text-purple-600 hover:text-purple-800 transition flex items-center gap-1 cursor-pointer">
                    <span>Bekijk Alle ({{ $friendsCount }})</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </button>
            </div>

            @if($friends->isEmpty())
            <div class="p-6 text-center text-xs text-slate-400 bg-slate-50/50 rounded-xl border border-slate-100">
                Nog geen goedgekeurde vrienden verbonden in dit kliniekdossier.
            </div>
            @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
                @foreach($friends->take(6) as $f)
                <a href="{{ route('profile.show', $f->friend->name) }}" class="p-2.5 rounded-xl border border-slate-200/70 hover:border-purple-300 hover:bg-purple-50/20 transition flex flex-col items-center text-center group">
                    <div class="relative mb-2">
                        <img src="{{ $f->friend->avatar_url }}" alt="{{ $f->friend->name }}" class="w-12 h-12 rounded-xl object-cover border border-slate-200 shadow-2xs group-hover:scale-105 transition-transform">
                        <span class="absolute -bottom-1 -right-1 w-2.5 h-2.5 rounded-full border-2 border-white {{ $f->friend->isOnline() ? 'bg-emerald-500' : 'bg-slate-300' }}" title="{{ $f->friend->online_status_label }}"></span>
                        @if($f->is_bff)
                        <span class="absolute -top-1 -right-1 bg-pink-500 text-white text-[8px] w-4 h-4 rounded-full flex items-center justify-center shadow-xs" title="BFF">
                            <i class="fa-solid fa-heart text-[7px] text-white"></i>
                        </span>
                        @endif
                    </div>
                    <span class="text-xs font-extrabold text-slate-900 group-hover:text-purple-600 transition truncate max-w-full block leading-tight">
                        {{ $f->friend->name }}
                    </span>
                    <span class="text-[10px] font-bold text-slate-400 mt-0.5">Lvl {{ $f->friend->level }}</span>
                </a>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Laatst Bezochte TBS'ers (Recent Profile Visits & Visitors) -->
        <div class="md:col-span-2 bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/80 dark:border-slate-800 p-6 space-y-4"
             x-data="{ visitorMode: 'visitors' }">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800/80 pb-4">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 flex items-center justify-center text-sm font-black shadow-2xs">
                        <i class="fa-solid fa-footsteps"></i>
                    </span>
                    <div>
                        <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider font-['Outfit']">
                            Laatst Bezochte TBS'ers
                        </h3>
                        <p class="text-[11px] text-slate-400">
                            Recente activiteit en bezoeken aan dit patiëntendossier
                        </p>
                    </div>
                </div>

                <!-- Mode Switcher Pills -->
                <div class="flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800/80 p-1 rounded-xl shrink-0 self-start sm:self-center">
                    <button type="button" @click="visitorMode = 'visitors'"
                            :class="visitorMode === 'visitors' ? 'bg-white dark:bg-purple-600 text-purple-700 dark:text-white shadow-xs font-black' : 'text-slate-600 dark:text-slate-400 font-bold hover:text-purple-600'"
                            class="px-3 py-1 rounded-lg text-xs transition cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-eye text-[10px]"></i>
                        <span>Bezoekers ({{ $recentVisitors->count() }})</span>
                    </button>
                    <button type="button" @click="visitorMode = 'visited'"
                            :class="visitorMode === 'visited' ? 'bg-white dark:bg-purple-600 text-purple-700 dark:text-white shadow-xs font-black' : 'text-slate-600 dark:text-slate-400 font-bold hover:text-purple-600'"
                            class="px-3 py-1 rounded-lg text-xs transition cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-compass text-[10px]"></i>
                        <span>{{ $isOwnProfile ? 'Door Jou Bezocht' : 'Door ' . $profileCharacter->name . ' Bezocht' }} ({{ $recentlyVisited->count() }})</span>
                    </button>
                </div>
            </div>

            <!-- VIEW 1: BEZOEKERS (TBS'ers who visited this profile) -->
            <div x-show="visitorMode === 'visitors'">
                @if($recentVisitors->isEmpty())
                    <div class="p-8 text-center text-xs text-slate-400 dark:text-slate-500 bg-slate-50/60 dark:bg-slate-800/30 rounded-xl border border-dashed border-slate-200 dark:border-slate-800">
                        <i class="fa-solid fa-ghost text-2xl text-slate-300 dark:text-slate-600 mb-1.5 block"></i>
                        <span>Nog geen recente bezoekers geregistreerd in dit kliniekdossier.</span>
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
                        @foreach($recentVisitors as $visit)
                            @if($visit->visitor)
                            <a href="{{ route('profile.show', $visit->visitor->name) }}" 
                               class="p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 hover:border-purple-300 dark:hover:border-purple-600/60 hover:bg-purple-50/20 dark:hover:bg-purple-950/20 transition-all duration-200 flex flex-col items-center text-center group shadow-2xs hover:-translate-y-0.5">
                                <div class="relative mb-2">
                                    <img src="{{ $visit->visitor->avatar_url }}" alt="{{ $visit->visitor->name }}" 
                                         class="w-12 h-12 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shadow-2xs group-hover:scale-105 transition-transform"
                                         onerror="this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}'">
                                    <span class="absolute -bottom-1 -right-1 w-2.5 h-2.5 rounded-full border-2 border-white dark:border-slate-900 {{ $visit->visitor->isOnline() ? 'bg-emerald-500 animate-pulse' : 'bg-slate-300 dark:bg-slate-600' }}" 
                                          title="{{ $visit->visitor->online_status_label }}"></span>
                                </div>
                                <span class="text-xs font-black text-slate-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition truncate max-w-full block leading-tight">
                                    {{ $visit->visitor->name }}
                                </span>
                                <div class="flex items-center gap-1 mt-0.5 flex-wrap justify-center">
                                    <span class="text-[9px] font-black px-1.5 py-0.2 rounded bg-slate-200 dark:bg-purple-950 text-slate-700 dark:text-purple-300">
                                        Lvl {{ $visit->visitor->level }}
                                    </span>
                                    @if($visit->visitor->gang)
                                        <span class="text-[8px] font-black text-purple-600 dark:text-purple-400 uppercase">
                                            [{{ $visit->visitor->gang->tag }}]
                                        </span>
                                    @endif
                                </div>
                                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 mt-1.5 flex items-center gap-1">
                                    <i class="fa-regular fa-clock text-[9px]"></i>
                                    <span>{{ $visit->visited_at ? $visit->visited_at->diffForHumans(null, true) : 'Zojuist' }}</span>
                                </span>
                            </a>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- VIEW 2: DOOR JOU / DEZE PATIENT BEZOCHT (TBS'ers visited by this character) -->
            <div x-show="visitorMode === 'visited'" x-cloak>
                @if($recentlyVisited->isEmpty())
                    <div class="p-8 text-center text-xs text-slate-400 dark:text-slate-500 bg-slate-50/60 dark:bg-slate-800/30 rounded-xl border border-dashed border-slate-200 dark:border-slate-800">
                        <i class="fa-solid fa-compass text-2xl text-slate-300 dark:text-slate-600 mb-1.5 block"></i>
                        <span>Nog geen andere dossiers bezocht door deze patiënt.</span>
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
                        @foreach($recentlyVisited as $visit)
                            @if($visit->visited)
                            <a href="{{ route('profile.show', $visit->visited->name) }}" 
                               class="p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 hover:border-purple-300 dark:hover:border-purple-600/60 hover:bg-purple-50/20 dark:hover:bg-purple-950/20 transition-all duration-200 flex flex-col items-center text-center group shadow-2xs hover:-translate-y-0.5">
                                <div class="relative mb-2">
                                    <img src="{{ $visit->visited->avatar_url }}" alt="{{ $visit->visited->name }}" 
                                         class="w-12 h-12 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shadow-2xs group-hover:scale-105 transition-transform"
                                         onerror="this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}'">
                                    <span class="absolute -bottom-1 -right-1 w-2.5 h-2.5 rounded-full border-2 border-white dark:border-slate-900 {{ $visit->visited->isOnline() ? 'bg-emerald-500 animate-pulse' : 'bg-slate-300 dark:bg-slate-600' }}" 
                                          title="{{ $visit->visited->online_status_label }}"></span>
                                </div>
                                <span class="text-xs font-black text-slate-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition truncate max-w-full block leading-tight">
                                    {{ $visit->visited->name }}
                                </span>
                                <div class="flex items-center gap-1 mt-0.5 flex-wrap justify-center">
                                    <span class="text-[9px] font-black px-1.5 py-0.2 rounded bg-slate-200 dark:bg-purple-950 text-slate-700 dark:text-purple-300">
                                        Lvl {{ $visit->visited->level }}
                                    </span>
                                    @if($visit->visited->gang)
                                        <span class="text-[8px] font-black text-purple-600 dark:text-purple-400 uppercase">
                                            [{{ $visit->visited->gang->tag }}]
                                        </span>
                                    @endif
                                </div>
                                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 mt-1.5 flex items-center gap-1">
                                    <i class="fa-regular fa-clock text-[9px]"></i>
                                    <span>{{ $visit->visited_at ? $visit->visited_at->diffForHumans(null, true) : 'Zojuist' }}</span>
                                </span>
                            </a>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Tab: Psychiatrisch Rapport (Dr. H. van Beek) -->
    <div x-show="activeTab === 'psychiatrisch'" x-cloak class="space-y-6">
        <!-- Forensic Clinic File Container -->
        <div class="bg-gradient-to-b from-stone-50 via-white to-stone-100/60 dark:from-slate-900/95 dark:via-slate-900 dark:to-slate-950/95 rounded-2xl shadow-md dark:shadow-2xl dark:shadow-purple-950/20 border-2 border-stone-300 dark:border-slate-800 p-6 sm:p-8 relative overflow-hidden transition-colors">
            <!-- Background Security Watermark & Grid Pattern -->
            <div class="absolute inset-0 opacity-[0.03] dark:opacity-[0.04] pointer-events-none select-none bg-[radial-gradient(#1e293b_1px,transparent_1px)] dark:bg-[radial-gradient(#64748b_1px,transparent_1px)] [background-size:16px_16px]"></div>
            <div class="absolute -right-8 -top-8 opacity-10 dark:opacity-[0.04] pointer-events-none select-none text-9xl text-slate-800 dark:text-white rotate-12">
                <i class="fa-solid fa-stamp"></i>
            </div>

            <!-- Top Forensic Header Bar -->
            <div class="border-b-2 border-stone-300 dark:border-slate-800 pb-5 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-red-700 dark:bg-rose-600 text-white shadow-xs">
                            <i class="fa-solid fa-lock text-[9px]"></i> STRIKT MEDISCH GEHEIM
                        </span>
                        <span class="px-2 py-0.5 rounded bg-stone-200 dark:bg-slate-800 text-stone-700 dark:text-slate-300 border border-transparent dark:border-slate-700 font-mono text-[10px] font-bold">
                            WGBO ART. 7:457
                        </span>
                        <span class="text-xs font-mono font-extrabold text-stone-800 dark:text-purple-400 tracking-wider">
                            DOSSIER #{{ $psychiatricReport['dossier_nr'] }}
                        </span>
                    </div>
                    <div class="pt-1">
                        <p class="text-[10px] font-black uppercase tracking-widest text-stone-500 dark:text-slate-400">
                            MINISTERIE VAN JUSTITIE &bull; DIENST JUSTITIËLE INRICHTINGEN &bull; FORENSISCHE ZORG
                        </p>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                            <span>Psychiatrisch Onderzoeksverslag</span>
                        </h2>
                    </div>
                    <div class="text-xs text-stone-600 dark:text-slate-400 flex flex-wrap items-center gap-x-3 gap-y-1 pt-0.5 font-medium">
                        <span>Patiënt: <strong class="text-slate-900 dark:text-slate-100 font-black">{{ $profileCharacter->name }}</strong></span>
                        <span>&bull;</span>
                        <span>Opnamedatum: <strong class="text-slate-800 dark:text-slate-200 font-bold">{{ $profileCharacter->created_at ? \Illuminate\Support\Carbon::parse($profileCharacter->created_at)->format('d-m-Y') : now()->format('d-m-Y') }}</strong></span>
                        <span>&bull;</span>
                        <span>Status: <span class="text-amber-800 dark:text-amber-300 font-extrabold bg-amber-100/80 dark:bg-amber-950/70 border border-amber-200/60 dark:border-amber-800/60 px-2 py-0.5 rounded text-[10px]">TBS met Dwangverpleging</span></span>
                    </div>
                </div>

                <!-- Doctor Card Header -->
                <div class="bg-white/90 dark:bg-slate-800/90 border border-stone-300 dark:border-slate-700/80 rounded-xl p-3 shadow-2xs flex items-center gap-3 shrink-0 self-start md:self-center">
                    <div class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-950/70 border border-indigo-200 dark:border-indigo-800/60 text-indigo-700 dark:text-indigo-400 flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-user-doctor"></i>
                    </div>
                    <div>
                        <div class="text-[9px] font-black uppercase tracking-wider text-stone-400 dark:text-slate-400">Behandelend Geneesheer</div>
                        <div class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                            {{ $psychiatricReport['doctor_name'] }}
                            <i class="fa-solid fa-circle-check text-blue-600 dark:text-blue-400 text-[11px]" title="BIG Geverifieerd"></i>
                        </div>
                        <div class="text-[10px] text-stone-500 dark:text-slate-400 font-medium leading-tight">{{ $psychiatricReport['doctor_role'] }}</div>
                        <div class="text-[9px] font-mono text-stone-400 dark:text-slate-400">{{ $psychiatricReport['big_registration'] ?? 'BIG: 492.019.481' }}</div>
                    </div>
                </div>
            </div>

            <!-- Threat Level Meter Banner -->
            <div class="p-5 rounded-xl border-2 {{ $psychiatricReport['threat_color'] }} mb-6 relative z-10 transition-all">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-xl bg-white/90 dark:bg-slate-900/90 border border-current flex items-center justify-center text-2xl shrink-0 shadow-xs">
                            <i class="{{ $psychiatricReport['threat_icon'] }}"></i>
                        </div>
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-widest block opacity-75">Vastgesteld Dreigingsniveau</span>
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-black tracking-wide">{{ $psychiatricReport['threat_level'] }}</span>
                                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full {{ $psychiatricReport['threat_badge_color'] ?? 'bg-white/40' }} border">
                                    {{ $psychiatricReport['regime'] ?? 'Gesloten Afdeling' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="text-xs font-bold md:max-w-md md:text-right leading-relaxed bg-white/60 dark:bg-slate-900/60 md:bg-transparent md:dark:bg-transparent p-2.5 md:p-0 rounded-lg text-slate-900 dark:text-slate-100">
                        {{ $psychiatricReport['danger_desc'] }}
                    </div>
                </div>

                <!-- Threat Scale Visual Meter -->
                <div class="mt-4 pt-3 border-t border-current/20">
                    <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-wider mb-1.5 opacity-80">
                        <span>Dreigingsladder (TBS Risico-index)</span>
                        <span>Schaal: {{ $psychiatricReport['threat_meter'] ?? 50 }}%</span>
                    </div>
                    <div class="w-full h-2.5 bg-black/10 dark:bg-black/40 rounded-full overflow-hidden p-0.5 flex gap-1">
                        <div class="h-full rounded-full transition-all duration-500 {{ ($psychiatricReport['threat_meter'] ?? 50) >= 10 ? 'bg-emerald-500' : 'bg-transparent' }} flex-1"></div>
                        <div class="h-full rounded-full transition-all duration-500 {{ ($psychiatricReport['threat_meter'] ?? 50) >= 30 ? 'bg-yellow-400' : 'bg-stone-300 dark:bg-slate-800' }} flex-1"></div>
                        <div class="h-full rounded-full transition-all duration-500 {{ ($psychiatricReport['threat_meter'] ?? 50) >= 55 ? 'bg-amber-500' : 'bg-stone-300 dark:bg-slate-800' }} flex-1"></div>
                        <div class="h-full rounded-full transition-all duration-500 {{ ($psychiatricReport['threat_meter'] ?? 50) >= 80 ? 'bg-rose-600' : 'bg-stone-300 dark:bg-slate-800' }} flex-1"></div>
                        <div class="h-full rounded-full transition-all duration-500 {{ ($psychiatricReport['threat_meter'] ?? 50) >= 95 ? 'bg-slate-900 dark:bg-black border border-rose-500' : 'bg-stone-300 dark:bg-slate-800' }} flex-1"></div>
                    </div>
                    <div class="flex justify-between text-[9px] font-bold text-stone-500 dark:text-slate-400 mt-1">
                        <span class="text-emerald-700 dark:text-emerald-400">Groen (Intake)</span>
                        <span class="text-yellow-700 dark:text-yellow-400">Geel</span>
                        <span class="text-amber-700 dark:text-amber-400">Oranje</span>
                        <span class="text-rose-700 dark:text-rose-400">Rood</span>
                        <span class="text-stone-900 dark:text-rose-300 font-extrabold">Zwart (EBI)</span>
                    </div>
                </div>
            </div>

            <!-- Diagnosis & Treatment Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 relative z-10">
                <!-- Card 1: Hoofddiagnose -->
                <div class="bg-white dark:bg-slate-800/90 rounded-xl p-5 border-2 border-stone-200/90 dark:border-slate-700/80 shadow-2xs space-y-2.5 hover:border-purple-300 dark:hover:border-purple-500 transition-colors">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase text-purple-800 dark:text-purple-300 tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-stethoscope text-purple-600 dark:text-purple-400"></i> Voorlopige Hoofddiagnose
                        </span>
                        <span class="px-2 py-0.5 rounded bg-purple-50 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 text-[10px] font-mono font-black border border-purple-200 dark:border-purple-800/60">
                            {{ $psychiatricReport['dsm_code'] ?? 'DSM-TBS 301.7' }}
                        </span>
                    </div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white leading-snug">
                        {{ $psychiatricReport['diagnosis'] }}
                    </h3>
                    <p class="text-xs text-stone-600 dark:text-slate-300 leading-relaxed font-sans pt-1 border-t border-stone-100 dark:border-slate-700/60">
                        Vastgesteld conform klinische DSM-TBS standaarden. Patiënt toont een structureel patroon van verhoogde risicobereidheid, dominantiedrang en verminderde impulsremming.
                    </p>
                </div>

                <!-- Card 2: Behandelplan & Medicatie -->
                <div class="bg-white dark:bg-slate-800/90 rounded-xl p-5 border-2 border-stone-200/90 dark:border-slate-700/80 shadow-2xs space-y-2.5 hover:border-rose-300 dark:hover:border-rose-500 transition-colors">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase text-rose-800 dark:text-rose-300 tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-pills text-rose-600 dark:text-rose-400"></i> Behandelplan &amp; Maatregel
                        </span>
                        <span class="px-2 py-0.5 rounded bg-rose-50 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300 text-[10px] font-mono font-black border border-rose-200 dark:border-rose-800/60">
                            ℞ MEDICATIE &amp; REGIME
                        </span>
                    </div>
                    <div class="text-xs font-bold text-slate-900 dark:text-slate-100 leading-relaxed bg-stone-50 dark:bg-slate-900/80 p-3 rounded-lg border border-stone-200 dark:border-slate-700">
                        <div class="text-[10px] uppercase font-black text-stone-400 dark:text-slate-400 mb-1">Voorgeschreven Kuur:</div>
                        {{ $psychiatricReport['medication'] ?? $psychiatricReport['treatment_plan'] }}
                    </div>
                    <p class="text-[11px] text-stone-500 dark:text-slate-400 font-medium">
                        <strong class="text-slate-700 dark:text-slate-300">Maatregel:</strong> {{ $psychiatricReport['treatment_plan'] }}
                    </p>
                </div>

                <!-- Card 3: Artsenobservatie (Full width) -->
                <div class="md:col-span-2 bg-white dark:bg-slate-800/90 rounded-xl p-5 border-2 border-stone-200/90 dark:border-slate-700/80 shadow-2xs space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase text-stone-600 dark:text-slate-300 tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-notes-medical text-indigo-600 dark:text-indigo-400"></i> Artsenobservatie &amp; Gedragsanalyse
                        </span>
                        <span class="text-[10px] text-stone-400 dark:text-slate-400 font-mono">Vertrouwelijk Inrichtingsverslag</span>
                    </div>
                    <div class="text-xs text-slate-800 dark:text-slate-200 leading-relaxed bg-stone-50/80 dark:bg-slate-900/80 p-4 rounded-xl border border-stone-200/80 dark:border-slate-700/80 italic font-serif relative">
                        <span class="text-2xl text-stone-300 dark:text-slate-600 absolute -top-1 left-2 font-serif select-none">&ldquo;</span>
                        <p class="relative z-10 pl-3">
                            {{ $psychiatricReport['doctor_notes'] }}
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-2 border-t border-stone-100 dark:border-slate-700/60 text-[10px] text-stone-500 dark:text-slate-400">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Afdeling Forensisch Toezicht &amp; Diagnostiek &bull; FPC De Balk</span>
                        </div>
                        <div class="font-mono flex items-center gap-2 text-indigo-900 dark:text-indigo-300 font-bold">
                            <i class="fa-solid fa-signature text-stone-400 dark:text-slate-500"></i>
                            <span>Digitale handtekening geverifieerd: {{ $psychiatricReport['doctor_name'] }}, arts</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barcode & Official Stamp Footer -->
            <div class="mt-6 pt-5 border-t-2 border-stone-300/80 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-stone-400 dark:text-slate-400">
                <div class="font-mono text-xs tracking-widest flex items-center gap-2 select-none">
                    <span class="text-stone-800 dark:text-slate-300 font-black text-sm tracking-tighter">||| | |||| ||| ||||| |||| || |||</span>
                    <span class="text-[10px] text-stone-500 dark:text-slate-400 font-mono">{{ $psychiatricReport['dossier_nr'] }}-DJI-NETHERLANDS</span>
                </div>
                <div class="text-[10px] text-stone-500 dark:text-slate-400 font-medium">
                    Dit document is uitsluitend bestemd voor de behandelstaf en de commissie van toezicht.
                </div>
            </div>
        </div>

        <!-- Interactive Explainer: Hoe werkt het rapport? -->
        <div x-data="{ openExplainer: false }" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-2xs">
            <button type="button" 
                    @click="openExplainer = !openExplainer" 
                    class="w-full px-5 py-4 flex items-center justify-between text-left hover:bg-slate-50 dark:hover:bg-slate-800/60 transition cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-circle-question"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wide">Hoe werkt het Psychiatrisch Rapport?</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Klik hier om te zien hoe jouw acties en statistieken dit verslag beïnvloeden.</p>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': openExplainer }"></i>
            </button>

            <div x-show="openExplainer" x-collapse x-cloak class="px-5 pb-5 pt-1 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-300 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-2">
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-lg border border-slate-200 dark:border-slate-700 space-y-1">
                        <span class="font-black text-slate-900 dark:text-white flex items-center gap-1.5 text-[11px]">
                            <i class="fa-solid fa-triangle-exclamation text-amber-500"></i> Dreigingsniveau
                        </span>
                        <p class="text-[11px] text-slate-600 dark:text-slate-300">
                            Bepaald door je <strong>Level</strong> en het aantal <strong>gewonnen gevechten</strong>. Stijgt van <em>Code Groen</em> (intake) naar <em>Geel</em>, <em>Oranje</em>, <em>Rood</em> tot het gevreesde <em>Code Zwart</em> (EBI status).
                        </p>
                    </div>

                    <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-lg border border-slate-200 dark:border-slate-700 space-y-1">
                        <span class="font-black text-slate-900 dark:text-white flex items-center gap-1.5 text-[11px]">
                            <i class="fa-solid fa-brain text-purple-600 dark:text-purple-400"></i> Dynamische Diagnose
                        </span>
                        <p class="text-[11px] text-slate-600 dark:text-slate-300">
                            Dr. Van Beek analyseert je gedrag: meer dan <strong>€100.000 vermogen</strong> geeft <em>Witteboorden-paranoia</em>, veel <strong>vechten</strong> geeft <em>Impuls-agressie</em>, en veel <strong>diefstallen</strong> geeft <em>Kleptomanie</em>.
                        </p>
                    </div>

                    <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-lg border border-slate-200 dark:border-slate-700 space-y-1">
                        <span class="font-black text-slate-900 dark:text-white flex items-center gap-1.5 text-[11px]">
                            <i class="fa-solid fa-prescription-bottle-medical text-rose-600 dark:text-rose-400"></i> Behandeling &amp; Medicatie
                        </span>
                        <p class="text-[11px] text-slate-600 dark:text-slate-300">
                            Elke diagnose krijgt een passend kliniekvoorschrift, variërend van lichte dagbesteding tot zware kalmeringsdepots en strikte fouillering bij de cellen.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab: Medailles & Prestaties (Badgeskast) -->
    <div x-show="activeTab === 'medailles'" x-cloak x-data="{ activeSet: 'all' }" class="bg-white rounded-xl shadow-xs border border-slate-200/80 p-6 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-medal text-amber-500"></i>
                    <span>Dossier Medailles & Prestaties van {{ $profileCharacter->name }}</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Officiële reputatiestempels, mijlpalen en prestaties behaald tijdens het verblijf in de inrichting.</p>
            </div>
            <span class="px-3 py-1 bg-purple-50 text-purple-700 border border-purple-200 rounded-xl text-xs font-extrabold flex items-center gap-1.5 self-start sm:self-auto">
                <i class="fa-solid fa-trophy text-amber-500"></i>
                <span>{{ collect($badges)->where('unlocked', true)->count() }} / {{ count($badges) }} Ontgrendeld</span>
            </span>
        </div>

        <!-- Totale Voortgangsbalk -->
        @php
            $unlockedCount = collect($badges)->where('unlocked', true)->count();
            $totalBadgesCount = count($badges);
            $totalPercentage = $totalBadgesCount > 0 ? (int) round(($unlockedCount / $totalBadgesCount) * 100) : 0;
        @endphp
        <div class="space-y-1.5 bg-slate-50 p-3.5 rounded-xl border border-slate-200/70">
            <div class="flex items-center justify-between text-xs font-bold">
                <span class="text-slate-600 flex items-center gap-1.5">
                    <i class="fa-solid fa-trophy text-amber-500"></i> Totale Medaille Collectie
                </span>
                <span class="text-purple-700 font-black">
                    {{ $totalPercentage }}% Voltooid ({{ $unlockedCount }}/{{ $totalBadgesCount }})
                </span>
            </div>
            <div class="w-full bg-slate-200/80 h-2 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-purple-500 via-indigo-500 to-amber-500 rounded-full transition-all duration-500" style="width: {{ $totalPercentage }}%"></div>
            </div>
        </div>

        <!-- Set Filter Tabs Bar -->
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-filter text-slate-400"></i> Selecteer Medaille Set
                </span>
                <span class="text-[11px] text-slate-500 font-semibold" x-text="activeSet === 'all' ? 'Alle sets getoond' : 'Gefilterd op set'"></span>
            </div>
            <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-thin">
                <button type="button" @click="activeSet = 'all'"
                    :class="activeSet === 'all' ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                    class="px-3.5 py-2 rounded-xl text-xs font-black transition flex items-center gap-2 shrink-0 cursor-pointer">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>Alle Sets</span>
                    <span :class="activeSet === 'all' ? 'bg-white/20 text-white' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200'" class="px-1.5 py-0.5 rounded-md text-[10px] font-extrabold">
                        {{ $unlockedCount }}/{{ $totalBadgesCount }}
                    </span>
                </button>

                @foreach($badgeSets as $sKey => $sData)
                    @php
                        $setBadges = collect($badges)->where('set_id', $sKey);
                        $setUnlocked = $setBadges->where('unlocked', true)->count();
                        $setTotal = $setBadges->count();
                    @endphp
                    <button type="button" @click="activeSet = '{{ $sKey }}'"
                        :class="activeSet === '{{ $sKey }}' ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                        class="px-3.5 py-2 rounded-xl text-xs font-black transition flex items-center gap-2 shrink-0 cursor-pointer">
                        <i class="{{ $sData['icon'] }}"></i>
                        <span>{{ $sData['name'] }}</span>
                        <span :class="activeSet === '{{ $sKey }}' ? 'bg-white/20 text-white' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200'" class="px-1.5 py-0.5 rounded-md text-[10px] font-extrabold">
                            {{ $setUnlocked }}/{{ $setTotal }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Medailles Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($badges as $badge)
            <div x-show="activeSet === 'all' || activeSet === '{{ $badge['set_id'] }}'"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="p-4 rounded-xl border transition flex flex-col justify-between gap-3 {{ $badge['unlocked'] ? 'bg-white border-slate-200 hover:border-purple-300 hover:shadow-xs' : 'bg-slate-50/70 border-slate-200/60 opacity-75' }}">
                <div class="flex items-start gap-3">
                    <!-- Eigen Icon Asset via Blade Component -->
                    <x-medal-badge :badge="$badge" size="md" />

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-1 flex-wrap">
                            @php
                                $tierStyle = match($badge['tier']) {
                                    'silver' => 'text-slate-700 bg-slate-100 border-slate-300',
                                    'gold' => 'text-amber-800 bg-amber-50 border-amber-300',
                                    'diamond' => 'text-cyan-800 bg-cyan-50 border-cyan-300',
                                    default => 'text-orange-800 bg-orange-50 border-orange-300',
                                };
                                $tierStars = str_repeat('★', $badge['tier_stars']);
                            @endphp
                            <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded border {{ $tierStyle }}">
                                {{ $tierStars }} {{ $badge['tier_name'] }}
                            </span>

                            @if($badge['unlocked'])
                                <span class="text-[9px] font-black uppercase text-emerald-600 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">
                                    <i class="fa-solid fa-check text-[8px]"></i> Behaald
                                </span>
                            @else
                                <span class="text-[9px] font-bold text-slate-400 bg-slate-100 px-1.5 py-0.2 rounded">
                                    <i class="fa-solid fa-lock text-[8px]"></i> Gesloten
                                </span>
                            @endif
                        </div>

                        <h4 class="text-xs font-extrabold text-slate-900 mt-1 truncate">
                            {{ $badge['name'] }}
                        </h4>
                        <p class="text-[11px] text-slate-500 mt-1 leading-snug">
                            {{ $badge['description'] }}
                        </p>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 space-y-1.5">
                    <div class="flex items-center justify-between text-[10px]">
                        <span class="font-bold text-slate-400 uppercase tracking-wider">Voortgang:</span>
                        <span class="font-extrabold {{ $badge['unlocked'] ? 'text-purple-700' : 'text-slate-600' }}">
                            {{ $badge['progress'] }}
                        </span>
                    </div>
                    <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-300 {{ $badge['unlocked'] ? 'bg-emerald-500' : 'bg-purple-500' }}" style="width: {{ $badge['progress_pct'] }}%"></div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Tab 2: Vriendenlijst (Volledig Overzicht) -->
    <div x-show="activeTab === 'vrienden'" class="bg-white rounded-xl shadow-xs border border-slate-200/80 p-6 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-users text-purple-600"></i>
                    <span>Vrienden & Bondgenoten van {{ $profileCharacter->name }} ({{ $friendsCount }})</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Medepatiënten waarmee een vertrouwensband is opgebouwd binnen de TBS kliniek.</p>
            </div>
            @if($isOwnProfile)
            <a href="{{ route('friends.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 text-xs font-bold transition">
                <i class="fa-solid fa-gear"></i> Vrienden Beheren & Toevoegen
            </a>
            @endif
        </div>

        @if($friends->isEmpty())
        <div class="p-10 text-center space-y-2 bg-slate-50 rounded-xl border border-slate-100">
            <div class="w-12 h-12 mx-auto rounded-full bg-purple-50 text-purple-400 flex items-center justify-center text-xl">
                <i class="fa-solid fa-user-group"></i>
            </div>
            <h4 class="text-xs font-extrabold text-slate-700">Geen vrienden gevonden</h4>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">
                Deze patiënt heeft momenteel nog geen officiële vrienden in het kliniekdossier.
            </p>
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($friends as $f)
            @if($f->friend)
            <div class="p-4 rounded-xl border border-slate-200/90 hover:border-purple-300 bg-white hover:bg-purple-50/20 transition flex flex-col justify-between gap-3 shadow-2xs group">
                <div class="flex items-center gap-3">
                    <a href="{{ route('profile.show', $f->friend->name) }}" class="relative shrink-0">
                        <img src="{{ $f->friend->avatar_url }}" alt="{{ $f->friend->name }}" class="w-12 h-12 rounded-xl object-cover border border-slate-200 shadow-2xs group-hover:scale-105 transition-transform">
                        <span class="absolute -bottom-1 -right-1 w-2.5 h-2.5 rounded-full border-2 border-white {{ $f->friend->isOnline() ? 'bg-emerald-500' : 'bg-slate-300' }}" title="{{ $f->friend->online_status_label }}"></span>
                        @if($f->is_bff)
                        <span class="absolute -top-1 -right-1 bg-pink-500 text-white text-[9px] w-4 h-4 rounded-full flex items-center justify-center shadow-xs" title="BFF (Beste Vriend)">
                            <i class="fa-solid fa-heart text-[8px]"></i>
                        </span>
                        @endif
                    </a>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('profile.show', $f->friend->name) }}" class="text-xs font-black text-slate-900 hover:text-purple-600 transition truncate block">
                                {{ $f->friend->name }}
                            </a>
                        </div>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-1.5 py-0.2 rounded">
                                Lvl {{ $f->friend->level }}
                            </span>
                            @if($f->friend->gang)
                            <a href="{{ route('gangs.show', $f->friend->gang->id) }}" class="text-[10px] font-bold text-purple-700 bg-purple-50 px-1.5 py-0.2 rounded border border-purple-100 hover:bg-purple-100 transition truncate" title="Bekijk {{ $f->friend->gang->name }}">
                                [{{ $f->friend->gang->tag }}]
                            </a>
                            @endif
                        </div>
                        <span class="text-[10px] text-purple-700 font-extrabold mt-1 block">
                            {{ number_format($f->friend->respect, 0, ',', '.') }} Respect
                        </span>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                    <a href="{{ route('profile.show', $f->friend->name) }}" class="text-[11px] font-bold text-slate-600 hover:text-purple-600 transition flex items-center gap-1">
                        <i class="fa-solid fa-address-card text-slate-400"></i> Profiel
                    </a>
                    @if(Auth::user()->character->id !== $f->friend->id)
                    <button type="button" 
                            onclick="if(window.openTBSDirectChat){ window.openTBSDirectChat({{ $f->friend->id }}, @js($f->friend->name), @js($f->friend->avatar_url)); } else { window.location.href='{{ route('chat.index', ['tab' => 'direct', 'recipient' => $f->friend->id]) }}'; }" 
                            class="text-[11px] font-bold text-emerald-600 hover:text-emerald-800 transition flex items-center gap-1 cursor-pointer">
                        <i class="fa-solid fa-comments text-emerald-500"></i> Privé Chat
                    </button>
                    @endif
                </div>
            </div>
            @endif
            @endforeach
        </div>
        @endif
    </div>

    <!-- Tab 3: Gastenboek -->
    <div x-show="activeTab === 'gastenboek'" class="bg-white rounded-xl shadow-xs border border-slate-200/80 p-6 space-y-6">
        <div>
            <h3 class="text-sm font-bold text-slate-900 mb-1">Gastenboek van {{ $profileCharacter->name }}</h3>
            <p class="text-xs text-slate-500">Laat een bericht achter op het prikbord van deze bewoner.</p>
        </div>

        <!-- Nieuw bericht formulier -->
        <form action="{{ route('guestbook.post', $profileCharacter->name) }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <textarea name="message" rows="3" required maxlength="500" placeholder="Schrijf een respectvolle (of dreigende) boodschap..." class="w-full text-xs p-3 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 resize-none"></textarea>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs px-5 py-2.5 rounded-lg transition shadow-xs flex items-center gap-2">
                    <i class="fa-solid fa-paper-plane text-[10px]"></i> Plaats Bericht
                </button>
            </div>
        </form>

        <!-- Berichtjes lijst -->
        <div class="divide-y divide-slate-100 pt-4">
            @forelse($guestbookEntries as $entry)
            @if($entry->author)
            <div class="py-4 flex items-start gap-4">
                <img src="{{ $entry->author->avatar_url }}" alt="{{ $entry->author->name }}" class="w-9 h-9 rounded-lg object-cover shrink-0 border border-slate-200">
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('profile.show', $entry->author->name) }}" class="text-xs font-bold text-slate-900 hover:text-indigo-600 transition">
                                {{ $entry->author->name }}
                            </a>
                            @if($entry->author->user && $entry->author->user->role !== 'user')
                                @php $gBadge = $entry->author->user->role_badge; @endphp
                                <span class="text-[9px] font-black uppercase tracking-wider {{ $gBadge['bg'] }} {{ $gBadge['text'] }} border {{ $gBadge['border'] }} px-1.5 py-0.2 rounded inline-flex items-center gap-1">
                                    <i class="fa-solid {{ $gBadge['icon'] }} text-[8px]"></i> {{ $gBadge['label'] }}
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-slate-400">{{ $entry->created_at->diffForHumans() }}</span>
                            @if($entry->author_character_id === Auth::user()->character->id || $entry->profile_character_id === Auth::user()->character->id || Auth::user()->canModerate())
                            <form action="{{ route('guestbook.destroy', $entry) }}" method="POST"
                                  data-confirm="{{ ($entry->author_character_id === Auth::user()->character->id || $entry->profile_character_id === Auth::user()->character->id) ? 'Weet je zeker dat je dit gastenboekbericht wilt verwijderen?' : 'Weet je zeker dat je dit gastenboekbericht als moderator/beheerder wilt verwijderen?' }}"
                                  data-confirm-title="Gastenboek Bericht Verwijderen"
                                  data-confirm-btn="Verwijderen"
                                  data-confirm-type="danger"
                                  data-confirm-icon="fa-trash-can"
                                  class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-rose-600 p-1 rounded-md hover:bg-slate-100 transition cursor-pointer" title="{{ Auth::user()->canModerate() ? 'Verwijderen als moderator/beheerder' : 'Verwijderen' }}">
                                    <i class="fa-regular fa-trash-can text-xs"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $entry->message }}</p>
                </div>
            </div>
            @endif
            @empty
            <div class="py-8 text-center text-slate-400 text-xs">
                Nog geen berichten in dit gastenboek. Wees de eerste die iets achterlaat!
            </div>
            @endforelse
        </div>
    </div>

    <!-- MODAL 1: Avatar Selector Modal (Diverse Generated Avatars) -->
    @if(Auth::user()->character->id === $profileCharacter->id)
    <div x-show="avatarModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80"
         x-data="{ 
             avatarTab: '{{ $profileCharacter->avatar_custom ? 'custom' : 'presets' }}',
             previewUrl: null,
             handleFileSelect(e) {
                 const file = e.target.files[0];
                 if (file) {
                     const reader = new FileReader();
                     reader.onload = (event) => {
                         this.previewUrl = event.target.result;
                     };
                     reader.readAsDataURL(file);
                 }
             }
         }">
        <div @click.away="avatarModalOpen = false" class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 max-w-lg w-full p-6 space-y-4 animate-in fade-in zoom-in-95 duration-150 transform-gpu will-change-transform">
            
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-camera text-purple-600"></i>
                        <span>Profielfoto Aanpassen</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Upload je eigen foto vanaf je apparaat of kies een kliniek avatar.</p>
                </div>
                <button @click="avatarModalOpen = false" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Tab Switcher -->
            <div class="flex rounded-xl bg-slate-100 dark:bg-slate-800 p-1 text-xs font-bold gap-1">
                <button type="button" 
                        @click="avatarTab = 'custom'" 
                        :class="avatarTab === 'custom' ? 'bg-white dark:bg-slate-900 text-purple-700 dark:text-purple-300 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                        class="flex-1 py-2 rounded-lg transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <span>📸 Eigen Foto Uploaden</span>
                </button>
                <button type="button" 
                        @click="avatarTab = 'presets'" 
                        :class="avatarTab === 'presets' ? 'bg-white dark:bg-slate-900 text-purple-700 dark:text-purple-300 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                        class="flex-1 py-2 rounded-lg transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-masks-theater"></i>
                    <span>🎭 Kliniek Avatars</span>
                </button>
            </div>

            <!-- TAB 1: Custom Upload -->
            <div x-show="avatarTab === 'custom'" class="space-y-4">
                <form action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    
                    <div class="flex flex-col sm:flex-row items-center gap-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-dashed border-slate-300 dark:border-slate-700">
                        <div class="relative w-24 h-24 rounded-2xl overflow-hidden bg-slate-200 dark:bg-slate-700 shrink-0 border-2 border-white dark:border-slate-800 shadow-sm flex items-center justify-center">
                            <template x-if="previewUrl">
                                <img :src="previewUrl" alt="Voorbeeld" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!previewUrl">
                                <img src="{{ $profileCharacter->avatar_url }}" alt="Huidig" class="w-full h-full object-cover">
                            </template>
                        </div>
                        <div class="flex-1 text-center sm:text-left space-y-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-900 dark:text-white mb-0.5">Kies een afbeelding</label>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">JPG, PNG, WEBP of GIF (max. 5MB). Direct zichtbaar op je hele profiel en chat!</p>
                            </div>
                            <input type="file" 
                                   name="custom_avatar" 
                                   accept="image/*" 
                                   required 
                                   @change="handleFileSelect($event)"
                                   class="block w-full text-xs text-slate-600 dark:text-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-600 file:text-white hover:file:bg-purple-700 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-1 cursor-pointer">
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                        <button @click="avatarModalOpen = false" type="button" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition cursor-pointer">
                            Annuleren
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-extrabold bg-purple-600 hover:bg-purple-700 text-white rounded-xl transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span>Eigen Foto Toepassen</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- TAB 2: Presets Grid -->
            <div x-show="avatarTab === 'presets'" class="space-y-4">
                <form action="{{ route('profile.avatar') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-2.5 max-h-80 overflow-y-auto custom-scrollbar p-1">
                        @php
                            $avatars = [
                                ['file' => 'assets/characters/avatar_male_1.jpg', 'title' => 'Man 1'],
                                ['file' => 'assets/characters/avatar_male_2.jpg', 'title' => 'Man 2'],
                                ['file' => 'assets/characters/avatar_male_3.jpg', 'title' => 'Man 3'],
                                ['file' => 'assets/characters/avatar_male_4.jpg', 'title' => 'Man 4'],
                                ['file' => 'assets/characters/avatar_male_5.jpg', 'title' => 'Man 5'],
                                ['file' => 'assets/characters/avatar_male_6.jpg', 'title' => 'Man 6'],
                                ['file' => 'assets/characters/avatar_female_1.jpg', 'title' => 'Vrouw 1'],
                                ['file' => 'assets/characters/avatar_female_2.jpg', 'title' => 'Vrouw 2'],
                                ['file' => 'assets/characters/avatar_female_3.jpg', 'title' => 'Vrouw 3'],
                                ['file' => 'assets/characters/avatar_female_4.jpg', 'title' => 'Vrouw 4'],
                                ['file' => 'assets/characters/avatar_female_5.jpg', 'title' => 'Vrouw 5'],
                                ['file' => 'assets/characters/avatar_female_6.jpg', 'title' => 'Vrouw 6'],
                                ['file' => 'assets/characters/henkie.jpg', 'title' => 'Henkie Legende'],
                            ];
                        @endphp

                        @foreach($avatars as $av)
                            <label class="cursor-pointer group relative flex flex-col items-center p-1.5 rounded-xl border-2 transition {{ (!$profileCharacter->avatar_custom && $profileCharacter->avatar === $av['file']) ? 'border-purple-600 bg-purple-50/50 dark:bg-purple-950/40 shadow-xs' : 'border-slate-200 dark:border-slate-700 hover:border-slate-400 bg-white dark:bg-slate-800' }}">
                                <input type="radio" name="avatar" value="{{ $av['file'] }}" class="sr-only" {{ (!$profileCharacter->avatar_custom && $profileCharacter->avatar === $av['file']) ? 'checked' : '' }}>
                                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-lg overflow-hidden relative shadow-2xs">
                                    <img src="{{ asset($av['file']) }}" alt="{{ $av['title'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                                    @if(!$profileCharacter->avatar_custom && $profileCharacter->avatar === $av['file'])
                                        <span class="absolute top-1 right-1 bg-purple-600 text-white w-5 h-5 rounded-full flex items-center justify-center text-[10px] shadow-xs">
                                            <i class="fa-solid fa-check"></i>
                                        </span>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3 mt-4">
                        <button @click="avatarModalOpen = false" type="button" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition cursor-pointer">
                            Annuleren
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold bg-slate-900 hover:bg-slate-800 dark:bg-purple-600 dark:hover:bg-purple-700 text-white rounded-xl transition shadow-xs cursor-pointer">
                            Preset Opslaan & Toepassen
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <!-- MODAL 2: Bio & Status Editor -->
    <div x-show="bioModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80">
        <div @click.away="bioModalOpen = false" class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full p-6 space-y-4 animate-in fade-in zoom-in-95 duration-150 transform-gpu will-change-transform">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Bio & Status Aanpassen</h3>
                <button @click="bioModalOpen = false" type="button" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status Citaat (Kort)</label>
                    <input type="text" name="status_message" value="{{ old('status_message', $profileCharacter->status_message) }}" maxlength="120" placeholder="bijv. Altijd op zoek naar een goede deal..." class="w-full text-xs p-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Patiënt Biografie</label>
                    <textarea name="bio" rows="5" maxlength="1000" placeholder="Vertel jouw verhaal in de TBS kliniek..." class="w-full text-xs p-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 resize-none">{{ old('bio', $profileCharacter->bio) }}</textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button @click="bioModalOpen = false" type="button" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                        Annuleren
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white rounded-lg transition shadow-xs">
                        Opslaan
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- MODAL 3: Title Selection Modal -->
    <div x-show="titleModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80">
        <div @click.away="titleModalOpen = false" class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full p-6 space-y-4 animate-in fade-in zoom-in-95 duration-150 transform-gpu will-change-transform">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-award text-purple-600"></i>
                        <span>Dossier Titel Kiezen</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Kies welke officiële status onder jouw naam getoond wordt.</p>
                </div>
                <button @click="titleModalOpen = false" type="button" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('profile.title') }}" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                    @foreach($availableTitles as $t)
                    <label class="p-3 rounded-xl border-2 transition flex items-center justify-between gap-3 {{ $t['unlocked'] ? 'cursor-pointer hover:border-purple-300 ' . (($profileCharacter->title === $t['id'] || (! $profileCharacter->title && $t['id'] === 'Modelpatiënt')) ? 'border-purple-600 bg-purple-50/50' : 'border-slate-200 bg-white') : 'opacity-50 bg-slate-50 border-slate-200 cursor-not-allowed' }}">
                        <div class="flex items-center gap-3">
                            <input type="radio" name="title" value="{{ $t['id'] }}" {{ $t['unlocked'] ? '' : 'disabled' }} {{ ($profileCharacter->title === $t['id'] || (! $profileCharacter->title && $t['id'] === 'Modelpatiënt')) ? 'checked' : '' }} class="text-purple-600 focus:ring-purple-500">
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <i class="{{ $t['icon'] }} text-xs text-purple-600"></i>
                                    <span class="text-xs font-black text-slate-900">{{ $t['name'] }}</span>
                                </div>
                                <span class="text-[10px] text-slate-500 block mt-0.5">{{ $t['description'] }}</span>
                            </div>
                        </div>
                        @if($t['unlocked'])
                            <span class="text-[9px] font-black uppercase text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 shrink-0">Vrijgespeeld</span>
                        @else
                            <span class="text-[9px] font-bold text-slate-400 bg-slate-200 px-1.5 py-0.5 rounded shrink-0"><i class="fa-solid fa-lock text-[8px]"></i> Vergrendeld</span>
                        @endif
                    </label>
                    @endforeach
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button @click="titleModalOpen = false" type="button" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                        Annuleren
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white rounded-lg transition shadow-xs cursor-pointer">
                        Titel Toepassen
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if(Auth::user()->canModerate() && $profileCharacter->id !== Auth::user()->character->id && $profileCharacter->user)
    <!-- STAFF WARN MODAL -->
    <div x-show="staffWarnModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80">
        <div @click.away="staffWarnModalOpen = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4 transform-gpu will-change-transform">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </span>
                    <div>
                        <h4 class="text-sm font-black text-slate-900">Officiële Waarschuwing Uitdelen</h4>
                        <p class="text-xs text-slate-500">Aan patiënt: <strong class="text-slate-800">{{ $profileCharacter->name }}</strong></p>
                    </div>
                </div>
                <button type="button" @click="staffWarnModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">&times;</button>
            </div>

            <form action="{{ route('admin.moderation.warn', $profileCharacter->user) }}" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700">Opgegeven Reden (verschijnt direct in het scherm van de speler)</label>
                    <textarea name="reason" rows="3" required placeholder="Bijv. Onbehoorlijk gedrag in de chat / overtreding regels..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-800 focus:outline-none focus:border-purple-600"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="staffWarnModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer">
                        Annuleren
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs transition cursor-pointer shadow-xs">
                        Waarschuwing Verzenden
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- STAFF BAN MODAL -->
    <div x-show="staffBanModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80">
        <div @click.away="staffBanModalOpen = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4 transform-gpu will-change-transform">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-ban"></i>
                    </span>
                    <div>
                        <h4 class="text-sm font-black text-slate-900">Speler Verbannen van het Spel</h4>
                        <p class="text-xs text-slate-500">Patiënt: <strong class="text-slate-800">{{ $profileCharacter->name }}</strong></p>
                    </div>
                </div>
                <button type="button" @click="staffBanModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">&times;</button>
            </div>

            <form action="{{ route('admin.moderation.ban', $profileCharacter->user) }}" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700">Duur van de verbanning</label>
                    <select name="duration" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs font-bold text-slate-800 focus:outline-none focus:border-purple-600">
                        <option value="1_hour">1 uur</option>
                        <option value="6_hours">6 uur</option>
                        <option value="12_hours">12 uur</option>
                        <option value="1_day" selected>1 dag (24 uur)</option>
                        <option value="3_days">3 dagen</option>
                        <option value="7_days">7 dagen (1 week)</option>
                        <option value="14_days">14 dagen</option>
                        <option value="30_days">30 dagen (1 maand)</option>
                        <option value="permanent">Permanent (Voor altijd)</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700">Opgegeven Reden van Verbanning</label>
                    <textarea name="reason" rows="3" required placeholder="Bijv. Herhaaldelijke overtreding van de kliniekregels / wangedrag..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-800 focus:outline-none focus:border-purple-600"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="staffBanModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer">
                        Annuleren
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-black text-xs transition cursor-pointer shadow-xs">
                        Definitief Verbannen
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if($isOwnProfile)
    <!-- ========================================== -->
    <!-- PROFILE PERSONALISATIE MODAL HUB           -->
    <!-- ========================================== -->
    <div x-show="customizeModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 flex items-center justify-center p-4 sm:p-6"
         @keydown.escape.window="customizeModalOpen = false">
        
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-2xl w-full shadow-2xl overflow-hidden relative transform-gpu will-change-transform"
             @click.away="customizeModalOpen = false">
            
            <!-- Modal Header -->
            <div class="p-6 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-purple-600 text-white flex items-center justify-center text-lg shadow-sm">
                        <i class="fa-solid fa-wand-magic-sparkles text-amber-300"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">Profiel Personalisatie Hub</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Personaliseer je stickers, achtergrondthema, titels en bio.</p>
                    </div>
                </div>
                <button type="button" @click="customizeModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-sm p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <!-- Tab Navigation Inside Modal -->
            <div class="flex border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-850 px-6 gap-2 text-xs font-bold overflow-x-auto">
                <button type="button" @click="activeCustomTab = 'name_style'" 
                        :class="activeCustomTab === 'name_style' ? 'border-pink-500 text-pink-600 dark:text-pink-400 border-b-2 font-black' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 font-bold'"
                        class="py-3 px-3 transition cursor-pointer shrink-0 flex items-center gap-1.5">
                    <i class="fa-solid fa-sparkles text-pink-500"></i>
                    <span>Naamkleur & Glow</span>
                </button>
                <button type="button" @click="activeCustomTab = 'sticker'" 
                        :class="activeCustomTab === 'sticker' ? 'border-purple-600 text-purple-600 dark:text-purple-400 border-b-2' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
                        class="py-3 px-3 transition cursor-pointer shrink-0 flex items-center gap-1.5">
                    <i class="fa-solid fa-certificate"></i>
                    <span>Sticker Vakje</span>
                </button>
                <button type="button" @click="activeCustomTab = 'theme'" 
                        :class="activeCustomTab === 'theme' ? 'border-purple-600 text-purple-600 dark:text-purple-400 border-b-2' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
                        class="py-3 px-3 transition cursor-pointer shrink-0 flex items-center gap-1.5">
                    <i class="fa-solid fa-palette"></i>
                    <span>Achtergrond & Thema</span>
                </button>
                <button type="button" @click="activeCustomTab = 'title'" 
                        :class="activeCustomTab === 'title' ? 'border-purple-600 text-purple-600 dark:text-purple-400 border-b-2' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
                        class="py-3 px-3 transition cursor-pointer shrink-0 flex items-center gap-1.5">
                    <i class="fa-solid fa-award"></i>
                    <span>Titels & Tags</span>
                </button>
                <button type="button" @click="activeCustomTab = 'bio'" 
                        :class="activeCustomTab === 'bio' ? 'border-purple-600 text-purple-600 dark:text-purple-400 border-b-2' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
                        class="py-3 px-3 transition cursor-pointer shrink-0 flex items-center gap-1.5">
                    <i class="fa-solid fa-pen"></i>
                    <span>Bio & Status</span>
                </button>
                <button type="button" @click="activeCustomTab = 'frame'" 
                        :class="activeCustomTab === 'frame' ? 'border-orange-500 text-orange-600 dark:text-orange-400 border-b-2 font-black' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 font-bold'"
                        class="py-3 px-3 transition cursor-pointer shrink-0 flex items-center gap-1.5">
                    <span>🎃</span>
                    <span>Profiel Rand / Frame</span>
                </button>
            </div>

            <!-- Form -->
            <form action="{{ route('profile.customize') }}" method="POST" class="p-6 space-y-5">
                @csrf
                <input type="hidden" name="name_style_submitted" value="1">

                <!-- TAB 0: NAAMKLEUR, KLEUROVERLOOP & GLOW -->
                <div x-show="activeCustomTab === 'name_style'" class="space-y-4 max-h-[68vh] overflow-y-auto pr-1 custom-scrollbar">
                    <!-- Live Name Preview Card -->
                    <div class="p-5 rounded-2xl border-2 border-purple-500/60 shadow-xl text-center space-y-2 relative overflow-hidden"
                         style="background: radial-gradient(circle at 50% 40%, #250b4a 0%, #0b0416 100%);">
                        <div class="text-[10px] uppercase tracking-widest font-black text-purple-300 flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-eye text-pink-400"></i>
                            <span>Live Voorbeeld (Balk, Profiel & Chat)</span>
                        </div>
                        <div class="py-2 flex items-center justify-center gap-2 flex-wrap">
                            <span class="text-2xl sm:text-3xl font-black px-1"
                                  :class="getPreviewNameClass()"
                                  :style="getPreviewNameStyle()">
                                {{ $profileCharacter->name }}
                            </span>
                            <template x-if="selectedSparkle">
                                <i class="fa-solid fa-wand-magic-sparkles text-amber-400 text-sm animate-pulse"></i>
                            </template>
                            @if($profileCharacter->vip_badge)
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase border inline-flex items-center gap-1 {{ $profileCharacter->vip_badge['class'] }}">
                                    <i class="fa-solid {{ $profileCharacter->vip_badge['icon'] }}"></i>
                                    <span>{{ $profileCharacter->vip_badge['short_label'] }}</span>
                                </span>
                            @endif
                        </div>
                        <p class="text-[11px] text-purple-200/75 font-medium">
                            Zo zien medepatiënten jouw naam en VIP-badge in de chat, op het plein en in de bovenbalk.
                        </p>
                    </div>

                    @if($profileCharacter->canCustomizeNameColor() || $profileCharacter->canUseSparkleEffect())
                        <!-- 1. KLEUROVERLOOP (GRADIENTS) -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-200 flex items-center gap-1.5">
                                    <i class="fa-solid fa-sliders text-purple-500"></i>
                                    <span>1. Kleuroverloop (Gradient Stijl)</span>
                                </label>
                                <span class="text-[10px] font-bold text-purple-600 dark:text-purple-400">Bewegend kleureffect</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                @foreach($nameGradientCatalog as $gKey => $grad)
                                    <label @click="selectedNameGradient = '{{ $gKey }}'"
                                           class="p-2.5 rounded-xl border-2 cursor-pointer transition flex items-center justify-between gap-2.5"
                                           :class="selectedNameGradient === '{{ $gKey }}' ? 'border-purple-600 bg-purple-50/70 dark:bg-purple-950/60 shadow-2xs' : 'border-slate-200 dark:border-slate-800 hover:border-purple-400'">
                                        <input type="radio" name="custom_name_gradient" value="{{ $gKey }}" x-model="selectedNameGradient" class="sr-only">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            @if($gKey === 'none')
                                                <span class="w-7 h-7 rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-500 flex items-center justify-center text-xs shrink-0">
                                                    <i class="fa-solid fa-droplet"></i>
                                                </span>
                                            @else
                                                <span class="w-7 h-7 rounded-lg shrink-0 shadow-xs border border-white/20" style="background: {{ $grad['css'] }}; background-size: 200% auto;"></span>
                                            @endif
                                            <div class="min-w-0">
                                                <span class="text-xs font-extrabold text-slate-900 dark:text-white block truncate">{{ $grad['name'] }}</span>
                                                <span class="text-[10px] text-slate-400 font-bold uppercase">{{ $grad['badge'] }}</span>
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-circle-check text-purple-600 dark:text-purple-400 text-xs shrink-0" x-show="selectedNameGradient === '{{ $gKey }}'"></i>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- 2. EFFEN NAAMKLEUR -->
                        <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-200 flex items-center gap-1.5">
                                    <i class="fa-solid fa-palette text-amber-500"></i>
                                    <span>2. Basis / Effen Naamkleur</span>
                                </label>
                                <button type="button" @click="selectedNameColor = ''; selectedNameGradient = 'none';" class="text-[10px] font-bold text-slate-400 hover:text-rose-500 cursor-pointer">
                                    Reset naar standaard
                                </button>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                @foreach($nameColorCatalog as $hex => $cInfo)
                                    <label @click="selectedNameColor = '{{ $hex }}'"
                                           class="p-2.5 rounded-xl border-2 cursor-pointer flex items-center gap-2 transition"
                                           :class="selectedNameColor === '{{ $hex }}' ? 'border-purple-600 bg-purple-50/70 dark:bg-purple-950/60 shadow-2xs' : 'border-slate-200 dark:border-slate-800 hover:border-purple-400'">
                                        <input type="radio" name="custom_name_color" value="{{ $hex }}" x-model="selectedNameColor" class="sr-only">
                                        <span class="w-4 h-4 rounded-full {{ $cInfo['class'] }} ring-1 ring-black/20 shrink-0"></span>
                                        <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 truncate">{{ $cInfo['name'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- 3. NAAM GLOW (GLOED INTENSITEIT) -->
                        <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                            <label class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-200 flex items-center gap-1.5">
                                <i class="fa-solid fa-sun text-cyan-400"></i>
                                <span>3. Naam Glow & Lichteffect</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                @foreach($nameGlowCatalog as $glowKey => $glowInfo)
                                    <label @click="selectedNameGlow = '{{ $glowKey }}'"
                                           class="p-2.5 rounded-xl border-2 cursor-pointer transition flex items-center justify-between gap-2"
                                           :class="selectedNameGlow === '{{ $glowKey }}' ? 'border-purple-600 bg-purple-50/70 dark:bg-purple-950/60 shadow-2xs' : 'border-slate-200 dark:border-slate-800 hover:border-purple-400'">
                                        <input type="radio" name="custom_name_glow" value="{{ $glowKey }}" x-model="selectedNameGlow" class="sr-only">
                                        <div class="min-w-0">
                                            <span class="text-xs font-extrabold text-slate-900 dark:text-white block">{{ $glowInfo['name'] }}</span>
                                            <span class="text-[10px] text-slate-400 block truncate">{{ $glowInfo['desc'] }}</span>
                                        </div>
                                        <i class="fa-solid fa-circle-check text-purple-600 dark:text-purple-400 text-xs shrink-0" x-show="selectedNameGlow === '{{ $glowKey }}'"></i>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- 4. GLITTER & SHIMMER SPARKLE BOOST -->
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                            @if($profileCharacter->canUseSparkleEffect())
                                <div class="p-3.5 rounded-2xl bg-purple-50/80 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800/60 flex items-center justify-between gap-4">
                                    <div class="space-y-0.5">
                                        <div class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                                            <i class="fa-solid fa-wand-magic-sparkles text-pink-500"></i>
                                            <span>Glitter Shimmer & Sparkle Icoon</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                            Voegt een vloeiende glans-animatie en glitter-icoon toe aan je naam.
                                        </p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                        <input type="checkbox" name="sparkle_enabled" value="1" x-model="selectedSparkle" class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                                    </label>
                                </div>
                            @else
                                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2.5">
                                        <i class="fa-solid fa-lock text-amber-500"></i>
                                        <span>Sparkle Shimmer is beschikbaar vanaf <strong>VIP Tier 3 (Kliniek Baas)</strong> & <strong>Lifetime</strong>.</span>
                                    </div>
                                    <a href="{{ route('vip.index', ['tab' => 'pakketten']) }}" class="px-3 py-1.5 rounded-lg bg-amber-500 text-slate-950 font-black text-[10px] shrink-0">
                                        Upgrade &raquo;
                                    </a>
                                </div>
                            @endif
                        </div>
                    @else
                        <!-- Locked Callout for Non-VIP / Tier 1 -->
                        <div class="p-5 rounded-2xl bg-slate-50 dark:bg-purple-950/30 border border-slate-200 dark:border-purple-800/50 text-center space-y-3">
                            <div class="w-11 h-11 mx-auto rounded-2xl bg-purple-100 dark:bg-purple-900/60 text-purple-600 dark:text-purple-300 flex items-center justify-center text-lg">
                                <i class="fa-solid fa-crown text-amber-400"></i>
                            </div>
                            <div class="space-y-1">
                                <h4 class="text-sm font-black text-slate-900 dark:text-white">Ontgrendel Naamkleuren, Kleuroverlopen & Glow</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto leading-relaxed">
                                    Met <strong>VIP Tier 2 (Gangster Elite)</strong> of hoger kies je uit 12 exclusieve neonkleuren, 8 kleuroverlopen en neon glows in de chat en op je profiel!
                                </p>
                            </div>
                            <a href="{{ route('vip.index', ['tab' => 'pakketten']) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-slate-950 font-black text-xs shadow-md transition">
                                <i class="fa-solid fa-crown"></i>
                                <span>Bekijk VIP Lidmaatschap Winkel</span>
                            </a>
                        </div>
                    @endif
                </div>

                <!-- TAB: PROFIEL RANDEN / FRAMES -->
                <div x-show="activeCustomTab === 'frame'" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Kies jouw actieve Halloween Avatar Frame:
                        </label>
                        <a href="{{ route('halloween.shop') }}" class="text-xs text-orange-600 dark:text-orange-400 hover:underline font-extrabold flex items-center gap-1">
                            <span>🎃 Halloween Shop</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-80 overflow-y-auto pr-1">
                        <!-- Option: Geen Rand -->
                        <label @click="selectedBorder = 'none'" 
                               class="p-3.5 rounded-2xl border-2 cursor-pointer transition flex items-center justify-between gap-3"
                               :class="selectedBorder === 'none' ? 'border-orange-500 bg-orange-50/50 dark:bg-orange-950/40 shadow-xs ring-2 ring-orange-500/40' : 'border-slate-200 dark:border-slate-800 hover:border-slate-300'">
                            <div class="flex items-center gap-3 min-w-0">
                                <input type="radio" name="equipped_border" value="none" x-model="selectedBorder" class="sr-only">
                                <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center text-sm shrink-0">
                                    <i class="fa-solid fa-ban"></i>
                                </div>
                                <div class="min-w-0">
                                    <span class="font-extrabold text-xs text-slate-800 dark:text-slate-200 block">Standaard (Geen Rand)</span>
                                    <span class="text-[11px] text-slate-400">Gebruik de standaard stijl zonder gloed</span>
                                </div>
                            </div>
                            <i x-show="selectedBorder === 'none'" class="fa-solid fa-circle-check text-orange-600 dark:text-orange-400 text-sm"></i>
                        </label>

                        <!-- All Halloween Borders -->
                        @foreach($allBorders as $bKey => $border)
                            @php 
                                $isOwned = in_array($bKey, $unlockedBorders ?? [], true);
                            @endphp
                            <label @if($isOwned) @click="selectedBorder = '{{ $bKey }}'" @endif
                                   class="p-3.5 rounded-2xl border-2 transition flex items-start justify-between gap-3 relative"
                                   :class="!{{ $isOwned ? 'true' : 'false' }} ? 'opacity-50 cursor-not-allowed border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/30' : (selectedBorder === '{{ $bKey }}' ? 'cursor-pointer border-orange-500 bg-orange-50/50 dark:bg-orange-950/40 shadow-xs ring-2 ring-orange-500/40' : 'cursor-pointer border-slate-200 dark:border-slate-800 hover:border-orange-300 dark:hover:border-orange-700')">
                                <div class="flex items-start gap-3 min-w-0 flex-1">
                                    <input type="radio" name="equipped_border" value="{{ $bKey }}" x-model="selectedBorder" {{ !$isOwned ? 'disabled' : '' }} class="sr-only">
                                    <!-- Mini Avatar Preview with Frame and Badge -->
                                    <div class="relative shrink-0">
                                        <div class="w-12 h-12 rounded-xl p-0.5 {{ $border['preview_class'] }}">
                                            <img src="{{ $profileCharacter->avatar_url }}" alt="{{ $border['name'] }}" class="w-full h-full rounded-xl object-cover bg-slate-900">
                                        </div>
                                        <span class="absolute -bottom-1 -right-1 text-sm select-none drop-shadow">{{ $border['emoji'] }}</span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-1">
                                            <span class="font-black text-xs text-slate-900 dark:text-white truncate">{{ $border['name'] }}</span>
                                            <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded-md bg-orange-100 dark:bg-orange-950/60 text-orange-700 dark:text-orange-300">
                                                {{ $border['badge'] }}
                                            </span>
                                        </div>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-2 leading-relaxed">
                                            {{ $border['description'] }}
                                        </p>
                                        @if(!$isOwned)
                                            <div class="mt-1 flex items-center gap-1.5 text-[10px] text-amber-600 dark:text-amber-400 font-bold">
                                                <i class="fa-solid fa-lock text-[9px]"></i>
                                                <span>{{ $border['price_seeds'] }} 🎃 in Halloween Shop</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <i x-show="selectedBorder === '{{ $bKey }}'" class="fa-solid fa-circle-check text-orange-600 dark:text-orange-400 text-sm shrink-0 mt-1"></i>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- TAB 1: STICKERS -->
                <div x-show="activeCustomTab === 'sticker'" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Kies een sticker voor je profielvakje:
                        </label>
                        <span class="text-xs text-purple-600 dark:text-purple-400 font-extrabold">
                            {{ count($unlockedStickers) }} / {{ count($allStickers) }} ontgrendeld
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-80 overflow-y-auto pr-1">
                        <!-- Option: Geen Sticker -->
                        <label @click="selectedSticker = 'none'" 
                               class="p-3.5 rounded-2xl border-2 cursor-pointer transition flex items-center justify-between gap-3"
                               :class="selectedSticker === 'none' ? 'border-purple-600 bg-purple-50/50 dark:bg-purple-950/40 shadow-xs ring-2 ring-purple-500/40' : 'border-slate-200 dark:border-slate-800 hover:border-slate-300'">
                            <div class="flex items-center gap-3 min-w-0">
                                <input type="radio" name="profile_sticker" value="none" x-model="selectedSticker" class="sr-only">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center text-sm shrink-0">
                                    <i class="fa-solid fa-ban"></i>
                                </div>
                                <div class="min-w-0">
                                    <span class="font-extrabold text-xs text-slate-800 dark:text-slate-200 block">Geen Sticker</span>
                                    <span class="text-[11px] text-slate-400">Laat het stickervakje leeg</span>
                                </div>
                            </div>
                            <i x-show="selectedSticker === 'none'" class="fa-solid fa-circle-check text-purple-600 dark:text-purple-400 text-sm"></i>
                        </label>

                        <!-- All Collectible Stickers -->
                        @foreach($allStickers as $sKey => $sticker)
                            @php 
                                $isUnlocked = in_array($sKey, $unlockedStickers, true);
                            @endphp
                            <label @if($isUnlocked) @click="selectedSticker = '{{ $sKey }}'" @endif
                                   class="p-3.5 rounded-2xl border-2 transition flex items-start justify-between gap-3 relative"
                                   :class="!{{ $isUnlocked ? 'true' : 'false' }} ? 'opacity-40 cursor-not-allowed border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/30' : (selectedSticker === '{{ $sKey }}' ? 'cursor-pointer border-purple-600 bg-purple-50/50 dark:bg-purple-950/40 shadow-xs ring-2 ring-purple-500/40' : 'cursor-pointer border-slate-200 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700')">
                                <div class="flex items-start gap-3 min-w-0 flex-1">
                                    <input type="radio" name="profile_sticker" value="{{ $sKey }}" x-model="selectedSticker" {{ !$isUnlocked ? 'disabled' : '' }} class="sr-only">
                                    <div class="w-10 h-10 rounded-xl {{ $sticker['badge_class'] }} flex items-center justify-center text-base shrink-0 border">
                                        <i class="fa-solid {{ $sticker['icon'] }}"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-1">
                                            <span class="font-black text-xs text-slate-900 dark:text-white truncate">{{ $sticker['name'] }}</span>
                                            <span class="text-[9px] font-black uppercase px-1.5 py-0.2 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-500">
                                                {{ $sticker['rarity'] }}
                                            </span>
                                        </div>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-2 leading-relaxed">
                                            {{ $sticker['description'] }}
                                        </p>
                                        @if(!$isUnlocked)
                                            <span class="text-[10px] text-rose-500 font-bold mt-1 block">🔒 Nog niet ontgrendeld</span>
                                        @endif
                                    </div>
                                </div>
                                <i x-show="selectedSticker === '{{ $sKey }}'" class="fa-solid fa-circle-check text-purple-600 dark:text-purple-400 text-sm shrink-0 mt-1"></i>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- TAB 2: THEME & BACKGROUNDS -->
                <div x-show="activeCustomTab === 'theme'" class="space-y-4">
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block mb-2">
                            Selecteer een Achtergrond Thema:
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-72 overflow-y-auto pr-1">
                            @foreach($allThemes as $tKey => $themeDef)
                                <label @click="selectedTheme = '{{ $tKey }}'"
                                       class="p-3 rounded-2xl border-2 cursor-pointer transition flex items-center gap-3"
                                       :class="selectedTheme === '{{ $tKey }}' ? 'border-purple-600 bg-purple-50/40 dark:bg-purple-950/40 shadow-xs ring-2 ring-purple-500/50' : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'">
                                    <input type="radio" name="profile_bg_theme" value="{{ $tKey }}" x-model="selectedTheme" class="sr-only">
                                    <div class="w-8 h-8 rounded-xl shrink-0 flex items-center justify-center border shadow-xs" style="background-color: {{ $themeDef['dot_color'] }};">
                                        <i class="fa-solid fa-sparkles text-white text-xs"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <span class="font-extrabold text-xs text-slate-900 dark:text-white block truncate">{{ $themeDef['name'] }}</span>
                                        <span class="text-[10px] text-slate-400">Header verloop & glow</span>
                                    </div>
                                    <i x-show="selectedTheme === '{{ $tKey }}'" class="fa-solid fa-circle-check text-purple-600 dark:text-purple-400 text-sm"></i>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Accent Kleur Chips -->
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block mb-2">
                            Profiel Accent Kleur:
                        </label>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['purple' => 'Paars', 'rose' => 'Rood', 'emerald' => 'Groen', 'amber' => 'Goud', 'blue' => 'Blauw', 'indigo' => 'Indigo', 'dark' => 'Zwart'] as $cKey => $cLabel)
                                <label @click="selectedColor = '{{ $cKey }}'"
                                       class="px-3 py-1.5 rounded-xl border cursor-pointer text-xs font-extrabold transition flex items-center gap-1.5"
                                       :class="selectedColor === '{{ $cKey }}' ? 'border-purple-600 bg-purple-600 text-white shadow-xs' : 'border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'">
                                    <input type="radio" name="profile_color" value="{{ $cKey }}" x-model="selectedColor" class="sr-only">
                                    <span>{{ $cLabel }}</span>
                                    <i x-show="selectedColor === '{{ $cKey }}'" class="fa-solid fa-check text-[10px]"></i>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- TAB 3: TITELS & TAGS -->
                <div x-show="activeCustomTab === 'title'" class="space-y-4">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block mb-1">
                        Selecteer je Actieve Dossier Titel:
                    </label>
                    <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                        @foreach($availableTitles as $tDef)
                            <label @if($tDef['unlocked']) @click="selectedTitle = '{{ addslashes($tDef['id']) }}'" @endif
                                   class="p-3 rounded-2xl border-2 transition flex items-center justify-between gap-3"
                                   :class="!{{ $tDef['unlocked'] ? 'true' : 'false' }} ? 'opacity-40 cursor-not-allowed border-slate-200 dark:border-slate-800' : (selectedTitle === '{{ addslashes($tDef['id']) }}' ? 'cursor-pointer border-purple-600 bg-purple-50/50 dark:bg-purple-950/40 ring-2 ring-purple-500/50' : 'cursor-pointer border-slate-200 dark:border-slate-800 hover:border-purple-300')">
                                <div class="flex items-center gap-3 min-w-0">
                                    <input type="radio" name="title" value="{{ $tDef['id'] }}" x-model="selectedTitle" {{ !$tDef['unlocked'] ? 'disabled' : '' }} class="sr-only">
                                    <div class="w-8 h-8 rounded-xl bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xs shrink-0">
                                        <i class="fa-solid fa-award"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-extrabold text-xs text-slate-900 dark:text-white block">{{ $tDef['name'] }}</span>
                                        <span class="text-[10px] text-slate-400 block">{{ $tDef['requirement'] ?? $tDef['description'] ?? '' }}</span>
                                    </div>
                                </div>
                                <div>
                                    <span x-show="selectedTitle === '{{ addslashes($tDef['id']) }}'" class="text-[10px] font-black text-purple-600 dark:text-purple-400 flex items-center gap-1">
                                        <i class="fa-solid fa-circle-check"></i> Geselecteerd
                                    </span>
                                    <span x-show="selectedTitle !== '{{ addslashes($tDef['id']) }}'">
                                        @if($tDef['unlocked'])
                                            <span class="text-[10px] font-black text-emerald-600">Beschikbaar</span>
                                        @else
                                            <span class="text-[10px] font-bold text-slate-400">Vergrendeld</span>
                                        @endif
                                    </span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- TAB 4: BIO & STATUS -->
                <div x-show="activeCustomTab === 'bio'" class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Statusregel (Korte shout op je profiel):</label>
                        <input type="text" name="status_message" maxlength="120" value="{{ $profileCharacter->status_message }}" placeholder="Bijv. Bezig met de volgende grote slag..." class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-3 text-xs font-semibold text-slate-900 dark:text-white focus:outline-none focus:border-purple-600">
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Persoonlijke Dossier Bio:</label>
                        <textarea name="bio" rows="4" maxlength="1000" placeholder="Schrijf je achtergrondverhaal, reputatie in de kliniek of boodschap aan rivalen..." class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-3 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-purple-600">{{ $profileCharacter->bio }}</textarea>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" @click="customizeModalOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition cursor-pointer">
                        Annuleren
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs uppercase tracking-wider transition shadow-md flex items-center gap-2 cursor-pointer active:scale-95">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span>Personalisatie Opslaan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection
