@extends('layouts.app')

@section('content')
@php
    $maxPassLevel = count($config);
    $unlockedCount = min($maxPassLevel, max(0, (int) $character->level));
    $highestUnlockedLevel = max(1, $unlockedCount);
    $claimLookup = array_flip($claims);

    $firstClaimableLevel = null;
    for ($lvlNum = 1; $lvlNum <= $unlockedCount; $lvlNum++) {
        $freeUnclaimed = !isset($claimLookup["lvl_{$lvlNum}_free"]);
        $vip1Unclaimed = ($isVip && $supporterTier >= 1) && !isset($claimLookup["lvl_{$lvlNum}_vip1"]);
        $vip2Unclaimed = ($isVip && $supporterTier >= 2) && !isset($claimLookup["lvl_{$lvlNum}_vip2"]);
        $vip3Unclaimed = ($isVip && $supporterTier >= 3) && !isset($claimLookup["lvl_{$lvlNum}_vip3"]);

        if ($freeUnclaimed || $vip1Unclaimed || $vip2Unclaimed || $vip3Unclaimed) {
            $firstClaimableLevel = $lvlNum;
            break;
        }
    }

    $initialTargetLevel = $firstClaimableLevel ?? $highestUnlockedLevel;

    // Compact JSON payload for ultra-fast client-side Alpine rendering (only 1 active card in DOM!)
    $levelsPayload = [];
    foreach ($config as $lvlNum => $lvlData) {
        $tracksPayload = [];
        foreach (['free', 'vip1', 'vip2', 'vip3'] as $tKey) {
            $t = $lvlData['tracks'][$tKey];
            $chips = [];
            foreach ($t['rewards'] as $rKey => $rVal) {
                $chips[] = [
                    'key' => $rKey,
                    'val' => number_format($rVal, 0, ',', '.'),
                    'raw' => $rVal,
                ];
            }
            $tracksPayload[$tKey] = [
                'title' => $t['title'],
                'icon' => $t['icon'],
                'chips' => $chips,
            ];
        }
        $levelsPayload[$lvlNum] = [
            'level' => $lvlNum,
            'req_level' => $lvlData['req_level'],
            'req_respect' => number_format($lvlData['req_respect'], 0, ',', '.'),
            'has_respect' => $lvlData['req_respect'] > 0,
            'tracks' => $tracksPayload,
        ];
    }

    $tracksMeta = [
        'free' => [
            'code'        => 'FREE',
            'rank_title'  => 'FREE Pass (Gratis)',
            'badge'       => 'PAS 1 • GRATIS',
            'name'        => 'FREE Pass',
            'subtitle'    => 'Geld, XP, Kracht & Codes',
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
            'code'        => 'VIP 1 • TBS V.I.P. LID',
            'rank_title'  => 'TBS V.I.P. Lid (VIP 1)',
            'badge'       => 'PAS 2 • VIP 1',
            'name'        => 'TBS V.I.P. Lid Pass',
            'subtitle'    => 'Sterren, Kracht, Codes & Cash',
            'image'       => asset('assets/illustrations/bp_track_vip1.jpg'),
            'fallback_bg' => asset('assets/illustrations/banner_smuggle.jpg'),
            'unlocked'    => $isVip && $supporterTier >= 1,
            'req_text'    => 'Vereist TBS V.I.P. Lid',
            'header_grad' => 'from-purple-900 via-indigo-950 to-slate-950',
            'border'      => 'border-violet-400/70 dark:border-violet-700/80',
            'badge_cls'   => 'bg-violet-500/25 text-violet-200 border-violet-400/50',
            'btn_cls'     => 'bg-gradient-to-r from-violet-600 to-purple-600 hover:from-violet-500 hover:to-purple-500 text-white shadow-violet-500/30',
            'icon'        => 'fa-star',
        ],
        'vip2' => [
            'code'        => 'VIP 2 • GANGSTER ELITE',
            'rank_title'  => 'Gangster Elite V.I.P. (VIP 2)',
            'badge'       => 'PAS 3 • VIP 2',
            'name'        => 'Gangster Elite V.I.P. Pass',
            'subtitle'    => 'Extra Sterren, Kracht, XP & Maskers',
            'image'       => asset('assets/illustrations/bp_track_vip2.jpg'),
            'fallback_bg' => asset('assets/illustrations/banner_gangs.jpg'),
            'unlocked'    => $isVip && $supporterTier >= 2,
            'req_text'    => 'Vereist Gangster Elite V.I.P.',
            'header_grad' => 'from-fuchsia-950 via-purple-950 to-slate-950',
            'border'      => 'border-fuchsia-400/70 dark:border-fuchsia-700/80',
            'badge_cls'   => 'bg-fuchsia-500/25 text-fuchsia-200 border-fuchsia-400/50',
            'btn_cls'     => 'bg-gradient-to-r from-fuchsia-600 to-purple-600 hover:from-fuchsia-500 hover:to-purple-500 text-white shadow-fuchsia-500/30',
            'icon'        => 'fa-gem',
        ],
        'vip3' => [
            'code'        => 'VIP 3 • KLINIEK BAAS',
            'rank_title'  => 'Kliniek Baas V.I.P. (VIP 3)',
            'badge'       => 'PAS 4 • VIP 3 BAAS',
            'name'        => 'Kliniek Baas V.I.P. Pass',
            'subtitle'    => 'Maximale Kracht, Codes & Kapitaal',
            'image'       => asset('assets/illustrations/bp_track_vip3.jpg'),
            'fallback_bg' => asset('assets/illustrations/banner_vault.jpg'),
            'unlocked'    => $isVip && $supporterTier >= 3,
            'req_text'    => 'Vereist Kliniek Baas V.I.P.',
            'header_grad' => 'from-amber-950/90 via-purple-950 to-slate-950',
            'border'      => 'border-amber-400/80 dark:border-amber-500/80',
            'badge_cls'   => 'bg-amber-400/25 text-amber-200 border-amber-400/60',
            'btn_cls'     => 'btn-star-gold',
            'icon'        => 'fa-crown',
        ],
    ];
    $bpFrontendPayload = [
        'levels' => $levelsPayload,
        'claims' => array_values($claims),
        'playerLevel' => (int) $character->level,
        'playerRespect' => number_format($character->respect, 0, ',', '.'),
        'playerStrength' => number_format($character->strength, 0, ',', '.'),
        'isVip' => (bool) $isVip,
        'supporterTier' => (int) $supporterTier,
        'claimableCount' => (int) $claimableCount,
        'initialLevel' => (int) $initialTargetLevel,
        'maxLevel' => (int) $maxPassLevel,
        'claimUrl' => route('battlepass.claim'),
        'claimAllUrl' => route('battlepass.claim_all'),
        'vipShopUrl' => route('vip.index', ['tab' => 'pakketten']),
    ];
@endphp

<script>
    window.__TBS_BP_DATA__ = @json($bpFrontendPayload);
</script>

<div class="space-y-5"
     x-data="{
        activeLevel: window.__TBS_BP_DATA__.initialLevel,
        totalLevels: window.__TBS_BP_DATA__.maxLevel,
        levels: window.__TBS_BP_DATA__.levels,
        claimedMap: Object.fromEntries((window.__TBS_BP_DATA__.claims || []).map(k => [k, true])),
        playerLevel: window.__TBS_BP_DATA__.playerLevel,
        playerRespect: window.__TBS_BP_DATA__.playerRespect,
        playerStrength: window.__TBS_BP_DATA__.playerStrength,
        isVip: window.__TBS_BP_DATA__.isVip,
        supporterTier: window.__TBS_BP_DATA__.supporterTier,
        claimableCount: window.__TBS_BP_DATA__.claimableCount,
        busyKey: null,
        touchStartX: 0,

        get currentItem() {
            return this.levels[this.activeLevel] || this.levels[1];
        },
        get isCurrentUnlocked() {
            return this.playerLevel >= this.currentItem.req_level;
        },
        get rangeStart() {
            return Math.floor((this.activeLevel - 1) / 30) * 30 + 1;
        },
        get visiblePills() {
            const start = this.rangeStart;
            const arr = [];
            for (let i = start; i <= Math.min(this.totalLevels, start + 29); i++) {
                arr.push(i);
            }
            return arr;
        },
        isClaimed(lvl, track) {
            return !!this.claimedMap['lvl_' + lvl + '_' + track];
        },
        isTrackUnlocked(track) {
            if (track === 'free') return true;
            if (track === 'vip1') return this.isVip && this.supporterTier >= 1;
            if (track === 'vip2') return this.isVip && this.supporterTier >= 2;
            if (track === 'vip3') return this.isVip && this.supporterTier >= 3;
            return false;
        },
        canClaim(lvl, track) {
            return (this.playerLevel >= lvl) && this.isTrackUnlocked(track) && !this.isClaimed(lvl, track);
        },
        hasUnclaimedOnLevel(lvl) {
            if (this.playerLevel < lvl) return false;
            if (!this.isClaimed(lvl, 'free')) return true;
            if (this.isVip && this.supporterTier >= 1 && !this.isClaimed(lvl, 'vip1')) return true;
            if (this.isVip && this.supporterTier >= 2 && !this.isClaimed(lvl, 'vip2')) return true;
            if (this.isVip && this.supporterTier >= 3 && !this.isClaimed(lvl, 'vip3')) return true;
            return false;
        },
        firstClaimableLvl() {
            const max = Math.min(this.totalLevels, this.playerLevel);
            for (let i = 1; i <= max; i++) {
                if (this.hasUnclaimedOnLevel(i)) return i;
            }
            return null;
        },
        goToLevel(lvl) {
            this.activeLevel = Math.max(1, Math.min(this.totalLevels, Number(lvl) || 1));
        },
        prevSlide() {
            this.activeLevel = this.activeLevel > 1 ? this.activeLevel - 1 : this.totalLevels;
        },
        nextSlide() {
            this.activeLevel = this.activeLevel < this.totalLevels ? this.activeLevel + 1 : 1;
        },
        chipMeta(key, rawVal) {
            const map = {
                cash:          { icon: 'fa-sack-dollar',      label: 'Cash',       prefix: '€', cls: 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-500/30' },
                bank:          { icon: 'fa-building-columns', label: 'Bank',       prefix: '€', cls: 'bg-cyan-500/15 text-cyan-700 dark:text-cyan-300 border-cyan-500/30' },
                xp:            { icon: 'fa-bolt-lightning',   label: 'XP',         prefix: '+', cls: 'bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border-indigo-500/35' },
                strength:      { icon: 'fa-dumbbell',         label: 'Kracht',     prefix: '+', cls: 'bg-orange-500/15 text-orange-700 dark:text-orange-300 border-orange-500/35' },
                respect:       { icon: 'fa-handshake-simple', label: 'Respect',    prefix: '+', cls: 'bg-fuchsia-500/15 text-fuchsia-700 dark:text-fuchsia-300 border-fuchsia-500/30' },
                vault_tokens:  { icon: 'fa-key',              label: rawVal === 1 ? 'Code' : 'Codes', prefix: '', cls: 'bg-yellow-500/15 text-yellow-700 dark:text-yellow-300 border-yellow-500/35' },
                stars:         { icon: 'fa-star',             label: rawVal === 1 ? 'Ster' : 'Sterren', prefix: '', cls: 'bg-amber-500/15 text-amber-700 dark:text-amber-300 border-amber-500/35' },
                masks:         { icon: 'fa-mask',             label: rawVal === 1 ? 'Masker' : 'Maskers', prefix: '', cls: 'bg-violet-500/15 text-violet-700 dark:text-violet-300 border-violet-500/30' },
                unopened_bags: { icon: 'fa-briefcase',        label: 'Koffer',     prefix: '',  cls: 'bg-purple-500/15 text-purple-700 dark:text-purple-300 border-purple-500/30' },
                xxl_cases:     { icon: 'fa-box-open',         label: 'XXL Kist',   prefix: '',  cls: 'bg-amber-500/20 text-amber-800 dark:text-amber-200 border-amber-400/50' },
                bullets:       { icon: 'fa-crosshairs',       label: 'Kogels',     prefix: '',  cls: 'bg-rose-500/15 text-rose-700 dark:text-rose-300 border-rose-500/30' },
                vip_points:    { icon: 'fa-crown',            label: 'VIP Pnt',    prefix: '+', cls: 'bg-purple-500/15 text-purple-700 dark:text-purple-300 border-purple-500/30' },
                energy:        { icon: 'fa-bolt',             label: 'Energie',    prefix: '+', cls: 'bg-sky-500/15 text-sky-700 dark:text-sky-300 border-sky-500/30' },
                health:        { icon: 'fa-heart-pulse',      label: 'HP',         prefix: '+', cls: 'bg-red-500/15 text-red-700 dark:text-red-300 border-red-500/30' },
            };
            return map[key] || { icon: 'fa-gift', label: key, prefix: '', cls: 'bg-purple-500/15 text-purple-300 border-purple-500/30' };
        },
        fireConfetti(mode = 'normal') {
            if (typeof window.tbsFireConfetti === 'function') {
                window.tbsFireConfetti(mode);
            }
        },
        async claimReward(lvl, track) {
            if (this.busyKey) return;
            const key = 'lvl_' + lvl + '_' + track;
            this.busyKey = key;
            // Optimistic UI update + instant confetti!
            this.claimedMap[key] = true;
            if (this.claimableCount > 0) this.claimableCount--;
            this.fireConfetti('normal');
            if (window.tbsVibrate) window.tbsVibrate(20);

            try {
                const token = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content');
                const fd = new FormData();
                fd.append('_token', token);
                fd.append('level', lvl);
                fd.append('track', track);

                const res = await fetch(window.__TBS_BP_DATA__.claimUrl, {
                    method: 'POST',
                    body: fd,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-BP-Fast': '1',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                this.applyServerResponse(data);
            } catch (e) {
                delete this.claimedMap[key];
                if (window.tbsToast) window.tbsToast('Netwerkfout bij claimen.', 'error');
            } finally {
                this.busyKey = null;
            }
        },
        async claimAllRewards() {
            if (this.busyKey || this.claimableCount <= 0) return;
            this.busyKey = 'all';
            this.fireConfetti('mega');
            if (window.tbsVibrate) window.tbsVibrate([25, 40, 25]);

            try {
                const token = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content');
                const fd = new FormData();
                fd.append('_token', token);

                const res = await fetch(window.__TBS_BP_DATA__.claimAllUrl, {
                    method: 'POST',
                    body: fd,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-BP-Fast': '1',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                this.applyServerResponse(data);
            } catch (e) {
                if (window.tbsToast) window.tbsToast('Netwerkfout bij alles claimen.', 'error');
            } finally {
                this.busyKey = null;
            }
        },
        applyServerResponse(data) {
            if (!data) return;
            if (Array.isArray(data.claims)) {
                this.claimedMap = Object.fromEntries(data.claims.map(k => [k, true]));
            }
            if (typeof data.claimableCount === 'number') {
                this.claimableCount = data.claimableCount;
            }
            if (data.character) {
                this.playerLevel = data.character.level || this.playerLevel;
                this.playerRespect = Number(data.character.respect || 0).toLocaleString('nl-NL');
                this.playerStrength = Number(data.character.strength || 0).toLocaleString('nl-NL');
                if (window.tbsApplyStats) window.tbsApplyStats(data.character);
            }
            if (data.message && window.tbsToast) {
                window.tbsToast(data.message, data.type || (data.success ? 'success' : 'error'));
            }
        }
     }"
     @keydown.window.arrow-left="prevSlide()"
     @keydown.window.arrow-right="nextSlide()">

    <!-- PAARSE TBS KLINIEK HERO BANNER -->
    <div class="relative overflow-hidden rounded-3xl bg-slate-950 text-white border-2 border-purple-500/40 shadow-xl">
        <div class="absolute inset-0 bg-cover bg-center opacity-35"
             style="background-image: url('{{ asset('assets/illustrations/tbs_purple_hero.jpg') }}');"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-purple-950/90 to-slate-950/85"></div>

        <div class="relative z-10 p-5 sm:p-7 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-3 max-w-2xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 bg-purple-500/25 text-purple-200 text-[11px] font-black uppercase tracking-wider px-3 py-1 rounded-full border border-purple-400/40">
                        <i class="fa-solid fa-trophy text-purple-300"></i>
                        <span>Seizoen 1 • {{ $maxPassLevel }} Niveaus</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 bg-amber-400/20 text-amber-300 text-[11px] font-black uppercase tracking-wider px-3 py-1 rounded-full border border-amber-400/40">
                        <i class="fa-solid fa-crown"></i>
                        <span>FREE • TBS V.I.P. Lid • Gangster Elite • Kliniek Baas</span>
                    </span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white flex items-center gap-3 flex-wrap">
                    <span>TBS Kliniek Battle Pass</span>
                    <span class="text-xs font-extrabold px-2.5 py-1 rounded-xl bg-purple-600/50 border border-purple-400/50 text-purple-100"
                          x-text="Math.min(totalLevels, playerLevel) + ' / ' + totalLevels + ' Levels Ontgrendeld'">
                        {{ $unlockedCount }} / {{ $maxPassLevel }} Levels Ontgrendeld
                    </span>
                </h1>

                <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">
                    Blader razendsnel met de paarse pijlen (of je pijltjestoetsen) door alle <strong class="text-white">{{ $maxPassLevel }} kliniek-niveaus</strong> en claim <strong class="text-purple-200">Geld, XP, Kracht, Respect, Codes, Sterren en schaarse Maskers</strong>! Speel extra rangen vrij met <strong class="text-white">TBS V.I.P. Lid (VIP 1), Gangster Elite V.I.P. (VIP 2) &amp; Kliniek Baas V.I.P. (VIP 3)</strong>.
                </p>

                <!-- Speler Status & Directe Link naar VIP Lidmaatschap Winkel -->
                <div class="pt-1 flex flex-wrap items-center gap-2.5">
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-purple-950/80 border border-purple-500/40 text-xs font-bold text-purple-100">
                        <i class="fa-solid fa-user-shield text-purple-400"></i>
                        <span>Jouw Level: <strong class="text-white" x-text="playerLevel">{{ $character->level }}</strong></span>
                        <span class="text-purple-400">&bull;</span>
                        <span x-text="playerRespect + ' Respect'">{{ number_format($character->respect, 0, ',', '.') }} Respect</span>
                        <span class="text-purple-400">&bull;</span>
                        <span x-text="playerStrength + ' Kracht'">{{ number_format($character->strength, 0, ',', '.') }} Kracht</span>
                    </div>

                    @if($isVip && $character->vip_badge)
                        <span class="px-3 py-1.5 rounded-xl bg-gradient-to-r from-purple-600 to-fuchsia-600 text-white text-xs font-black uppercase tracking-wider border border-purple-300/50 flex items-center gap-1.5 shadow-sm">
                            <i class="fa-solid {{ $character->vip_badge['icon'] }} text-amber-300"></i>
                            <span>{{ $character->vip_badge['label'] }}</span>
                        </span>
                    @else
                        <span class="px-3 py-1.5 rounded-xl bg-slate-900/90 border border-slate-700 text-slate-300 text-xs font-bold flex items-center gap-1.5">
                            <i class="fa-solid fa-unlock text-purple-400"></i>
                            <span>Actieve Pas: FREE Pass</span>
                        </span>
                    @endif

                    @if(!$isVip || $supporterTier < 3)
                        <a href="{{ route('vip.index', ['tab' => 'pakketten']) }}" class="btn-star-gold px-3.5 py-1.5 rounded-xl text-xs uppercase tracking-wider transition flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-crown"></i>
                            <span>Naar VIP Lidmaatschap Winkel</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Rechts: Claim Alles + Confetti Box -->
            <div class="bg-purple-950/85 border-2 border-purple-400/40 rounded-2xl p-4 sm:p-5 shrink-0 flex flex-col justify-between space-y-3 min-w-[265px] shadow-lg">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-purple-300 block">Klaar in je Kluis</span>
                        <div class="text-2xl font-black text-white mt-0.5 flex items-center gap-2">
                            <span x-text="claimableCount + (claimableCount === 1 ? ' Beloning' : ' Beloningen')">{{ $claimableCount }} Beloningen</span>
                            <span x-show="claimableCount > 0" class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                        </div>
                    </div>
                    <button type="button"
                            @click="fireConfetti('normal')"
                            title="Schiet Confetti!"
                            class="w-11 h-11 rounded-2xl bg-purple-600/40 hover:bg-purple-500/60 border border-purple-400/50 flex items-center justify-center text-amber-300 text-lg transition cursor-pointer active:scale-90">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                    </button>
                </div>

                <!-- Battle Pass Progressie Balk (150 Levels) -->
                <div class="space-y-1">
                    <div class="flex justify-between text-[10px] font-bold text-purple-200">
                        <span x-text="'Battle Pass Voortgang (' + Math.min(totalLevels, playerLevel) + '/' + totalLevels + ')'"></span>
                        <span x-text="Math.round((Math.min(totalLevels, playerLevel) / totalLevels) * 100) + '%'"></span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-slate-900 overflow-hidden p-0.5 border border-purple-500/30">
                        <div class="h-full rounded-full bg-gradient-to-r from-purple-500 via-fuchsia-500 to-amber-400 transition-all duration-300"
                             :style="'width: ' + Math.min(100, Math.round((Math.min(totalLevels, playerLevel) / totalLevels) * 100)) + '%'"></div>
                    </div>
                </div>

                <template x-if="claimableCount > 0">
                    <button type="button"
                            @click="claimAllRewards()"
                            :disabled="busyKey !== null"
                            class="btn-star-gold w-full py-2.5 px-4 rounded-xl font-black text-xs uppercase tracking-wider transition flex items-center justify-center gap-2 cursor-pointer active:scale-95">
                        <i class="fa-solid fa-gifts"></i>
                        <span x-text="busyKey === 'all' ? 'Bezig met Claimen...' : ('Claim Alle ' + claimableCount + ' Prijzen!')"></span>
                    </button>
                </template>
                <template x-if="claimableCount === 0">
                    <button type="button"
                            @click="goToLevel(Math.min(totalLevels, playerLevel + 1))"
                            class="w-full py-2.5 px-4 rounded-xl bg-purple-900/50 hover:bg-purple-800/60 border border-purple-500/40 text-purple-200 font-bold text-xs uppercase tracking-wider transition flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-crosshairs text-purple-300"></i>
                        <span x-text="'Bekijk Volgend Doel (Lvl ' + Math.min(totalLevels, playerLevel + 1) + ')'"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>

    <!-- DE 4 BATTLE PASSEN OVERZICHT (FREE • TBS V.I.P. LID • GANGSTER ELITE V.I.P. • KLINIEK BAAS V.I.P.) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        @foreach($tracksMeta as $trackKey => $tMeta)
            <div class="relative overflow-hidden rounded-2xl border {{ $tMeta['border'] }} bg-slate-950 text-white p-3.5 sm:p-4 shadow-md">
                <div class="absolute inset-0 bg-cover bg-center opacity-40"
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
                            <a href="{{ route('vip.index', ['tab' => 'pakketten']) }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-500/20 hover:bg-amber-500/35 border border-amber-400/50 text-amber-200 text-[10px] font-black transition">
                                <i class="fa-solid fa-crown text-[9px] text-amber-400"></i> VIP Lidmaatschap Winkel
                            </a>
                        @endif
                    </div>

                    <div class="flex items-center gap-2.5 pt-1">
                        <div class="w-10 h-10 rounded-xl bg-purple-600/30 border border-purple-400/40 flex items-center justify-center text-purple-200 shrink-0">
                            <i class="fa-solid {{ $tMeta['icon'] }} text-base {{ $trackKey === 'vip3' ? 'text-amber-300' : '' }}"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-black text-sm text-white truncate">{{ $tMeta['rank_title'] }}</h3>
                            <p class="text-[11px] text-purple-200/80 truncate">{{ $tMeta['subtitle'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- RAZENDSNELLE NIVEAU BALK (30 LEVELS PER REEKS) + PIJLEN VOOR & ACHTER -->
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
                        Kies een level-reeks of klik op de grote paarse pijlen voor &amp; achter om direct te wisselen
                    </p>
                </div>
            </div>

            <!-- Snelle Level-Reeks Sprong Knoppen (voor 150 Levels) -->
            <div class="flex flex-wrap items-center gap-1.5">
                <template x-if="firstClaimableLvl() !== null">
                    <button type="button"
                            @click="goToLevel(firstClaimableLvl())"
                            class="px-2.5 py-1 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30 text-[11px] font-black transition cursor-pointer flex items-center gap-1">
                        <i class="fa-solid fa-gift"></i>
                        <span x-text="'Claimbaar (Lvl ' + firstClaimableLvl() + ')'"></span>
                    </button>
                </template>
                <button type="button"
                        @click="goToLevel(Math.min(totalLevels, Math.max(1, playerLevel)))"
                        class="px-2.5 py-1 rounded-xl bg-purple-500/15 hover:bg-purple-500/25 text-purple-700 dark:text-purple-300 border border-purple-500/30 text-[11px] font-bold transition cursor-pointer"
                        x-text="'Huidig (Lvl ' + Math.min(totalLevels, Math.max(1, playerLevel)) + ')'">
                </button>
                @foreach([[1, 30], [31, 60], [61, 90], [91, 120], [121, 150]] as [$startLvl, $endLvl])
                    <button type="button" @click="goToLevel({{ $startLvl }})"
                            :class="activeLevel >= {{ $startLvl }} && activeLevel <= {{ $endLvl }} ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-purple-500/20'"
                            class="px-2.5 py-1 rounded-xl text-[11px] font-extrabold transition cursor-pointer">
                        Lvl {{ $startLvl }}–{{ $endLvl }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- 30 Zichtbare Level Knoppen per Reeks (Geen 150-elementen lag!) -->
        <div class="flex items-center gap-2">
            <button type="button"
                    @click="prevSlide()"
                    class="w-9 h-9 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-black flex items-center justify-center shrink-0 shadow-sm transition cursor-pointer active:scale-90"
                    title="Vorig Niveau">
                <i class="fa-solid fa-chevron-left text-xs"></i>
            </button>

            <div class="grid grid-cols-10 sm:grid-cols-15 gap-1.5 flex-1">
                <template x-for="lvlNum in visiblePills" :key="lvlNum">
                    <button type="button"
                            @click="goToLevel(lvlNum)"
                            :class="activeLevel === lvlNum
                                ? 'bg-gradient-to-br from-purple-600 to-fuchsia-600 text-white border-purple-300 shadow-sm ring-2 ring-purple-400/50'
                                : (playerLevel >= lvlNum
                                    ? 'bg-purple-50 dark:bg-slate-800 text-purple-900 dark:text-purple-200 border-purple-300 dark:border-purple-800/80 hover:bg-purple-100'
                                    : 'bg-slate-100 dark:bg-slate-800/50 text-slate-400 border-slate-200 dark:border-slate-800')"
                            class="relative h-9 rounded-xl border font-black text-xs flex flex-col items-center justify-center transition cursor-pointer">
                        <span x-text="lvlNum"></span>
                        <template x-if="hasUnclaimedOnLevel(lvlNum)">
                            <span class="absolute -top-1 -right-1 w-2.5 h-2.5 rounded-full bg-amber-400 border border-white dark:border-slate-900"></span>
                        </template>
                    </button>
                </template>
            </div>

            <button type="button"
                    @click="nextSlide()"
                    class="w-9 h-9 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-black flex items-center justify-center shrink-0 shadow-sm transition cursor-pointer active:scale-90"
                    title="Volgend Niveau">
                <i class="fa-solid fa-chevron-right text-xs"></i>
            </button>
        </div>
    </div>

    <!-- ULTRA-SNELLE INTERACTIEVE SLIDER KAART MET 2 GROTE PAARSE PIJLEN (VOOR & ACHTERKANT) -->
    <div class="relative flex items-center justify-center sm:px-14"
         @touchstart.passive="touchStartX = $event.changedTouches[0].screenX"
         @touchend.passive="const diff = $event.changedTouches[0].screenX - touchStartX; if (Math.abs(diff) > 45) { diff < 0 ? nextSlide() : prevSlide(); }">

        <!-- Grote Pijl VOORKANT (Links) -->
        <button type="button"
                @click="prevSlide()"
                aria-label="Vorig Battle Pass Niveau"
                class="hidden sm:flex items-center justify-center w-12 h-28 rounded-2xl bg-gradient-to-b from-purple-600 via-purple-700 to-indigo-900 hover:from-purple-500 hover:to-indigo-700 text-white border-2 border-purple-300/60 shadow-xl absolute left-0 top-1/2 -translate-y-1/2 z-20 transition cursor-pointer active:scale-90">
            <i class="fa-solid fa-chevron-left text-xl"></i>
        </button>

        <!-- Grote Pijl ACHTERKANT (Rechts) -->
        <button type="button"
                @click="nextSlide()"
                aria-label="Volgend Battle Pass Niveau"
                class="hidden sm:flex items-center justify-center w-12 h-28 rounded-2xl bg-gradient-to-b from-purple-600 via-purple-700 to-indigo-900 hover:from-purple-500 hover:to-indigo-700 text-white border-2 border-purple-300/60 shadow-xl absolute right-0 top-1/2 -translate-y-1/2 z-20 transition cursor-pointer active:scale-90">
            <i class="fa-solid fa-chevron-right text-xl"></i>
        </button>

        <!-- Enkele Actieve Niveau Kaart (Directe 0ms Weergave) -->
        <div class="w-full rounded-3xl border-2 overflow-hidden transition-colors duration-150 bg-white dark:bg-slate-900 shadow-lg"
             :class="isCurrentUnlocked ? 'border-purple-400/80 dark:border-purple-600/80' : 'border-slate-200 dark:border-slate-800'">

            <!-- Niveau Slide Top Banner (Paarse TBS Stijl) -->
            <div class="relative overflow-hidden bg-gradient-to-r from-purple-950 via-indigo-950 to-slate-950 text-white px-4 sm:px-6 py-4 border-b border-purple-500/30">
                <div class="absolute inset-0 bg-cover bg-center opacity-20"
                     style="background-image: url('{{ asset('assets/illustrations/tbs_purple_hero_boss.jpg') }}');"></div>

                <div class="relative z-10 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-2xl flex flex-col items-center justify-center font-black shrink-0"
                             :class="isCurrentUnlocked ? 'bg-gradient-to-br from-purple-500 to-fuchsia-600 text-white border-2 border-purple-200/60 shadow-md' : 'bg-slate-800 text-slate-400 border border-slate-700'">
                            <span class="text-[9px] uppercase tracking-widest opacity-80 leading-none">LVL</span>
                            <span class="text-lg leading-tight" x-text="activeLevel"></span>
                        </div>

                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-base sm:text-lg font-black text-white tracking-tight"
                                    x-text="'TBS Dossier Niveau ' + activeLevel"></h3>

                                <template x-if="activeLevel === totalLevels">
                                    <span class="px-2.5 py-0.5 rounded-full bg-amber-400 text-slate-950 text-[10px] font-black uppercase tracking-wider">
                                        👑 Level 150 Ultieme Finale
                                    </span>
                                </template>
                                <template x-if="activeLevel !== totalLevels && activeLevel % 25 === 0">
                                    <span class="px-2.5 py-0.5 rounded-full bg-amber-400 text-slate-950 text-[10px] font-black uppercase">
                                        💎 Mega Mijlpaal
                                    </span>
                                </template>
                                <template x-if="activeLevel !== totalLevels && activeLevel % 25 !== 0 && activeLevel % 10 === 0">
                                    <span class="px-2.5 py-0.5 rounded-full bg-amber-400/25 border border-amber-400/60 text-amber-200 text-[10px] font-black uppercase">
                                        🏆 Hoofdmijlpaal
                                    </span>
                                </template>
                                <template x-if="activeLevel % 10 !== 0 && activeLevel % 5 === 0">
                                    <span class="px-2.5 py-0.5 rounded-full bg-purple-500/30 border border-purple-300/50 text-purple-200 text-[10px] font-black uppercase">
                                        ⭐ Kluis Mijlpaal
                                    </span>
                                </template>
                            </div>
                            <p class="text-xs text-purple-200/80 font-medium mt-0.5">
                                <span x-text="'Vereist Speler Level ' + currentItem.req_level"></span>
                                <template x-if="currentItem.has_respect">
                                    <span x-text="' • Aanbevolen: ' + currentItem.req_respect + ' Respect'"></span>
                                </template>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <template x-if="isCurrentUnlocked">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-500/20 border border-emerald-400/50 text-emerald-300 text-xs font-black uppercase tracking-wider">
                                <i class="fa-solid fa-lock-open"></i> Ontgrendeld
                            </span>
                        </template>
                        <template x-if="!isCurrentUnlocked">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-800/90 border border-slate-700 text-slate-300 text-xs font-bold">
                                <i class="fa-solid fa-lock text-amber-400"></i>
                                <span x-text="'Nog ' + Math.max(1, currentItem.req_level - playerLevel) + ((currentItem.req_level - playerLevel) === 1 ? ' level nodig' : ' levels nodig')"></span>
                            </span>
                        </template>
                    </div>
                </div>
            </div>

            <!-- De 4 Passen Kolommen (FREE, TBS V.I.P. Lid, Gangster Elite V.I.P., Kliniek Baas V.I.P.) -->
            <div class="p-4 sm:p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                @foreach(['free', 'vip1', 'vip2', 'vip3'] as $tKey)
                    @php $tInfo = $tracksMeta[$tKey]; @endphp

                    <div class="rounded-2xl border-2 flex flex-col justify-between overflow-hidden"
                         :class="isClaimed(activeLevel, '{{ $tKey }}')
                            ? 'bg-slate-50/70 dark:bg-slate-800/40 border-slate-200 dark:border-slate-800 opacity-75'
                            : (canClaim(activeLevel, '{{ $tKey }}')
                                ? 'bg-purple-50/60 dark:bg-purple-950/35 border-purple-400 dark:border-purple-500 shadow-md ring-1 ring-purple-400/40'
                                : 'bg-slate-50/50 dark:bg-slate-900/60 border-slate-200/80 dark:border-slate-800')">

                        <!-- Pas Mini Artwork Header -->
                        <div class="relative h-20 overflow-hidden bg-slate-950 p-3 flex flex-col justify-between">
                            <div class="absolute inset-0 bg-cover bg-center opacity-50"
                                 style="background-image: url('{{ $tInfo['image'] }}'), url('{{ $tInfo['fallback_bg'] }}');"></div>
                            <div class="absolute inset-0 bg-gradient-to-r {{ $tInfo['header_grad'] }} opacity-80"></div>

                            <div class="relative z-10 flex items-center justify-between gap-1">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider border {{ $tInfo['badge_cls'] }} truncate">
                                    {{ $tInfo['rank_title'] }}
                                </span>

                                <template x-if="isClaimed(activeLevel, '{{ $tKey }}')">
                                    <span class="w-6 h-6 rounded-full bg-emerald-500 text-slate-950 flex items-center justify-center text-xs font-black shadow shrink-0">
                                        <i class="fa-solid fa-check"></i>
                                    </span>
                                </template>
                                <template x-if="!isClaimed(activeLevel, '{{ $tKey }}') && canClaim(activeLevel, '{{ $tKey }}')">
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-400 text-slate-950 text-[9px] font-black uppercase shrink-0">
                                        Klaar!
                                    </span>
                                </template>
                                <template x-if="!isClaimed(activeLevel, '{{ $tKey }}') && !canClaim(activeLevel, '{{ $tKey }}')">
                                    <span class="w-6 h-6 rounded-full bg-slate-900/80 border border-white/15 text-slate-300 flex items-center justify-center text-[10px] shrink-0">
                                        <i class="fa-solid fa-lock"></i>
                                    </span>
                                </template>
                            </div>

                            <div class="relative z-10 flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-white/15 border border-white/20 flex items-center justify-center text-white text-xs shrink-0">
                                    <i class="fa-solid" :class="currentItem.tracks['{{ $tKey }}'].icon"></i>
                                </div>
                                <h4 class="font-black text-xs text-white truncate drop-shadow"
                                    x-text="currentItem.tracks['{{ $tKey }}'].title"></h4>
                            </div>
                        </div>

                        <!-- Visuele Beloning Chips -->
                        <div class="p-3.5 space-y-3 flex-1 flex flex-col justify-between">
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="chip in currentItem.tracks['{{ $tKey }}'].chips" :key="chip.key">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg border text-[11px] font-extrabold"
                                          :class="chipMeta(chip.key, chip.raw).cls">
                                        <i class="fa-solid text-[10px]" :class="chipMeta(chip.key, chip.raw).icon"></i>
                                        <span x-text="chipMeta(chip.key, chip.raw).prefix + chip.val + ' ' + chipMeta(chip.key, chip.raw).label"></span>
                                    </span>
                                </template>
                            </div>

                            <!-- Actieknop onderaan elk pas-vak -->
                            <div class="pt-1">
                                <template x-if="isClaimed(activeLevel, '{{ $tKey }}')">
                                    <div class="w-full py-2 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-[11px] font-black uppercase tracking-wider flex items-center justify-center gap-1.5">
                                        <i class="fa-solid fa-circle-check"></i>
                                        <span>Geclaimd</span>
                                    </div>
                                </template>

                                <template x-if="!isClaimed(activeLevel, '{{ $tKey }}') && canClaim(activeLevel, '{{ $tKey }}')">
                                    <button type="button"
                                            @click="claimReward(activeLevel, '{{ $tKey }}')"
                                            :disabled="busyKey !== null"
                                            class="w-full py-2 rounded-xl {{ $tInfo['btn_cls'] }} text-[11px] font-black uppercase tracking-wider transition shadow-md cursor-pointer active:scale-95 flex items-center justify-center gap-1.5">
                                        <i class="fa-solid fa-gift"></i>
                                        <span>Claim Beloning</span>
                                    </button>
                                </template>

                                <template x-if="!isClaimed(activeLevel, '{{ $tKey }}') && !isTrackUnlocked('{{ $tKey }}')">
                                    <a href="{{ route('vip.index', ['tab' => 'pakketten']) }}" class="btn-star-gold w-full py-2 px-2.5 rounded-xl text-[10px] font-black uppercase tracking-wider flex items-center justify-center gap-1.5 transition cursor-pointer text-center">
                                        <i class="fa-solid fa-crown text-[10px]"></i>
                                        <span>VIP Lidmaatschap Winkel</span>
                                    </a>
                                </template>

                                <template x-if="!isClaimed(activeLevel, '{{ $tKey }}') && isTrackUnlocked('{{ $tKey }}') && !isCurrentUnlocked">
                                    <div class="w-full py-2 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 border border-slate-200 dark:border-slate-700 text-[10px] font-bold flex items-center justify-center gap-1.5">
                                        <i class="fa-solid fa-lock text-[9px]"></i>
                                        <span x-text="'Vereist Level ' + currentItem.req_level"></span>
                                    </div>
                                </template>
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

                <span class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400"
                      x-text="'Niveau ' + activeLevel + ' / ' + totalLevels">
                </span>

                <button type="button"
                        @click="nextSlide()"
                        class="px-3.5 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-black flex items-center gap-1.5 shadow-xs transition cursor-pointer active:scale-95">
                    <span>Volgend Niveau</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
