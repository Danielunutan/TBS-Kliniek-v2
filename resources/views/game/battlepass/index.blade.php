@extends('layouts.app')

@section('content')
@php
    $maxPassLevel = count($config);
    $unlockedCount = 0;
    $firstClaimableLevel = null;
    $highestUnlockedLevel = 1;

    foreach ($config as $lvlNum => $lvlData) {
        $unlocked = $character->level >= $lvlData['req_level'];
        if ($unlocked) {
            $unlockedCount++;
            $highestUnlockedLevel = $lvlNum;

            $freeUnclaimed = !in_array("lvl_{$lvlNum}_free", $claims, true);
            $vip1Unclaimed = ($isVip && $supporterTier >= 1) && !in_array("lvl_{$lvlNum}_vip1", $claims, true);
            $vip2Unclaimed = ($isVip && $supporterTier >= 2) && !in_array("lvl_{$lvlNum}_vip2", $claims, true);
            $vip3Unclaimed = ($isVip && $supporterTier >= 3) && !in_array("lvl_{$lvlNum}_vip3", $claims, true);

            if ($firstClaimableLevel === null && ($freeUnclaimed || $vip1Unclaimed || $vip2Unclaimed || $vip3Unclaimed)) {
                $firstClaimableLevel = $lvlNum;
            }
        }
    }

    $initialTargetLevel = $firstClaimableLevel ?? $highestUnlockedLevel;

    // Reward chip helper for visual badges (Cash, Bank, XP, Kracht, Respect, Kluiscodex, Sterren, Maskers, etc.)
    $rewardMeta = [
        'cash'          => ['icon' => 'fa-sack-dollar',      'label' => 'Cash',       'prefix' => '€', 'color' => 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-500/30'],
        'bank'          => ['icon' => 'fa-building-columns', 'label' => 'Bank',       'prefix' => '€', 'color' => 'bg-cyan-500/15 text-cyan-700 dark:text-cyan-300 border-cyan-500/30'],
        'xp'            => ['icon' => 'fa-bolt-lightning',   'label' => 'XP',         'prefix' => '+', 'color' => 'bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border-indigo-500/35'],
        'strength'      => ['icon' => 'fa-dumbbell',         'label' => 'Kracht',     'prefix' => '+', 'color' => 'bg-orange-500/15 text-orange-700 dark:text-orange-300 border-orange-500/35'],
        'respect'       => ['icon' => 'fa-handshake-simple', 'label' => 'Respect',    'prefix' => '+', 'color' => 'bg-fuchsia-500/15 text-fuchsia-700 dark:text-fuchsia-300 border-fuchsia-500/30'],
        'vault_tokens'  => ['icon' => 'fa-key',              'label' => 'Kluiscodex', 'prefix' => '',  'color' => 'bg-yellow-500/15 text-yellow-700 dark:text-yellow-300 border-yellow-500/35'],
        'stars'         => ['icon' => 'fa-star',             'label' => 'Sterren',    'prefix' => '',  'color' => 'bg-amber-500/15 text-amber-700 dark:text-amber-300 border-amber-500/35'],
        'masks'         => ['icon' => 'fa-mask',             'label' => 'Masker',     'prefix' => '',  'color' => 'bg-violet-500/15 text-violet-700 dark:text-violet-300 border-violet-500/30'],
        'unopened_bags' => ['icon' => 'fa-briefcase',        'label' => 'Koffer',     'prefix' => '',  'color' => 'bg-purple-500/15 text-purple-700 dark:text-purple-300 border-purple-500/30'],
        'xxl_cases'     => ['icon' => 'fa-box-open',         'label' => 'XXL Kist',   'prefix' => '',  'color' => 'bg-amber-500/20 text-amber-800 dark:text-amber-200 border-amber-400/50'],
        'bullets'       => ['icon' => 'fa-crosshairs',       'label' => 'Kogels',     'prefix' => '',  'color' => 'bg-rose-500/15 text-rose-700 dark:text-rose-300 border-rose-500/30'],
        'vip_points'    => ['icon' => 'fa-crown',            'label' => 'VIP Pnt',    'prefix' => '+', 'color' => 'bg-purple-500/15 text-purple-700 dark:text-purple-300 border-purple-500/30'],
        'energy'        => ['icon' => 'fa-bolt',             'label' => 'Energie',    'prefix' => '+', 'color' => 'bg-sky-500/15 text-sky-700 dark:text-sky-300 border-sky-500/30'],
        'health'        => ['icon' => 'fa-heart-pulse',      'label' => 'HP',         'prefix' => '+', 'color' => 'bg-red-500/15 text-red-700 dark:text-red-300 border-red-500/30'],
    ];

    $tracksMeta = [
        'free' => [
            'code'        => 'FREE',
            'badge'       => 'PAS 1 • GRATIS',
            'name'        => 'FREE Pass',
            'subtitle'    => 'Geld, XP, Kracht & Kluiscodex',
            'image'       => asset('assets/illustrations/bp_track_free.jpg'),
            'fallback_bg' => asset('assets/illustrations/banner_prison.jpg'),
            'unlocked'    => true,
            'req_text'    => 'Standaard Actief',
            'header_grad' => 'from-slate-800 via-purple-950/90 to-slate-900',
            'border'      => 'border-purple-300/70 dark:border-purple-800/70',
            'badge_cls'   => 'bg-purple-500/20 text-purple-200 border-purple-400/40',
            'btn_cls'     => 'bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white shadow-purple-500/25',
            'icon'        => 'fa-unlock-keyhole',
        ],
        'vip1' => [
            'code'        => 'VIP 1',
            'badge'       => 'PAS 2 • TIER 1',
            'name'        => 'VIP 1 Pass',
            'subtitle'    => 'Sterren, Kracht, Kluiscodex & Cash',
            'image'       => asset('assets/illustrations/bp_track_vip1.jpg'),
            'fallback_bg' => asset('assets/illustrations/banner_smuggle.jpg'),
            'unlocked'    => $isVip && $supporterTier >= 1,
            'req_text'    => 'Vereist VIP 1',
            'header_grad' => 'from-purple-900 via-indigo-950 to-slate-950',
            'border'      => 'border-violet-400/70 dark:border-violet-700/80',
            'badge_cls'   => 'bg-violet-500/25 text-violet-200 border-violet-400/50',
            'btn_cls'     => 'bg-gradient-to-r from-violet-600 to-purple-600 hover:from-violet-500 hover:to-purple-500 text-white shadow-violet-500/30',
            'icon'        => 'fa-star',
        ],
        'vip2' => [
            'code'        => 'VIP 2',
            'badge'       => 'PAS 3 • TIER 2',
            'name'        => 'VIP 2 Pass',
            'subtitle'    => 'Extra Sterren, Kracht, XP & Maskers',
            'image'       => asset('assets/illustrations/bp_track_vip2.jpg'),
            'fallback_bg' => asset('assets/illustrations/banner_gangs.jpg'),
            'unlocked'    => $isVip && $supporterTier >= 2,
            'req_text'    => 'Vereist VIP 2',
            'header_grad' => 'from-fuchsia-950 via-purple-950 to-slate-950',
            'border'      => 'border-fuchsia-400/70 dark:border-fuchsia-700/80',
            'badge_cls'   => 'bg-fuchsia-500/25 text-fuchsia-200 border-fuchsia-400/50',
            'btn_cls'     => 'bg-gradient-to-r from-fuchsia-600 to-purple-600 hover:from-fuchsia-500 hover:to-purple-500 text-white shadow-fuchsia-500/30',
            'icon'        => 'fa-gem',
        ],
        'vip3' => [
            'code'        => 'VIP 3',
            'badge'       => 'PAS 4 • TIER 3 BAAS',
            'name'        => 'VIP 3 Pass',
            'subtitle'    => 'Maximale Kracht, Kluiscodex & Kapitaal',
            'image'       => asset('assets/illustrations/bp_track_vip3.jpg'),
            'fallback_bg' => asset('assets/illustrations/banner_vault.jpg'),
            'unlocked'    => $isVip && $supporterTier >= 3,
            'req_text'    => 'Vereist VIP 3',
            'header_grad' => 'from-amber-950/90 via-purple-950 to-slate-950',
            'border'      => 'border-amber-400/80 dark:border-amber-500/80',
            'badge_cls'   => 'bg-amber-400/25 text-amber-200 border-amber-400/60',
            'btn_cls'     => 'btn-star-gold',
            'icon'        => 'fa-crown',
        ],
    ];
@endphp

<div class="space-y-5"
     x-data="{
        activeLevel: {{ $initialTargetLevel }},
        totalLevels: {{ $maxPassLevel }},
        scrollToLevel(lvl, smooth = true) {
            const target = Math.max(1, Math.min(this.totalLevels, lvl));
            this.activeLevel = target;
            const slider = this.$refs.levelCarousel;
            const slide = document.getElementById('bp-slide-' + target);
            if (slider && slide) {
                const scrollLeft = slide.offsetLeft - slider.offsetLeft - (slider.clientWidth / 2) + (slide.clientWidth / 2);
                slider.scrollTo({ left: Math.max(0, scrollLeft), behavior: smooth ? 'smooth' : 'instant' });
            }
            const pillStrip = this.$refs.pillStrip;
            const pill = document.getElementById('bp-pill-' + target);
            if (pillStrip && pill) {
                const pillLeft = pill.offsetLeft - pillStrip.offsetLeft - (pillStrip.clientWidth / 2) + (pill.clientWidth / 2);
                pillStrip.scrollTo({ left: Math.max(0, pillLeft), behavior: smooth ? 'smooth' : 'instant' });
            }
        },
        prevSlide() {
            if (this.activeLevel > 1) {
                this.scrollToLevel(this.activeLevel - 1);
            } else {
                this.scrollToLevel(this.totalLevels);
            }
        },
        nextSlide() {
            if (this.activeLevel < this.totalLevels) {
                this.scrollToLevel(this.activeLevel + 1);
            } else {
                this.scrollToLevel(1);
            }
        },
        onCarouselScroll() {
            const slider = this.$refs.levelCarousel;
            if (!slider) return;
            const center = slider.scrollLeft + (slider.clientWidth / 2);
            let closest = this.activeLevel;
            let minDiff = Infinity;
            for (let i = 1; i <= this.totalLevels; i++) {
                const el = document.getElementById('bp-slide-' + i);
                if (el) {
                    const elCenter = (el.offsetLeft - slider.offsetLeft) + (el.clientWidth / 2);
                    const diff = Math.abs(center - elCenter);
                    if (diff < minDiff) {
                        minDiff = diff;
                        closest = i;
                    }
                }
            }
            if (closest !== this.activeLevel) {
                this.activeLevel = closest;
            }
        },
        fireConfetti(mode = 'normal') {
            if (typeof window.tbsFireConfetti === 'function') {
                window.tbsFireConfetti(mode);
            }
        }
     }"
     x-init="setTimeout(() => scrollToLevel(activeLevel, false), 60)">

    <!-- PAARSE TBS KLINIEK HERO BANNER -->
    <div class="relative overflow-hidden rounded-3xl bg-slate-950 text-white border-2 border-purple-500/40 shadow-xl">
        <!-- TBS Purple Artwork Background -->
        <div class="absolute inset-0 bg-cover bg-center opacity-35 mix-blend-luminosity"
             style="background-image: url('{{ asset('assets/illustrations/tbs_purple_hero.jpg') }}');"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-purple-950/90 to-slate-950/85"></div>
        <div class="absolute -right-20 -top-20 w-72 h-72 bg-purple-600/25 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 w-72 h-72 bg-fuchsia-600/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 p-5 sm:p-7 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-3 max-w-2xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 bg-purple-500/25 text-purple-200 text-[11px] font-black uppercase tracking-wider px-3 py-1 rounded-full border border-purple-400/40">
                        <i class="fa-solid fa-calendar-days text-purple-300"></i>
                        <span>Seizoen 1 (3 Maanden) • {{ $maxPassLevel }} Levels</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 bg-amber-400/20 text-amber-300 text-[11px] font-black uppercase tracking-wider px-3 py-1 rounded-full border border-amber-400/40">
                        <i class="fa-solid fa-layer-group"></i>
                        <span>4 Passen: FREE • VIP 1 • VIP 2 • VIP 3</span>
                    </span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white flex items-center gap-3 flex-wrap">
                    <span>TBS Kliniek Battle Pass</span>
                    <span class="text-xs font-extrabold px-2.5 py-1 rounded-xl bg-purple-600/50 border border-purple-400/50 text-purple-100">
                        {{ $unlockedCount }} / {{ $maxPassLevel }} Niveaus Bereikt
                    </span>
                </h1>

                <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">
                    Speel 3 maanden lang door alle <strong class="text-white">{{ $maxPassLevel }} kliniek-niveaus</strong>! Gebruik de paarse pijlen om door de slider te bladeren en claim <strong class="text-purple-200">Geld, XP, Kracht, Respect, Kluiscodex, Sterren en schaarse Maskers</strong> op je actieve passen (<strong class="text-white">FREE, VIP 1, VIP 2 &amp; VIP 3</strong>).
                </p>

                <!-- Speler Status & Voortgangsbalk -->
                <div class="pt-1 flex flex-wrap items-center gap-2.5">
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-purple-950/80 border border-purple-500/40 text-xs font-bold text-purple-100">
                        <i class="fa-solid fa-user-shield text-purple-400"></i>
                        <span>Jouw Level: <strong class="text-white">{{ $character->level }}</strong></span>
                        <span class="text-purple-400">&bull;</span>
                        <span>{{ number_format($character->respect, 0, ',', '.') }} Respect</span>
                        <span class="text-purple-400">&bull;</span>
                        <span>{{ number_format($character->strength, 0, ',', '.') }} Kracht</span>
                    </div>

                    @if($isVip && $supporterTier >= 1)
                        <span class="px-3 py-1.5 rounded-xl bg-gradient-to-r from-purple-600 to-fuchsia-600 text-white text-xs font-black uppercase tracking-wider border border-purple-300/50 flex items-center gap-1.5 shadow-sm">
                            <i class="fa-solid fa-crown text-amber-300"></i>
                            <span>VIP {{ $supporterTier }} Ontgrendeld</span>
                        </span>
                    @else
                        <span class="px-3 py-1.5 rounded-xl bg-slate-900/90 border border-slate-700 text-slate-300 text-xs font-bold flex items-center gap-1.5">
                            <i class="fa-solid fa-unlock text-purple-400"></i>
                            <span>Actieve Pas: FREE</span>
                        </span>
                    @endif

                    @if(!$isVip || $supporterTier < 3)
                        <a href="{{ route('vip.index') }}" class="btn-star-gold px-3.5 py-1.5 rounded-xl text-xs uppercase tracking-wider transition flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-bolt"></i>
                            <span>Upgrade naar VIP {{ min(3, ($isVip ? $supporterTier + 1 : 1)) }}</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Rechts: Claim Alles + Confetti Box -->
            <div class="bg-purple-950/75 backdrop-blur-md border-2 border-purple-400/40 rounded-2xl p-4 sm:p-5 shrink-0 flex flex-col justify-between space-y-3 min-w-[265px] shadow-lg">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-purple-300 block">Klaar in je Kluis</span>
                        <div class="text-2xl font-black text-white mt-0.5 flex items-center gap-2">
                            <span>{{ $claimableCount }} {{ $claimableCount === 1 ? 'Beloning' : 'Beloningen' }}</span>
                            @if($claimableCount > 0)
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                            @endif
                        </div>
                    </div>
                    <button type="button"
                            @click="fireConfetti('normal')"
                            title="Schiet Confetti!"
                            class="w-11 h-11 rounded-2xl bg-purple-600/40 hover:bg-purple-500/60 border border-purple-400/50 flex items-center justify-center text-amber-300 text-lg transition cursor-pointer active:scale-90">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                    </button>
                </div>

                <!-- Seizoen Progressie Balk (3 Maanden / 60 Levels) -->
                <div class="space-y-1">
                    <div class="flex justify-between text-[10px] font-bold text-purple-200">
                        <span>3-Maanden Pas Voortgang ({{ $unlockedCount }}/{{ $maxPassLevel }})</span>
                        <span>{{ round(($unlockedCount / $maxPassLevel) * 100) }}%</span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-slate-900 overflow-hidden p-0.5 border border-purple-500/30">
                        <div class="h-full rounded-full bg-gradient-to-r from-purple-500 via-fuchsia-500 to-amber-400 transition-all duration-500"
                             style="width: {{ min(100, round(($unlockedCount / $maxPassLevel) * 100)) }}%"></div>
                    </div>
                </div>

                @if($claimableCount > 0)
                    <form action="{{ route('battlepass.claim_all') }}" method="POST" @submit="fireConfetti('mega')">
                        @csrf
                        <button type="submit" class="btn-star-gold w-full py-2.5 px-4 rounded-xl font-black text-xs uppercase tracking-wider transition flex items-center justify-center gap-2 cursor-pointer active:scale-95">
                            <i class="fa-solid fa-gifts"></i>
                            <span>Claim Alle {{ $claimableCount }} Prijzen!</span>
                        </button>
                    </form>
                @else
                    <button type="button"
                            @click="scrollToLevel({{ min($maxPassLevel, $highestUnlockedLevel + 1) }})"
                            class="w-full py-2.5 px-4 rounded-xl bg-purple-900/50 hover:bg-purple-800/60 border border-purple-500/40 text-purple-200 font-bold text-xs uppercase tracking-wider transition flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-crosshairs text-purple-300"></i>
                        <span>Bekijk Volgend Doel (Lvl {{ min($maxPassLevel, $highestUnlockedLevel + 1) }})</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- DE 4 BATTLE PASSEN OVERZICHT (FREE • VIP 1 • VIP 2 • VIP 3) MET PAARSE TBS ASSETS -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        @foreach($tracksMeta as $trackKey => $tMeta)
            <div class="relative overflow-hidden rounded-2xl border {{ $tMeta['border'] }} bg-slate-950 text-white p-3.5 sm:p-4 shadow-md group">
                <div class="absolute inset-0 bg-cover bg-center opacity-40 group-hover:scale-105 transition duration-500"
                     style="background-image: url('{{ $tMeta['image'] }}'), url('{{ $tMeta['fallback_bg'] }}');"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/80 to-purple-950/50"></div>

                <div class="relative z-10 flex flex-col justify-between h-full space-y-2.5">
                    <div class="flex items-center justify-between gap-1.5">
                        <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider border {{ $tMeta['badge_cls'] }}">
                            {{ $tMeta['code'] }}
                        </span>
                        @if($tMeta['unlocked'])
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-300 text-[10px] font-black uppercase">
                                <i class="fa-solid fa-check text-[9px]"></i> Actief
                            </span>
                        @else
                            <a href="{{ route('vip.index') }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-900/90 hover:bg-purple-900 border border-purple-400/40 text-purple-200 text-[10px] font-bold transition">
                                <i class="fa-solid fa-lock text-[9px] text-amber-400"></i> {{ $tMeta['req_text'] }}
                            </a>
                        @endif
                    </div>

                    <div class="flex items-center gap-2.5 pt-1">
                        <div class="w-10 h-10 rounded-xl bg-purple-600/30 border border-purple-400/40 flex items-center justify-center text-purple-200 shrink-0 shadow-inner">
                            <i class="fa-solid {{ $tMeta['icon'] }} text-base {{ $trackKey === 'vip3' ? 'text-amber-300' : '' }}"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-black text-sm text-white truncate">{{ $tMeta['name'] }}</h3>
                            <p class="text-[11px] text-purple-200/80 truncate">{{ $tMeta['subtitle'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- INTERACTIEVE NIVEAU BALK (60 LEVELS / 3 MAANDEN) + PIJLEN VOOR & ACHTER -->
    <div class="bg-white dark:bg-slate-900 border border-purple-200/80 dark:border-purple-900/60 rounded-2xl p-3 sm:p-4 shadow-sm space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="w-9 h-9 rounded-xl bg-purple-600 text-white font-black text-xs flex items-center justify-center shadow-xs">
                    <span x-text="activeLevel"></span>
                </span>
                <div>
                    <h2 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white leading-tight">
                        Niveau Kiezer &bull; <span class="text-purple-600 dark:text-purple-400" x-text="'Level ' + activeLevel + ' van ' + totalLevels"></span>
                    </h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        Spring direct naar een seizoensfase of blader met de grote paarse pijlen voor &amp; achter
                    </p>
                </div>
            </div>

            <!-- Snelle Maand / Fase Sprong Knoppen (voor 60 Levels) -->
            <div class="flex flex-wrap items-center gap-1.5">
                @if($firstClaimableLevel)
                    <button type="button"
                            @click="scrollToLevel({{ $firstClaimableLevel }})"
                            class="px-2.5 py-1 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30 text-[11px] font-black transition cursor-pointer flex items-center gap-1">
                        <i class="fa-solid fa-gift animate-bounce"></i>
                        <span>Claimbaar (Lvl {{ $firstClaimableLevel }})</span>
                    </button>
                @endif
                <button type="button"
                        @click="scrollToLevel({{ $highestUnlockedLevel }})"
                        class="px-2.5 py-1 rounded-xl bg-purple-500/15 hover:bg-purple-500/25 text-purple-700 dark:text-purple-300 border border-purple-500/30 text-[11px] font-bold transition cursor-pointer">
                    Huidig (Lvl {{ $highestUnlockedLevel }})
                </button>
                <button type="button" @click="scrollToLevel(1)"
                        :class="activeLevel >= 1 && activeLevel <= 20 ? 'bg-purple-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
                        class="px-2.5 py-1 rounded-xl text-[11px] font-extrabold transition cursor-pointer">
                    Maand 1 (1-20)
                </button>
                <button type="button" @click="scrollToLevel(21)"
                        :class="activeLevel >= 21 && activeLevel <= 40 ? 'bg-purple-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
                        class="px-2.5 py-1 rounded-xl text-[11px] font-extrabold transition cursor-pointer">
                    Maand 2 (21-40)
                </button>
                <button type="button" @click="scrollToLevel(41)"
                        :class="activeLevel >= 41 && activeLevel <= 60 ? 'bg-purple-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
                        class="px-2.5 py-1 rounded-xl text-[11px] font-extrabold transition cursor-pointer">
                    Maand 3 (41-60)
                </button>
            </div>
        </div>

        <!-- Level 1..60 Mini Pills met Voor/Achter Pijlen -->
        <div class="flex items-center gap-2">
            <button type="button"
                    @click="prevSlide()"
                    class="w-9 h-9 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-black flex items-center justify-center shrink-0 shadow-sm transition cursor-pointer active:scale-90"
                    title="Vorig Niveau">
                <i class="fa-solid fa-chevron-left text-xs"></i>
            </button>

            <div x-ref="pillStrip" class="flex items-center gap-1.5 overflow-x-auto scrollbar-none py-1 px-0.5 scroll-smooth flex-1">
                @foreach($config as $lvlNum => $lvlItem)
                    @php
                        $lvlUnlocked = $character->level >= $lvlItem['req_level'];
                        $hasUnclaimedOnLvl = $lvlUnlocked && (
                            !in_array("lvl_{$lvlNum}_free", $claims, true) ||
                            ($isVip && $supporterTier >= 1 && !in_array("lvl_{$lvlNum}_vip1", $claims, true)) ||
                            ($isVip && $supporterTier >= 2 && !in_array("lvl_{$lvlNum}_vip2", $claims, true)) ||
                            ($isVip && $supporterTier >= 3 && !in_array("lvl_{$lvlNum}_vip3", $claims, true))
                        );
                    @endphp
                    <button type="button"
                            id="bp-pill-{{ $lvlNum }}"
                            @click="scrollToLevel({{ $lvlNum }})"
                            :class="activeLevel === {{ $lvlNum }}
                                ? 'bg-gradient-to-br from-purple-600 to-fuchsia-600 text-white border-purple-300 scale-105 shadow-md ring-2 ring-purple-400/50'
                                : '{{ $lvlUnlocked ? 'bg-purple-50 dark:bg-slate-800 text-purple-900 dark:text-purple-200 border-purple-300 dark:border-purple-800/80 hover:bg-purple-100' : 'bg-slate-100 dark:bg-slate-800/50 text-slate-400 border-slate-200 dark:border-slate-800' }}'"
                            class="relative w-10 h-10 rounded-xl border font-black text-xs shrink-0 flex flex-col items-center justify-center transition cursor-pointer">
                        <span>{{ $lvlNum }}</span>
                        @if($hasUnclaimedOnLvl)
                            <span class="absolute -top-1 -right-1 w-3 h-3 rounded-full bg-amber-400 border-2 border-white dark:border-slate-900"></span>
                        @elseif($lvlUnlocked)
                            <i class="fa-solid fa-check text-[8px] text-emerald-400"></i>
                        @else
                            <i class="fa-solid fa-lock text-[8px] opacity-50"></i>
                        @endif
                    </button>
                @endforeach
            </div>

            <button type="button"
                    @click="nextSlide()"
                    class="w-9 h-9 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-black flex items-center justify-center shrink-0 shadow-sm transition cursor-pointer active:scale-90"
                    title="Volgend Niveau">
                <i class="fa-solid fa-chevron-right text-xs"></i>
            </button>
        </div>
    </div>

    <!-- HOOFD SLIDER MET 2 MOOIE GROTE PIJLEN (VOOR & ACHTERKANT) -->
    <div class="relative group/slider">

        <!-- Grote Pijl VOORKANT (Links) -->
        <button type="button"
                @click="prevSlide()"
                aria-label="Vorig Battle Pass Niveau"
                class="hidden sm:flex items-center justify-center w-12 h-24 rounded-2xl bg-gradient-to-b from-purple-600 via-purple-700 to-indigo-900 hover:from-purple-500 hover:to-indigo-700 text-white border-2 border-purple-300/60 shadow-xl shadow-purple-950/60 absolute -left-3 top-1/2 -translate-y-1/2 z-30 transition cursor-pointer active:scale-90">
            <i class="fa-solid fa-chevron-left text-xl"></i>
        </button>

        <!-- Grote Pijl ACHTERKANT (Rechts) -->
        <button type="button"
                @click="nextSlide()"
                aria-label="Volgend Battle Pass Niveau"
                class="hidden sm:flex items-center justify-center w-12 h-24 rounded-2xl bg-gradient-to-b from-purple-600 via-purple-700 to-indigo-900 hover:from-purple-500 hover:to-indigo-700 text-white border-2 border-purple-300/60 shadow-xl shadow-purple-950/60 absolute -right-3 top-1/2 -translate-y-1/2 z-30 transition cursor-pointer active:scale-90">
            <i class="fa-solid fa-chevron-right text-xl"></i>
        </button>

        <!-- Horizontale Snap Carousel voor de 60 Niveaus -->
        <div x-ref="levelCarousel"
             @scroll.debounce.40ms="onCarouselScroll()"
             class="flex gap-4 overflow-x-auto snap-x snap-mandatory scroll-smooth pb-3 pt-1 px-1 sm:px-8 scrollbar-none">

            @foreach($config as $lvlNum => $item)
                @php
                    $isLevelUnlocked = $character->level >= $item['req_level'];
                    $isMilestone10 = ($lvlNum % 10 === 0);
                    $isMilestone5 = ($lvlNum % 5 === 0);
                @endphp

                <!-- Niveau Slide Kaart -->
                <div id="bp-slide-{{ $lvlNum }}"
                     class="snap-center shrink-0 w-[92%] sm:w-[86%] lg:w-[78%] xl:w-[72%] rounded-3xl border-2 transition-all duration-300 overflow-hidden
                        {{ $isLevelUnlocked
                            ? 'bg-white dark:bg-slate-900 border-purple-400/80 dark:border-purple-600/80 shadow-lg'
                            : 'bg-white/90 dark:bg-slate-900/90 border-slate-200 dark:border-slate-800' }}"
                     :class="activeLevel === {{ $lvlNum }} ? 'ring-2 ring-purple-500/50 scale-[1.005]' : 'opacity-85 hover:opacity-100'">

                    <!-- Niveau Slide Top Banner (Paarse TBS Stijl) -->
                    <div class="relative overflow-hidden bg-gradient-to-r from-purple-950 via-indigo-950 to-slate-950 text-white px-4 sm:px-6 py-4 border-b border-purple-500/30">
                        <div class="absolute inset-0 bg-cover bg-center opacity-20 mix-blend-luminosity"
                             style="background-image: url('{{ asset('assets/illustrations/tbs_purple_hero_boss.jpg') }}');"></div>

                        <div class="relative z-10 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3.5">
                                <div class="w-12 h-12 rounded-2xl {{ $isLevelUnlocked ? 'bg-gradient-to-br from-purple-500 to-fuchsia-600 text-white border-2 border-purple-200/60 shadow-lg shadow-purple-600/40' : 'bg-slate-800 text-slate-400 border border-slate-700' }} flex flex-col items-center justify-center font-black shrink-0">
                                    <span class="text-[9px] uppercase tracking-widest opacity-80 leading-none">LVL</span>
                                    <span class="text-lg leading-tight">{{ $lvlNum }}</span>
                                </div>

                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="text-base sm:text-lg font-black text-white tracking-tight">
                                            TBS Dossier Niveau {{ $lvlNum }}
                                        </h3>
                                        @if($lvlNum === $maxPassLevel)
                                            <span class="px-2.5 py-0.5 rounded-full bg-amber-400 text-slate-950 text-[10px] font-black uppercase tracking-wider">
                                                👑 Level 60 Seizoen Finale
                                            </span>
                                        @elseif($isMilestone10)
                                            <span class="px-2.5 py-0.5 rounded-full bg-amber-400/25 border border-amber-400/60 text-amber-200 text-[10px] font-black uppercase">
                                                🏆 Hoofdmijlpaal
                                            </span>
                                        @elseif($isMilestone5)
                                            <span class="px-2.5 py-0.5 rounded-full bg-purple-500/30 border border-purple-300/50 text-purple-200 text-[10px] font-black uppercase">
                                                ⭐ Kluis Mijlpaal
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-purple-200/80 font-medium mt-0.5">
                                        Vereist Speler Level {{ $item['req_level'] }}
                                        @if($item['req_respect'] > 0)
                                            &bull; Aanbevolen: {{ number_format($item['req_respect'], 0, ',', '.') }} Respect
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                @if($isLevelUnlocked)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-500/20 border border-emerald-400/50 text-emerald-300 text-xs font-black uppercase tracking-wider">
                                        <i class="fa-solid fa-lock-open"></i> Ontgrendeld
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-800/90 border border-slate-700 text-slate-300 text-xs font-bold">
                                        <i class="fa-solid fa-lock text-amber-400"></i> Nog {{ max(1, $item['req_level'] - $character->level) }} {{ ($item['req_level'] - $character->level) === 1 ? 'level' : 'levels' }} nodig
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- De 4 Passen Rijen / Kolommen (FREE, VIP 1, VIP 2, VIP 3) -->
                    <div class="p-4 sm:p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                        @foreach(['free', 'vip1', 'vip2', 'vip3'] as $tKey)
                            @php
                                $tData = $item['tracks'][$tKey];
                                $tInfo = $tracksMeta[$tKey];
                                $isClaimed = in_array("lvl_{$lvlNum}_{$tKey}", $claims, true);
                                $canClaim = $isLevelUnlocked && $tInfo['unlocked'] && !$isClaimed;
                            @endphp

                            <div class="rounded-2xl border-2 flex flex-col justify-between overflow-hidden transition-all duration-200
                                {{ $isClaimed
                                    ? 'bg-slate-50/70 dark:bg-slate-800/40 border-slate-200 dark:border-slate-800 opacity-75'
                                    : ($canClaim
                                        ? 'bg-purple-50/60 dark:bg-purple-950/35 border-purple-400 dark:border-purple-500 shadow-md ring-1 ring-purple-400/40'
                                        : 'bg-slate-50/50 dark:bg-slate-900/60 border-slate-200/80 dark:border-slate-800') }}">

                                <!-- Pas Mini Artwork Header -->
                                <div class="relative h-20 overflow-hidden bg-slate-950 p-3 flex flex-col justify-between">
                                    <div class="absolute inset-0 bg-cover bg-center opacity-50"
                                         style="background-image: url('{{ $tInfo['image'] }}'), url('{{ $tInfo['fallback_bg'] }}');"></div>
                                    <div class="absolute inset-0 bg-gradient-to-r {{ $tInfo['header_grad'] }} opacity-80"></div>

                                    <div class="relative z-10 flex items-center justify-between gap-1">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider border {{ $tInfo['badge_cls'] }}">
                                            {{ $tInfo['code'] }}
                                        </span>

                                        @if($isClaimed)
                                            <span class="w-6 h-6 rounded-full bg-emerald-500 text-slate-950 flex items-center justify-center text-xs font-black shadow">
                                                <i class="fa-solid fa-check"></i>
                                            </span>
                                        @elseif($canClaim)
                                            <span class="px-2 py-0.5 rounded-full bg-emerald-400 text-slate-950 text-[9px] font-black uppercase animate-pulse">
                                                Klaar!
                                            </span>
                                        @else
                                            <span class="w-6 h-6 rounded-full bg-slate-900/80 border border-white/15 text-slate-300 flex items-center justify-center text-[10px]">
                                                <i class="fa-solid fa-lock"></i>
                                            </span>
                                        @endif
                                    </div>

                                    <div class="relative z-10 flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-white/15 backdrop-blur-xs border border-white/20 flex items-center justify-center text-white text-xs shrink-0">
                                            <i class="fa-solid {{ $tData['icon'] }}"></i>
                                        </div>
                                        <h4 class="font-black text-xs text-white truncate drop-shadow">
                                            {{ $tData['title'] }}
                                        </h4>
                                    </div>
                                </div>

                                <!-- Visuele Beloning Chips -->
                                <div class="p-3.5 space-y-3 flex-1 flex flex-col justify-between">
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($tData['rewards'] as $rKey => $rVal)
                                            @php
                                                $meta = $rewardMeta[$rKey] ?? ['icon' => 'fa-gift', 'label' => ucfirst($rKey), 'prefix' => '', 'color' => 'bg-purple-500/15 text-purple-300 border-purple-500/30'];
                                                $formattedVal = number_format($rVal, 0, ',', '.');
                                                $chipLabel = ($rKey === 'masks' && $rVal > 1) ? 'Maskers' : $meta['label'];
                                                $chipLabel = ($rKey === 'stars' && $rVal === 1) ? 'Ster' : $chipLabel;
                                            @endphp
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg border text-[11px] font-extrabold {{ $meta['color'] }}">
                                                <i class="fa-solid {{ $meta['icon'] }} text-[10px]"></i>
                                                <span>{{ $meta['prefix'] }}{{ $formattedVal }} {{ $chipLabel }}</span>
                                            </span>
                                        @endforeach
                                    </div>

                                    <!-- Actieknop onderaan elk pas-vak -->
                                    <div class="pt-1">
                                        @if($isClaimed)
                                            <div class="w-full py-2 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-[11px] font-black uppercase tracking-wider flex items-center justify-center gap-1.5">
                                                <i class="fa-solid fa-circle-check"></i>
                                                <span>Geclaimd</span>
                                            </div>
                                        @elseif($canClaim)
                                            <form action="{{ route('battlepass.claim') }}" method="POST" @submit="fireConfetti('normal')">
                                                @csrf
                                                <input type="hidden" name="level" value="{{ $lvlNum }}">
                                                <input type="hidden" name="track" value="{{ $tKey }}">
                                                <button type="submit" class="w-full py-2 rounded-xl {{ $tInfo['btn_cls'] }} text-[11px] font-black uppercase tracking-wider transition shadow-md cursor-pointer active:scale-95 flex items-center justify-center gap-1.5">
                                                    <i class="fa-solid fa-gift"></i>
                                                    <span>Claim {{ $tInfo['code'] }}</span>
                                                </button>
                                            </form>
                                        @elseif(!$tInfo['unlocked'])
                                            <a href="{{ route('vip.index') }}" class="w-full py-2 rounded-xl bg-purple-950/80 hover:bg-purple-900 text-purple-200 border border-purple-500/40 text-[10px] font-black uppercase tracking-wider flex items-center justify-center gap-1.5 transition">
                                                <i class="fa-solid fa-lock text-amber-400 text-[9px]"></i>
                                                <span>Ontgrendel {{ $tInfo['code'] }}</span>
                                            </a>
                                        @else
                                            <div class="w-full py-2 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 border border-slate-200 dark:border-slate-700 text-[10px] font-bold flex items-center justify-center gap-1.5">
                                                <i class="fa-solid fa-lock text-[9px]"></i>
                                                <span>Vereist Level {{ $item['req_level'] }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Onder-Navigatie Pijlen (Voor en Achter) binnen de kaart -->
                    <div class="px-4 py-3 bg-slate-50 dark:bg-slate-950/60 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-between gap-2">
                        <button type="button"
                                @click="prevSlide()"
                                class="px-3.5 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-black flex items-center gap-1.5 shadow-xs transition cursor-pointer active:scale-95">
                            <i class="fa-solid fa-arrow-left"></i>
                            <span>Vorig Niveau</span>
                        </button>

                        <span class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400">
                            Niveau {{ $lvlNum }} / {{ $maxPassLevel }}
                        </span>

                        <button type="button"
                                @click="nextSlide()"
                                class="px-3.5 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-black flex items-center gap-1.5 shadow-xs transition cursor-pointer active:scale-95">
                            <span>Volgend Niveau</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            @endforeach

        </div>
    </div>

</div>
@endsection
