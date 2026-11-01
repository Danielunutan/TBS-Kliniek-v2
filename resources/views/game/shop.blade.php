@extends('layouts.app')

@section('content')
@php
    $getItemData = function($item) {
        $slug = $item->slug ?? '';
        $nameLower = strtolower($item->name ?? '');

        if (in_array($slug, ['frikandelbroodje', 'saucijzenbroodje', 'bami-schijf-speciaal', 'broodje-bal-mayo', 'portie-bitterballen', 'kapsalon-paviljoen-7', 'gouden-frikandel'])
            || str_contains($nameLower, 'frikandel') || str_contains($nameLower, 'broodje') || str_contains($nameLower, 'bami') || str_contains($nameLower, 'bitterbal') || str_contains($nameLower, 'kapsalon')) {
            $cat = 'eten';
        } elseif (in_array($slug, ['sterke-kliniek-koffie', 'energy-drink-xxl'])
            || str_contains($nameLower, 'koffie') || str_contains($nameLower, 'energy') || str_contains($nameLower, 'drank')) {
            $cat = 'drank';
        } elseif (in_array($slug, ['strip-oxazepam', 'ehbo-noodverbanddoos', 'adrenaline-injectie', 'strip-ritalin', 'haloperidol-forte'])
            || str_contains($nameLower, 'oxazepam') || str_contains($nameLower, 'ehbo') || str_contains($nameLower, 'adrenaline') || str_contains($nameLower, 'medicijn') || str_contains($nameLower, 'ritalin') || str_contains($nameLower, 'haloperidol')) {
            $cat = 'medisch';
        } elseif (in_array($slug, ['trainingshandschoenen', 'paarse-trainingsjas', 'sporttas', 'legendarische-halter', 'eiwitshake-bodybuilder', 'multivitaminen-focus', 'boksbeugel', 'stalen-neus-kisten'])
            || str_contains($nameLower, 'training') || str_contains($nameLower, 'halter') || str_contains($nameLower, 'boksbeugel') || str_contains($nameLower, 'kisten') || str_contains($nameLower, 'eiwitshake') || str_contains($nameLower, 'vitamin') || str_contains($nameLower, 'sporttas')) {
            $cat = 'kracht';
        } else {
            $cat = 'smokkel';
        }

        $icon = match($slug) {
            'frikandelbroodje' => 'fa-solid fa-hotdog',
            'saucijzenbroodje' => 'fa-solid fa-bread-slice',
            'bami-schijf-speciaal' => 'fa-solid fa-burger',
            'broodje-bal-mayo' => 'fa-solid fa-drumstick-bite',
            'portie-bitterballen' => 'fa-solid fa-cookie-bite',
            'kapsalon-paviljoen-7' => 'fa-solid fa-bowl-food',
            'gouden-frikandel' => 'fa-solid fa-crown',
            'sterke-kliniek-koffie' => 'fa-solid fa-mug-hot',
            'energy-drink-xxl' => 'fa-solid fa-bolt',
            'strip-oxazepam' => 'fa-solid fa-pills',
            'ehbo-noodverbanddoos' => 'fa-solid fa-kit-medical',
            'adrenaline-injectie' => 'fa-solid fa-syringe',
            'trainingshandschoenen' => 'fa-solid fa-hand-fist',
            'paarse-trainingsjas' => 'fa-solid fa-shirt',
            'sporttas' => 'fa-solid fa-bag-shopping',
            'legendarische-halter' => 'fa-solid fa-dumbbell',
            'eiwitshake-bodybuilder' => 'fa-solid fa-bottle-water',
            'multivitaminen-focus' => 'fa-solid fa-capsules',
            'boksbeugel' => 'fa-solid fa-hand-back-fist',
            'stalen-neus-kisten' => 'fa-solid fa-shoe-prints',
            'bivakmuts-masker' => 'fa-solid fa-mask',
            'smokkelpakketje' => 'fa-solid fa-box',
            'ijzerzaagje' => 'fa-solid fa-screwdriver-wrench',
            'sleutel-paviljoen-13', 'oude-sleutel' => 'fa-solid fa-key',
            'sigaretten' => 'fa-solid fa-smoking',
            'ritalin', 'strip-ritalin' => 'fa-solid fa-pills',
            'haloperidol-forte' => 'fa-solid fa-syringe',
            'telefoon' => 'fa-solid fa-mobile-screen',
            default => (!empty($item->icon) && !in_array($item->icon, ['fa-vest', 'fa-mitten', 'fa-key-skeleton', 'fa-utensils'])
                ? (str_starts_with($item->icon, 'fa-') ? 'fa-solid ' . $item->icon : $item->icon)
                : 'fa-solid fa-box')
        };

        $badgeClass = match($cat) {
            'eten' => ($slug === 'gouden-frikandel' ? 'bg-gradient-to-br from-amber-300 to-amber-500 text-amber-950 shadow-xs ring-1 ring-amber-400' : 'bg-amber-100 dark:bg-amber-950/70 text-amber-700 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/60'),
            'drank' => 'bg-cyan-100 dark:bg-cyan-950/70 text-cyan-700 dark:text-cyan-300 border border-cyan-200/60 dark:border-cyan-800/60',
            'medisch' => ($slug === 'adrenaline-injectie' ? 'bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/60' : ($slug === 'ehbo-noodverbanddoos' ? 'bg-rose-100 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300 border border-rose-200/60 dark:border-rose-800/60' : 'bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 border border-purple-200/60 dark:border-purple-800/60')),
            'kracht' => ($slug === 'legendarische-halter' ? 'bg-fuchsia-100 dark:bg-fuchsia-950/70 text-fuchsia-700 dark:text-fuchsia-300 border border-fuchsia-200/60 dark:border-fuchsia-800/60' : ($slug === 'paarse-trainingsjas' ? 'bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 border border-purple-200/60 dark:border-purple-800/60' : ($slug === 'boksbeugel' ? 'bg-zinc-800 dark:bg-zinc-900 text-amber-400 border border-zinc-700 dark:border-zinc-700' : 'bg-slate-200 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-300 dark:border-slate-700'))),
            'smokkel' => ($slug === 'bivakmuts-masker' ? 'bg-slate-900 dark:bg-slate-950 text-white border border-slate-800' : ($slug === 'sleutel-paviljoen-13' || $slug === 'oude-sleutel' ? 'bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/60' : 'bg-indigo-100 dark:bg-indigo-950/70 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/60')),
            default => 'bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 border border-purple-200/60 dark:border-purple-800/60'
        };

        return ['cat' => $cat, 'icon' => $icon, 'badgeClass' => $badgeClass];
    };

    $allowedShopTabs = ['pakketten', 'items', 'sterren', 'inventaris', 'donaties'];
    $rawShopTab = (string) session('shop_tab', request('tab', 'pakketten'));
    $safeShopTab = in_array($rawShopTab, $allowedShopTabs, true) ? $rawShopTab : 'pakketten';
    $hasExplicitShopTab = session()->has('shop_tab') || in_array((string) request('tab'), $allowedShopTabs, true);

    $dailyDealItem = $dailyDealItem ?? null;
    $dailyDealDiscountPct = (int) ($dailyDealDiscountPct ?? 15);
    $charismaDiscountPct = min(15, (int) floor($character->getEffectiveCharisma() / 10));

    $playerStats = $playerStatsComparison ?? [
        'energy' => (int) $character->energy,
        'max_energy' => (int) max(1, $character->max_energy),
        'health' => (int) $character->health,
        'max_health' => (int) max(1, $character->max_health),
        'strength' => (int) $character->getEffectiveStrength(),
        'base_strength' => (int) ($character->strength ?? 10),
        'defense' => (int) $character->getEffectiveDefense(),
        'stamina' => (int) $character->getEffectiveStamina(),
        'base_stamina' => (int) ($character->stamina ?? 10),
        'intelligence' => (int) $character->getEffectiveIntelligence(),
        'charisma' => (int) $character->getEffectiveCharisma(),
        'discipline' => (int) $character->getEffectiveDiscipline(),
        'stat_cap' => (int) $character->getMaxStatForLevel(),
    ];
@endphp

<div class="space-y-6" x-data="{ 
    shopTab: '{{ $safeShopTab }}',
    bundleFilter: 'all',
    itemFilter: 'all',
    selectedBundleId: 'afdelingsbaas_deal',
    bundleQty: 1,
    recipientId: '',
    bundles: @js($bundles),
    @php
        $ownedCountsMap = $inventory->pluck('quantity', 'item_id')->toArray();
        $bivakItem = $items->firstWhere('slug', 'bivakmuts-masker');
        if ($bivakItem) {
            $ownedCountsMap[$bivakItem->id] = (int) ($character->masks ?? 0);
        }
    @endphp
    ownedCounts: @js($ownedCountsMap),
    totalInvCount: {{ (int) $inventory->sum('quantity') }},
    storageUsed: {{ (int) $character->getStorageUsed() }},
    storageCapacity: {{ (int) $character->getStorageCapacity() }},
    get storageRemaining() {
        return Math.max(0, this.storageCapacity - this.storageUsed);
    },
    itemBuying: {},
    itemToast: {},
    init() {
        if ({{ $hasExplicitShopTab ? 'false' : 'true' }} && sessionStorage.getItem('tbs_shop_tab')) {
            this.shopTab = sessionStorage.getItem('tbs_shop_tab');
        }
    },
    setShopTab(tab) {
        this.shopTab = tab;
        sessionStorage.setItem('tbs_shop_tab', tab);
        try {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url.toString());
        } catch (e) {}
    },
    async buyItemViaAjax(event, itemId, qty) {
        const form = event.target;
        const url = form.action;
        const token = form.querySelector('input[name=_token]') ? form.querySelector('input[name=_token]').value : '{{ csrf_token() }}';
        
        this.itemBuying = { ...this.itemBuying, [itemId]: true };
        const cleanToast = { ...this.itemToast };
        delete cleanToast[itemId];
        this.itemToast = cleanToast;
        
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ quantity: qty })
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
                this.ownedCounts = { ...this.ownedCounts, [itemId]: data.owned_count };
                this.totalInvCount = data.total_inventory_count;
                if (data.storage_used !== undefined) {
                    this.storageUsed = data.storage_used;
                }
                if (data.storage_capacity !== undefined) {
                    this.storageCapacity = data.storage_capacity;
                }
                
                window.dispatchEvent(new CustomEvent('stats:refresh'));
                
                this.itemToast = {
                    ...this.itemToast,
                    [itemId]: {
                        type: 'success',
                        text: qty + 'x Aangeschaft! (In bezit: ' + data.owned_count + 'x)'
                    }
                };
                setTimeout(() => {
                    if (this.itemToast[itemId] && this.itemToast[itemId].type === 'success') {
                        const next = { ...this.itemToast };
                        delete next[itemId];
                        this.itemToast = next;
                    }
                }, 2500);
            } else {
                if (data.storage_used !== undefined) {
                    this.storageUsed = data.storage_used;
                }
                if (data.storage_capacity !== undefined) {
                    this.storageCapacity = data.storage_capacity;
                }
                this.itemToast = {
                    ...this.itemToast,
                    [itemId]: {
                        type: 'error',
                        text: data.message || 'Transactie geweigerd.'
                    }
                };
                setTimeout(() => {
                    if (this.itemToast[itemId] && this.itemToast[itemId].type === 'error') {
                        const next = { ...this.itemToast };
                        delete next[itemId];
                        this.itemToast = next;
                    }
                }, 4000);
            }
        } catch (err) {
            form.submit();
        } finally {
            const nextBuying = { ...this.itemBuying };
            delete nextBuying[itemId];
            this.itemBuying = nextBuying;
        }
    }
}">

    @include('game.control_points.banner', ['slug' => 'kantine'])

    <!-- Top Command Banner -->
    <div class="rounded-2xl shadow-xs p-6 sm:p-7 text-slate-800 dark:text-slate-100 relative overflow-hidden bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800">
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <div class="space-y-1.5">
                <div class="inline-flex items-center gap-2 bg-purple-50 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 text-xs font-black px-3 py-1 rounded-full border border-purple-200/80 dark:border-purple-800/70">
                    <i class="fa-solid fa-cart-flatbed-suitcase"></i> Paviljoen Bevoorrading &amp; Zwarte Toonbank
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Kliniek Handelsplein &amp; Uitrustingsgrid</h1>
                <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm max-w-2xl leading-relaxed">
                    Vergelijk elk artikel rechtstreeks met je huidige eigenschappen, profiteer van de dagelijkse <strong>-{{ $dailyDealDiscountPct }}% Dagdeal</strong> en stel voorraadpakketten of sterrenprivileges veilig.
                </p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 shrink-0">
                <div class="px-3.5 py-2.5 rounded-xl bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/60">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 block">Sterren</span>
                    <span class="text-sm font-black text-amber-600 dark:text-amber-300 flex items-center gap-1 mt-0.5">
                        <i class="fa-solid fa-star text-amber-500 text-xs"></i> {{ $character->stars ?? 0 }}
                    </span>
                </div>
                <div class="px-3.5 py-2.5 rounded-xl bg-purple-50/80 dark:bg-purple-950/40 border border-purple-200/80 dark:border-purple-800/60">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-purple-700 dark:text-purple-400 block">Maskers</span>
                    <span class="text-sm font-black text-purple-700 dark:text-purple-300 flex items-center gap-1 mt-0.5">
                        <i class="fa-solid fa-mask text-purple-500 text-xs"></i> {{ $character->masks ?? 0 }}
                    </span>
                </div>
                <div class="px-3.5 py-2.5 rounded-xl bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/60">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 block">Charisma-Korting</span>
                    <span class="text-sm font-black text-emerald-700 dark:text-emerald-300 font-mono mt-0.5 block">
                        -{{ $charismaDiscountPct }}%
                    </span>
                </div>
                <div class="px-3.5 py-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Depot Vakken</span>
                    <span class="text-sm font-black text-slate-800 dark:text-white font-mono mt-0.5 block" x-text="storageUsed + '/' + storageCapacity">
                        {{ $character->getStorageUsed() }}/{{ $character->getStorageCapacity() }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- MOBILE STICKY SEGMENTED BAR -->
    <div class="lg:hidden sticky top-2 z-30 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md p-2 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-md space-y-2">
        <div class="flex items-center gap-1.5 overflow-x-auto scrollbar-none py-0.5 px-0.5">
            <button type="button" @click="setShopTab('items')"
                    :class="shopTab === 'items' ? 'bg-purple-600 text-white shadow-xs font-black' : 'text-slate-600 dark:text-slate-400 font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700'"
                    class="px-3 py-2 rounded-xl text-xs transition-all duration-150 active:scale-95 cursor-pointer flex items-center gap-1.5 shrink-0 whitespace-nowrap">
                <i class="fa-solid fa-table-cells-large text-xs"></i>
                <span>Grid-Winkel</span>
            </button>
            <button type="button" @click="setShopTab('pakketten')"
                    :class="shopTab === 'pakketten' ? 'bg-purple-600 text-white shadow-xs font-black' : 'text-slate-600 dark:text-slate-400 font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700'"
                    class="px-3 py-2 rounded-xl text-xs transition-all duration-150 active:scale-95 cursor-pointer flex items-center gap-1.5 shrink-0 whitespace-nowrap">
                <i class="fa-solid fa-boxes-packing text-xs"></i>
                <span>Bundels</span>
            </button>
            <button type="button" @click="setShopTab('sterren')"
                    :class="shopTab === 'sterren' ? 'bg-purple-600 text-white shadow-xs font-black' : 'text-slate-600 dark:text-slate-400 font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700'"
                    class="px-3 py-2 rounded-xl text-xs transition-all duration-150 active:scale-95 cursor-pointer flex items-center gap-1.5 shrink-0 whitespace-nowrap">
                <i class="fa-solid fa-gem text-amber-500 text-xs"></i>
                <span>Sterrenkiosk</span>
            </button>
            <button type="button" @click="setShopTab('inventaris')"
                    :class="shopTab === 'inventaris' ? 'bg-purple-600 text-white shadow-xs font-black' : 'text-slate-600 dark:text-slate-400 font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700'"
                    class="px-3 py-2 rounded-xl text-xs transition-all duration-150 active:scale-95 cursor-pointer flex items-center gap-1.5 shrink-0 whitespace-nowrap">
                <i class="fa-solid fa-box-archive text-xs"></i>
                <span>Plunjezak</span>
                <span class="px-1.5 py-0.5 rounded-full bg-purple-200 dark:bg-purple-950 text-purple-800 dark:text-purple-300 font-black text-[10px]" x-text="totalInvCount"></span>
            </button>
            <button type="button" @click="setShopTab('donaties')"
                    :class="shopTab === 'donaties' ? 'bg-gradient-to-r from-amber-500 to-amber-600 text-purple-950 shadow-xs font-black' : 'text-amber-700 dark:text-amber-400 font-bold bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/60'"
                    class="px-3 py-2 rounded-xl text-xs transition-all duration-150 active:scale-95 cursor-pointer flex items-center gap-1.5 shrink-0 whitespace-nowrap">
                <i class="fa-solid fa-mug-hot text-xs text-amber-500"></i>
                <span>Supporter</span>
            </button>
        </div>
    </div>

    <!-- FLIPPED MAIN GRID: LEFT = 9-COL INTERACTIVE GRID-SHOP, RIGHT = 3-COL TELEMETRY & DOCK -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left Column (lg:col-span-9): Main Interactive Shop Views -->
        <div class="lg:col-span-9 space-y-6 order-2 lg:order-1">

            <!-- TAB: MODERNE GRID-SHOP MET AANBIEDING VAN DE DAG & STAT-VERGELIJKING -->
            <div x-show="shopTab === 'items'" class="space-y-6">
                @include('partials.bmac_banner')

                <!-- AANBIEDING VAN DE DAG (DETERMINISTIC DAILY DEAL SPOTLIGHT) -->
                @if($dailyDealItem)
                @php
                    $ddMeta = $getItemData($dailyDealItem);
                    $ddCombinedPct = min(25, $charismaDiscountPct + $dailyDealDiscountPct);
                    $ddDiscountedPrice = (int) round($dailyDealItem->price_kc * (1 - ($ddCombinedPct / 100)));
                    $ddOwned = $dailyDealItem->slug === 'bivakmuts-masker'
                        ? (int) ($character->masks ?? 0)
                        : ($inventory->firstWhere('item_id', $dailyDealItem->id)?->quantity ?? 0);
                @endphp
                <div class="rounded-2xl p-5 sm:p-6 bg-gradient-to-r from-amber-500/15 via-purple-600/10 to-emerald-500/15 border-2 border-amber-400/80 dark:border-amber-500/60 shadow-sm relative overflow-hidden"
                     data-testid="daily-deal-spotlight">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 relative z-10">
                        <div class="flex items-start sm:items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl {{ $ddMeta['badgeClass'] }} flex items-center justify-center text-2xl shrink-0 shadow-md ring-2 ring-amber-400/60">
                                <i class="{{ $ddMeta['icon'] }}"></i>
                            </div>
                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-full bg-amber-400 text-slate-950 text-[10px] font-black uppercase tracking-wider inline-flex items-center gap-1">
                                        <i class="fa-solid fa-fire"></i> Aanbieding van de Dag (-{{ $dailyDealDiscountPct }}%)
                                    </span>
                                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 font-mono">
                                        Datumselectie: {{ now()->format('d-m-Y') }}
                                    </span>
                                </div>
                                <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white">
                                    {{ $dailyDealItem->name }}
                                </h3>
                                <p class="text-xs text-slate-600 dark:text-slate-300 max-w-xl">
                                    Vandaag geselecteerd door de kantinebeheerder met <strong>{{ $dailyDealDiscountPct }}% dagkorting</strong> bovenop je Charisma-voordeel (totale korting: <strong>{{ $ddCombinedPct }}%</strong>).
                                </p>
                                <div class="flex flex-wrap items-center gap-2 pt-1 text-[11px] font-bold">
                                    @if($dailyDealItem->energy_restore > 0)
                                        <span class="px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300">
                                            Energie: {{ $playerStats['energy'] }} &rarr; {{ min($playerStats['max_energy'], $playerStats['energy'] + $dailyDealItem->energy_restore) }} (+{{ $dailyDealItem->energy_restore }})
                                        </span>
                                    @endif
                                    @if($dailyDealItem->health_restore > 0)
                                        <span class="px-2 py-0.5 rounded-md bg-rose-100 dark:bg-rose-950/70 text-rose-800 dark:text-rose-300">
                                            HP: {{ $playerStats['health'] }} &rarr; {{ min($playerStats['max_health'], $playerStats['health'] + $dailyDealItem->health_restore) }} (+{{ $dailyDealItem->health_restore }})
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row lg:flex-col items-stretch sm:items-center lg:items-end justify-between gap-3 shrink-0 border-t lg:border-t-0 pt-3 lg:pt-0 border-amber-300/50 dark:border-amber-800/50">
                            <div class="text-left lg:text-right">
                                <span class="text-xs text-slate-400 line-through font-mono mr-1.5">€{{ number_format($dailyDealItem->price_kc, 0, ',', '.') }}</span>
                                <span class="text-lg font-black text-emerald-700 dark:text-emerald-400 font-mono">€{{ number_format($ddDiscountedPrice, 0, ',', '.') }}</span>
                                <span class="block text-[10px] font-bold text-amber-700 dark:text-amber-300">Directe besparing per stuk</span>
                            </div>

                            <form action="{{ route('shop.buy', $dailyDealItem->id) }}" method="POST" data-turbo="false" @submit.prevent="buyItemViaAjax($event, {{ $dailyDealItem->id }}, 1)">
                                @csrf
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit"
                                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs uppercase tracking-wider transition active:scale-95 cursor-pointer flex items-center justify-center gap-2 shadow-sm">
                                    <i class="fa-solid fa-bolt"></i>
                                    <span>Pak Dagdeal (€{{ number_format($ddDiscountedPrice, 0, ',', '.') }})</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endif

                <!-- LIVE SPELER STAT-VERGELIJKINGSBALK -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div>
                            <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                                <i class="fa-solid fa-chart-simple text-purple-600 dark:text-purple-400"></i>
                                <span>Huidig Spelersprofiel &amp; Stat-Referentie</span>
                            </h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                Elk winkelartikel hieronder toont direct hoe het zich verhoudt tot jouw actuele waarden en je levelplafond ({{ $playerStats['stat_cap'] }}).
                            </p>
                        </div>
                        <span class="text-[11px] font-mono font-bold px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                            Level {{ $character->level }} Plafond: {{ $playerStats['stat_cap'] }} pt
                        </span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2.5 text-xs">
                        <div class="p-2.5 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200/70 dark:border-emerald-800/60">
                            <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 block">Energie</span>
                            <span class="font-black text-slate-900 dark:text-white font-mono">{{ $playerStats['energy'] }}/{{ $playerStats['max_energy'] }}</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-rose-50/70 dark:bg-rose-950/30 border border-rose-200/70 dark:border-rose-800/60">
                            <span class="text-[10px] font-bold text-rose-700 dark:text-rose-400 block">Gezondheid</span>
                            <span class="font-black text-slate-900 dark:text-white font-mono">{{ $playerStats['health'] }}/{{ $playerStats['max_health'] }}</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-purple-50/70 dark:bg-purple-950/30 border border-purple-200/70 dark:border-purple-800/60">
                            <span class="text-[10px] font-bold text-purple-700 dark:text-purple-400 block">Kracht</span>
                            <span class="font-black text-slate-900 dark:text-white font-mono">{{ $playerStats['strength'] }}</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-blue-50/70 dark:bg-blue-950/30 border border-blue-200/70 dark:border-blue-800/60">
                            <span class="text-[10px] font-bold text-blue-700 dark:text-blue-400 block">Verdediging</span>
                            <span class="font-black text-slate-900 dark:text-white font-mono">{{ $playerStats['defense'] }}</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/70 dark:border-amber-800/60">
                            <span class="text-[10px] font-bold text-amber-700 dark:text-amber-400 block">Conditie</span>
                            <span class="font-black text-slate-900 dark:text-white font-mono">{{ $playerStats['stamina'] }}</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-200/70 dark:border-indigo-800/60">
                            <span class="text-[10px] font-bold text-indigo-700 dark:text-indigo-400 block">Intelligentie</span>
                            <span class="font-black text-slate-900 dark:text-white font-mono">{{ $playerStats['intelligence'] }}</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-pink-50/70 dark:bg-pink-950/30 border border-pink-200/70 dark:border-pink-800/60">
                            <span class="text-[10px] font-bold text-pink-700 dark:text-pink-400 block">Charisma</span>
                            <span class="font-black text-slate-900 dark:text-white font-mono">{{ $playerStats['charisma'] }}</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-cyan-50/70 dark:bg-cyan-950/30 border border-cyan-200/70 dark:border-cyan-800/60">
                            <span class="text-[10px] font-bold text-cyan-700 dark:text-cyan-400 block">Discipline</span>
                            <span class="font-black text-slate-900 dark:text-white font-mono">{{ $playerStats['discipline'] }}</span>
                        </div>
                    </div>
                </div>

                <!-- GRID-SHOP CONTAINER MET CATEGORIE-FILTERS -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 flex items-center justify-center text-sm shadow-2xs">
                                <i class="fa-solid fa-table-cells-large"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">Assortiments-Grid &amp; Stat-Vergelijker</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Filter op afdeling en bekijk per kaart de exacte vooruitgang op jouw dossier.</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <div class="text-xs font-bold px-3 py-1.5 rounded-lg border flex items-center gap-1.5 transition"
                                 :class="storageRemaining <= 0 ? 'bg-rose-50 dark:bg-rose-950/60 border-rose-200/80 dark:border-rose-800/70 text-rose-700 dark:text-rose-300' : 'bg-slate-50/90 dark:bg-slate-800/60 border-slate-200/80 dark:border-slate-700/70 text-slate-700 dark:text-slate-300'">
                                <i class="fa-solid fa-warehouse text-purple-600 dark:text-purple-400"></i>
                                <span>Celopslag: <span class="font-mono font-black" x-text="storageUsed + '/' + storageCapacity"></span></span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded-full font-bold font-mono"
                                      :class="storageRemaining <= 0 ? 'bg-rose-600 text-white' : 'bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300'"
                                      x-text="storageRemaining > 0 ? storageRemaining + ' vrij' : 'VOL'"></span>
                            </div>
                            <button type="button" @click="setShopTab('inventaris')" class="text-xs font-bold text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 flex items-center gap-1.5 cursor-pointer bg-purple-50 dark:bg-purple-950/60 hover:bg-purple-100 dark:hover:bg-purple-900/60 px-3 py-1.5 rounded-lg transition w-fit">
                                <i class="fa-solid fa-box-archive"></i>
                                <span x-text="'Plunjezak (' + totalInvCount + ')'">Plunjezak ({{ $inventory->sum('quantity') }})</span>
                            </button>
                        </div>
                    </div>

                    @php
                        $categoryCounts = [
                            'eten' => 0,
                            'drank' => 0,
                            'medisch' => 0,
                            'kracht' => 0,
                            'smokkel' => 0,
                        ];
                        foreach($items as $it) {
                            $c = $getItemData($it)['cat'];
                            if(isset($categoryCounts[$c])) {
                                $categoryCounts[$c]++;
                            }
                        }
                    @endphp

                    <!-- Category Filter Pills -->
                    <div class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto pb-1 scrollbar-none border-b border-slate-100 dark:border-slate-800">
                        <button type="button"
                                @click="itemFilter = 'all'"
                                :class="itemFilter === 'all' ? 'bg-purple-600 text-white shadow-xs font-extrabold' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold border border-slate-200/60 dark:border-slate-700/60'"
                                class="px-3 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-1.5 shrink-0">
                            <i class="fa-solid fa-border-all text-[11px]"></i>
                            <span>Volledig Grid</span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full font-mono"
                                  :class="itemFilter === 'all' ? 'bg-purple-700 text-purple-100' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300'">
                                {{ count($items) }}
                            </span>
                        </button>
                        <button type="button"
                                @click="itemFilter = 'eten'"
                                :class="itemFilter === 'eten' ? 'bg-purple-600 text-white shadow-xs font-extrabold' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold border border-slate-200/60 dark:border-slate-700/60'"
                                class="px-3 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-1.5 shrink-0">
                            <i class="fa-solid fa-burger text-[11px]" :class="itemFilter === 'eten' ? 'text-white' : 'text-amber-500'"></i>
                            <span>Kantinekeuken</span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full font-mono"
                                  :class="itemFilter === 'eten' ? 'bg-purple-700 text-purple-100' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300'">
                                {{ $categoryCounts['eten'] ?? 0 }}
                            </span>
                        </button>
                        <button type="button"
                                @click="itemFilter = 'drank'"
                                :class="itemFilter === 'drank' ? 'bg-purple-600 text-white shadow-xs font-extrabold' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold border border-slate-200/60 dark:border-slate-700/60'"
                                class="px-3 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-1.5 shrink-0">
                            <i class="fa-solid fa-bolt text-[11px]" :class="itemFilter === 'drank' ? 'text-white' : 'text-cyan-500'"></i>
                            <span>Cafeïne &amp; Drank</span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full font-mono"
                                  :class="itemFilter === 'drank' ? 'bg-purple-700 text-purple-100' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300'">
                                {{ $categoryCounts['drank'] ?? 0 }}
                            </span>
                        </button>
                        <button type="button"
                                @click="itemFilter = 'medisch'"
                                :class="itemFilter === 'medisch' ? 'bg-purple-600 text-white shadow-xs font-extrabold' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold border border-slate-200/60 dark:border-slate-700/60'"
                                class="px-3 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-1.5 shrink-0">
                            <i class="fa-solid fa-kit-medical text-[11px]" :class="itemFilter === 'medisch' ? 'text-white' : 'text-rose-500'"></i>
                            <span>Ziekenboeg</span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full font-mono"
                                  :class="itemFilter === 'medisch' ? 'bg-purple-700 text-purple-100' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300'">
                                {{ $categoryCounts['medisch'] ?? 0 }}
                            </span>
                        </button>
                        <button type="button"
                                @click="itemFilter = 'kracht'"
                                :class="itemFilter === 'kracht' ? 'bg-purple-600 text-white shadow-xs font-extrabold' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold border border-slate-200/60 dark:border-slate-700/60'"
                                class="px-3 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-1.5 shrink-0">
                            <i class="fa-solid fa-dumbbell text-[11px]" :class="itemFilter === 'kracht' ? 'text-white' : 'text-indigo-500'"></i>
                            <span>Gevechtsgear</span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full font-mono"
                                  :class="itemFilter === 'kracht' ? 'bg-purple-700 text-purple-100' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300'">
                                {{ $categoryCounts['kracht'] ?? 0 }}
                            </span>
                        </button>
                        <button type="button"
                                @click="itemFilter = 'smokkel'"
                                :class="itemFilter === 'smokkel' ? 'bg-purple-600 text-white shadow-xs font-extrabold' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold border border-slate-200/60 dark:border-slate-700/60'"
                                class="px-3 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-1.5 shrink-0">
                            <i class="fa-solid fa-mask text-[11px]" :class="itemFilter === 'smokkel' ? 'text-white' : 'text-slate-600'"></i>
                            <span>Gereedschap &amp; Maskers</span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full font-mono"
                                  :class="itemFilter === 'smokkel' ? 'bg-purple-700 text-purple-100' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300'">
                                {{ $categoryCounts['smokkel'] ?? 0 }}
                            </span>
                        </button>
                    </div>

                    <!-- MODERN PRODUCT GRID -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-4">
                        @foreach($items as $item)
                        @php
                            $ownedCount = $item->slug === 'bivakmuts-masker'
                                ? (int) ($character->masks ?? 0)
                                : ($inventory->firstWhere('item_id', $item->id)?->quantity ?? 0);
                            $itemData = $getItemData($item);
                            $isItemDailyDeal = $dailyDealItem && $dailyDealItem->id === $item->id;
                            $itemTotalDiscountPct = min(25, $charismaDiscountPct + ($isItemDailyDeal ? $dailyDealDiscountPct : 0));

                            $energyGainPreview = $item->slug === 'adrenaline-injectie'
                                ? max(0, $playerStats['max_energy'] - $playerStats['energy'])
                                : (int) ($item->energy_restore ?? 0);
                            $healthGainPreview = $item->slug === 'ehbo-noodverbanddoos'
                                ? max(0, $playerStats['max_health'] - $playerStats['health'])
                                : (int) ($item->health_restore ?? 0);

                            $projectedEnergy = min($playerStats['max_energy'], $playerStats['energy'] + $energyGainPreview);
                            $projectedHealth = min($playerStats['max_health'], $playerStats['health'] + $healthGainPreview);
                        @endphp
                        <div x-show="itemFilter === 'all' || itemFilter === '{{ $itemData['cat'] }}'"
                             class="bg-slate-50/90 dark:bg-slate-800/60 border {{ $isItemDailyDeal ? 'border-amber-400 dark:border-amber-500 ring-1 ring-amber-400/50' : 'border-slate-200/80 dark:border-slate-700/70 hover:border-purple-300 dark:hover:border-purple-600/70' }} rounded-xl p-4 flex flex-col justify-between gap-3 transition shadow-xs relative">
                            <div class="space-y-2.5">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="w-10 h-10 rounded-xl {{ $itemData['badgeClass'] }} flex items-center justify-center text-base shrink-0 shadow-2xs">
                                        <i class="{{ $itemData['icon'] }}"></i>
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-wrap justify-end">
                                        @if($isItemDailyDeal)
                                            <span class="px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 text-[10px] font-black uppercase tracking-wider">
                                                Dagdeal -{{ $dailyDealDiscountPct }}%
                                            </span>
                                        @endif
                                        <template x-if="ownedCounts[{{ $item->id }}] > 0">
                                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300 text-[10px] font-black shrink-0 transition"
                                                  x-text="'{{ $item->category === 'equipment' ? '1x (Actief)' : '' }}' || (ownedCounts[{{ $item->id }}] + 'x in bezit')">
                                                {{ $item->category === 'equipment' ? '1x (Actief)' : $ownedCount.'x in bezit' }}
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white">{{ $item->name }}</h4>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2 mt-0.5">{{ $item->description ?: 'Geselecteerd artikel uit de paviljoenbevoorrading.' }}</p>
                                </div>

                                <!-- VISUELE STAT-VERGELIJKING MET HUIDIGE STATS VAN DE SPELER -->
                                <div class="p-2.5 rounded-xl bg-white/90 dark:bg-slate-900/80 border border-slate-200/70 dark:border-slate-700/70 space-y-1.5 text-[10px]">
                                    <div class="font-extrabold uppercase tracking-wider text-slate-400 flex items-center justify-between">
                                        <span>Dossier-Impact</span>
                                        <i class="fa-solid fa-scale-unbalanced text-purple-500"></i>
                                    </div>

                                    @if($item->slug === 'adrenaline-injectie' || $item->energy_restore > 0)
                                        <div class="space-y-1">
                                            <div class="flex items-center justify-between font-bold">
                                                <span class="text-emerald-700 dark:text-emerald-400"><i class="fa-solid fa-bolt text-amber-500 mr-1"></i>Energie</span>
                                                <span class="font-mono text-slate-800 dark:text-slate-200">
                                                    {{ $playerStats['energy'] }} &rarr; <strong class="text-emerald-600 dark:text-emerald-400">{{ $projectedEnergy }}</strong>/{{ $playerStats['max_energy'] }}
                                                </span>
                                            </div>
                                            <div class="w-full h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden flex">
                                                <div class="h-full bg-slate-400 dark:bg-slate-500" style="width: {{ min(100, round(($playerStats['energy'] / $playerStats['max_energy']) * 100)) }}%"></div>
                                                <div class="h-full bg-emerald-500" style="width: {{ max(0, min(100 - round(($playerStats['energy'] / $playerStats['max_energy']) * 100), round((($projectedEnergy - $playerStats['energy']) / $playerStats['max_energy']) * 100))) }}%"></div>
                                            </div>
                                        </div>
                                    @endif

                                    @if($item->slug === 'ehbo-noodverbanddoos' || $item->health_restore > 0)
                                        <div class="space-y-1">
                                            <div class="flex items-center justify-between font-bold">
                                                <span class="text-rose-700 dark:text-rose-400"><i class="fa-solid fa-heart text-rose-500 mr-1"></i>Gezondheid</span>
                                                <span class="font-mono text-slate-800 dark:text-slate-200">
                                                    {{ $playerStats['health'] }} &rarr; <strong class="text-rose-600 dark:text-rose-400">{{ $projectedHealth }}</strong>/{{ $playerStats['max_health'] }}
                                                </span>
                                            </div>
                                            <div class="w-full h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden flex">
                                                <div class="h-full bg-slate-400 dark:bg-slate-500" style="width: {{ min(100, round(($playerStats['health'] / $playerStats['max_health']) * 100)) }}%"></div>
                                                <div class="h-full bg-rose-500" style="width: {{ max(0, min(100 - round(($playerStats['health'] / $playerStats['max_health']) * 100), round((($projectedHealth - $playerStats['health']) / $playerStats['max_health']) * 100))) }}%"></div>
                                            </div>
                                        </div>
                                    @endif

                                    @if($item->strength_bonus > 0 || $item->slug === 'eiwitshake-bodybuilder')
                                        @php
                                            $strDelta = $item->slug === 'eiwitshake-bodybuilder' ? 1 : (int) $item->strength_bonus;
                                            $newStr = $ownedCount > 0 && $item->category === 'equipment' ? $playerStats['strength'] : ($playerStats['strength'] + $strDelta);
                                        @endphp
                                        <div class="flex items-center justify-between font-bold text-purple-700 dark:text-purple-300">
                                            <span><i class="fa-solid fa-hand-fist mr-1"></i>Kracht</span>
                                            <span class="font-mono">{{ $playerStats['strength'] }} &rarr; {{ $newStr }} (+{{ $strDelta }})</span>
                                        </div>
                                    @endif

                                    @if($item->defense_bonus > 0)
                                        @php
                                            $newDef = $ownedCount > 0 && $item->category === 'equipment' ? $playerStats['defense'] : ($playerStats['defense'] + (int) $item->defense_bonus);
                                        @endphp
                                        <div class="flex items-center justify-between font-bold text-blue-700 dark:text-blue-300">
                                            <span><i class="fa-solid fa-shield-halved mr-1"></i>Verdediging</span>
                                            <span class="font-mono">{{ $playerStats['defense'] }} &rarr; {{ $newDef }} (+{{ $item->defense_bonus }})</span>
                                        </div>
                                    @endif

                                    @if($item->stamina_bonus > 0 || $item->slug === 'multivitaminen-focus')
                                        @php
                                            $staDelta = $item->slug === 'multivitaminen-focus' ? 1 : (int) $item->stamina_bonus;
                                            $newSta = $ownedCount > 0 && $item->category === 'equipment' ? $playerStats['stamina'] : ($playerStats['stamina'] + $staDelta);
                                        @endphp
                                        <div class="flex items-center justify-between font-bold text-amber-700 dark:text-amber-300">
                                            <span><i class="fa-solid fa-person-running mr-1"></i>Conditie</span>
                                            <span class="font-mono">{{ $playerStats['stamina'] }} &rarr; {{ $newSta }} (+{{ $staDelta }})</span>
                                        </div>
                                    @endif

                                    @if(($item->intelligence_bonus ?? 0) > 0)
                                        <div class="flex items-center justify-between font-bold text-indigo-700 dark:text-indigo-300">
                                            <span><i class="fa-solid fa-brain mr-1"></i>Intelligentie</span>
                                            <span class="font-mono">{{ $playerStats['intelligence'] }} &rarr; {{ $playerStats['intelligence'] + (int) $item->intelligence_bonus }} (+{{ $item->intelligence_bonus }})</span>
                                        </div>
                                    @endif

                                    @if(($item->charisma_bonus ?? 0) > 0)
                                        <div class="flex items-center justify-between font-bold text-pink-700 dark:text-pink-300">
                                            <span><i class="fa-solid fa-comments mr-1"></i>Charisma</span>
                                            <span class="font-mono">{{ $playerStats['charisma'] }} &rarr; {{ $playerStats['charisma'] + (int) $item->charisma_bonus }} (+{{ $item->charisma_bonus }})</span>
                                        </div>
                                    @endif

                                    @if(($item->discipline_bonus ?? 0) > 0 || $item->slug === 'strip-oxazepam')
                                        @php
                                            $disDelta = $item->slug === 'strip-oxazepam' ? 1 : (int) ($item->discipline_bonus ?? 0);
                                        @endphp
                                        <div class="flex items-center justify-between font-bold text-cyan-700 dark:text-cyan-300">
                                            <span><i class="fa-solid fa-scale-balanced mr-1"></i>Discipline</span>
                                            <span class="font-mono">{{ $playerStats['discipline'] }} &rarr; {{ $playerStats['discipline'] + $disDelta }} (+{{ $disDelta }})</span>
                                        </div>
                                    @endif

                                    @if($item->slug === 'bivakmuts-masker')
                                        <div class="flex items-center justify-between font-bold text-indigo-700 dark:text-indigo-300">
                                            <span><i class="fa-solid fa-mask mr-1"></i>Maskervoorraad</span>
                                            <span class="font-mono">{{ $character->masks ?? 0 }} &rarr; {{ ($character->masks ?? 0) + 1 }} (+1)</span>
                                        </div>
                                    @elseif($item->slug === 'ijzerzaagje')
                                        <div class="flex items-center justify-between font-bold text-amber-700 dark:text-amber-300">
                                            <span><i class="fa-solid fa-hourglass-half mr-1"></i>Celstraf-Reductie</span>
                                            <span class="font-mono">-30 min per stuk</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @php
                                $isEquipment = $item->category === 'equipment';
                                $isRestrictedStat = in_array($item->slug, ['eiwitshake-bodybuilder', 'multivitaminen-focus'], true);
                                $cooldownKey = 'last_bought_' . str_replace('-', '_', $item->slug);
                                $lastBought = $character->perks[$cooldownKey] ?? null;
                                $statCooldownUntil = $lastBought ? \Carbon\Carbon::parse($lastBought)->addHours(6) : null;
                                $isStatOnCooldown = $statCooldownUntil && $statCooldownUntil->isFuture();
                                $statRemainingMinutes = $isStatOnCooldown ? max(1, (int) ceil(now()->diffInMinutes($statCooldownUntil))) : 0;
                                $statRemainingHours = intdiv($statRemainingMinutes, 60);
                                $statRemainingMins = $statRemainingMinutes % 60;
                                $statRemainingText = $statRemainingHours > 0 ? "{$statRemainingHours}u {$statRemainingMins}m" : "{$statRemainingMins}m";

                                $isDirectDelivery = in_array($item->slug, [
                                    'bivakmuts-masker',
                                    'smokkelpakketje',
                                    'eiwitshake-bodybuilder',
                                    'multivitaminen-focus',
                                    'haloperidol-forte',
                                ], true);
                            @endphp
                            <div class="pt-2.5 border-t border-slate-200/70 dark:border-slate-700/60 space-y-2.5"
                                 x-data="{
                                     buyQty: 1,
                                     isEquipment: {{ $isEquipment ? 'true' : 'false' }},
                                     isRestrictedStat: {{ $isRestrictedStat ? 'true' : 'false' }},
                                     isOnCooldown: {{ $isStatOnCooldown ? 'true' : 'false' }},
                                     isDirectDelivery: {{ $isDirectDelivery ? 'true' : 'false' }},
                                     unitPrice: {{ $item->price_kc }},
                                     discountPct: {{ $itemTotalDiscountPct }},
                                     get actualPrice() {
                                         return this.discountPct > 0 ? Math.round(this.unitPrice * (1 - (this.discountPct / 100))) : this.unitPrice;
                                     },
                                     get totalCost() {
                                         return (this.isEquipment || this.isRestrictedStat) ? this.actualPrice : (this.actualPrice * this.buyQty);
                                     },
                                     get isOwned() {
                                         return this.isEquipment && (ownedCounts[{{ $item->id }}] > 0);
                                     },
                                     get maxByMoney() {
                                         let cash = (typeof stats !== 'undefined' && stats.cash !== undefined) ? stats.cash : {{ (int) $character->cash }};
                                         let bank = (typeof stats !== 'undefined' && stats.bank !== undefined) ? stats.bank : {{ (int) $character->bank }};
                                         return Math.floor((cash + bank) / Math.max(1, this.actualPrice));
                                     },
                                     get maxByStorage() {
                                         return this.isDirectDelivery ? 999999 : Math.max(0, storageRemaining);
                                     },
                                     get maxBuyable() {
                                         if (this.isEquipment) {
                                             return this.isOwned ? 0 : (this.maxByMoney >= 1 && this.maxByStorage >= 1 ? 1 : 0);
                                         }
                                         if (this.isRestrictedStat) {
                                             return this.isOnCooldown ? 0 : (this.maxByMoney >= 1 ? 1 : 0);
                                         }
                                         return Math.max(0, Math.min(100, Math.min(this.maxByMoney, this.maxByStorage)));
                                     },
                                     setQty(amount) {
                                         if (this.isEquipment || this.isRestrictedStat) {
                                             this.buyQty = 1;
                                             return;
                                         }
                                         if (this.maxBuyable <= 0) {
                                             this.buyQty = 1;
                                         } else {
                                             this.buyQty = Math.max(1, Math.min(amount, this.maxBuyable));
                                         }
                                     }
                                 }">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-500 dark:text-slate-400 font-medium">Tarief:</span>
                                    <div class="text-right">
                                        @if($itemTotalDiscountPct > 0)
                                            <span class="text-[10px] text-slate-400 line-through font-mono mr-1">€{{ number_format($item->price_kc, 0, ',', '.') }}</span>
                                        @endif
                                        <span class="font-extrabold text-emerald-700 dark:text-emerald-400 font-mono"
                                              x-text="'€' + new Intl.NumberFormat('nl-NL').format(actualPrice)">
                                            €{{ number_format($item->price_kc, 0, ',', '.') }}
                                        </span>
                                        <template x-if="!isEquipment && !isRestrictedStat && buyQty > 1">
                                            <span class="text-[10px] text-slate-400 dark:text-slate-400 block font-mono" x-text="'Som: €' + new Intl.NumberFormat('nl-NL').format(totalCost)"></span>
                                        </template>
                                    </div>
                                </div>

                                <template x-if="isEquipment">
                                    <div class="flex items-center justify-between py-1.5 px-3 rounded-xl bg-slate-100/80 dark:bg-slate-800/80 border border-slate-200/70 dark:border-slate-700/60 text-[11px]">
                                        <span class="text-slate-600 dark:text-slate-300 font-bold flex items-center gap-1.5">
                                            <i class="fa-solid fa-shield-halved text-purple-600 dark:text-purple-400"></i>
                                            <span>Permanente Uitrusting</span>
                                        </span>
                                        <template x-if="isOwned">
                                            <span class="text-emerald-600 dark:text-emerald-400 font-black flex items-center gap-1">
                                                <i class="fa-solid fa-circle-check"></i>
                                                <span>Actief</span>
                                            </span>
                                        </template>
                                        <template x-if="!isOwned">
                                            <span class="text-slate-400 dark:text-slate-500 font-bold font-mono">1x uniek</span>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="isRestrictedStat">
                                    <div class="flex items-center justify-between py-1.5 px-3 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/70 text-[11px]">
                                        <span class="text-amber-800 dark:text-amber-300 font-extrabold flex items-center gap-1.5">
                                            <i class="fa-solid fa-clock text-amber-500"></i>
                                            <span>Limiet: 1x per 6 uur</span>
                                        </span>
                                        <span class="text-slate-500 dark:text-slate-400 font-bold font-mono">1 dosis</span>
                                    </div>
                                </template>
                                <template x-if="!isEquipment && !isRestrictedStat">
                                    <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800/80 p-1 rounded-xl border border-slate-200/70 dark:border-slate-700/60">
                                        <button type="button" @click="setQty(1)" :class="buyQty === 1 ? 'bg-white dark:bg-slate-700 text-purple-700 dark:text-purple-300 shadow-2xs font-black' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 font-bold'" class="flex-1 py-1 rounded-lg text-[10px] sm:text-[11px] transition cursor-pointer">1x</button>
                                        <button type="button" @click="setQty(5)" :class="buyQty === 5 ? 'bg-white dark:bg-slate-700 text-purple-700 dark:text-purple-300 shadow-2xs font-black' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 font-bold'" class="flex-1 py-1 rounded-lg text-[10px] sm:text-[11px] transition cursor-pointer">5x</button>
                                        <button type="button" @click="setQty(10)" :class="buyQty === 10 ? 'bg-white dark:bg-slate-700 text-purple-700 dark:text-purple-300 shadow-2xs font-black' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 font-bold'" class="flex-1 py-1 rounded-lg text-[10px] sm:text-[11px] transition cursor-pointer">10x</button>
                                        <button type="button" @click="buyQty = Math.max(1, maxBuyable)" :class="buyQty > 10 || (maxBuyable > 0 && buyQty === maxBuyable) ? 'bg-white dark:bg-slate-700 text-purple-700 dark:text-purple-300 shadow-2xs font-black' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 font-bold'" class="flex-1 py-1 rounded-lg text-[10px] sm:text-[11px] transition cursor-pointer">
                                            <span class="hidden sm:inline" x-text="maxBuyable > 0 ? ('Max (' + maxBuyable + ')') : 'Max'">Max</span>
                                            <span class="sm:hidden">Max</span>
                                        </button>
                                    </div>
                                </template>

                                @if($isStatOnCooldown)
                                <div class="text-[11px] text-amber-700 dark:text-amber-300 font-bold text-center flex items-center justify-center gap-1.5 py-1 px-2 bg-amber-50 dark:bg-amber-950/80 rounded-lg border border-amber-300/80 dark:border-amber-800">
                                    <i class="fa-solid fa-hourglass-half text-amber-500 text-[10px]"></i>
                                    <span>Doseringspauze: nog {{ $statRemainingText }}</span>
                                </div>
                                @endif

                                <form action="{{ route('shop.buy', $item->id) }}" method="POST" data-turbo="false" @submit.prevent="buyItemViaAjax($event, {{ $item->id }}, (isEquipment || isRestrictedStat) ? 1 : buyQty)">
                                    @csrf
                                    <input type="hidden" name="quantity" :value="(isEquipment || isRestrictedStat) ? 1 : buyQty">

                                    <template x-if="isOwned">
                                        <div class="w-full py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-emerald-700 dark:text-emerald-400 font-black text-xs flex items-center justify-center gap-1.5 border border-emerald-500/30 dark:border-emerald-500/20 shadow-2xs select-none">
                                            <i class="fa-solid fa-circle-check text-xs"></i>
                                            <span>Reeds Gekoppeld</span>
                                        </div>
                                    </template>
                                    <template x-if="!isOwned">
                                        <button type="submit"
                                                :disabled="itemBuying[{{ $item->id }}] || (!isDirectDelivery && storageRemaining <= 0) || maxByMoney < 1 || isOnCooldown"
                                                :class="(!isDirectDelivery && storageRemaining <= 0) || maxByMoney < 1 || isOnCooldown ? 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed' : 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white cursor-pointer'"
                                                class="w-full py-2.5 rounded-xl text-white font-black text-xs transition-all duration-150 flex items-center justify-center gap-1.5 shadow-xs">
                                            <template x-if="itemBuying[{{ $item->id }}]">
                                                <span class="inline-flex items-center gap-1.5 text-purple-200">
                                                    <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                                                    <span>Afrekenen...</span>
                                                </span>
                                            </template>
                                            <template x-if="!itemBuying[{{ $item->id }}] && (!itemToast[{{ $item->id }}] || itemToast[{{ $item->id }}].type !== 'success')">
                                                <span class="inline-flex items-center gap-1.5">
                                                    <template x-if="isOnCooldown">
                                                        <span><i class="fa-solid fa-lock text-[11px] mr-1"></i> Pauze ({{ $statRemainingText }})</span>
                                                    </template>
                                                    <template x-if="!isOnCooldown && !isDirectDelivery && storageRemaining <= 0">
                                                        <span>Celopslag Vol</span>
                                                    </template>
                                                    <template x-if="!isOnCooldown && (isDirectDelivery || storageRemaining > 0) && maxByMoney < 1">
                                                        <span>Saldo Tekort</span>
                                                    </template>
                                                    <template x-if="!isOnCooldown && (isDirectDelivery || storageRemaining > 0) && maxByMoney >= 1">
                                                        <span class="inline-flex items-center gap-1.5">
                                                            <i class="fa-solid fa-cart-plus text-xs"></i>
                                                            <span x-text="isEquipment ? 'Uitrusting Aanschaffen' : (buyQty > 1 ? 'Sla ' + buyQty + 'x In' : 'Aanschaffen')"></span>
                                                        </span>
                                                    </template>
                                                </span>
                                            </template>
                                            <template x-if="!itemBuying[{{ $item->id }}] && itemToast[{{ $item->id }}] && itemToast[{{ $item->id }}].type === 'success'">
                                                <span class="inline-flex items-center gap-1.5 text-emerald-300 font-black">
                                                    <i class="fa-solid fa-circle-check text-xs"></i>
                                                    <span x-text="itemToast[{{ $item->id }}].text"></span>
                                                </span>
                                            </template>
                                        </button>
                                    </template>
                                    <template x-if="itemToast[{{ $item->id }}] && itemToast[{{ $item->id }}].type === 'error'">
                                        <div class="mt-1 text-[11px] font-bold text-rose-600 text-center flex items-center justify-center gap-1" x-text="itemToast[{{ $item->id }}].text"></div>
                                    </template>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- TAB: PAKKETTEN & BUNDEL-GRID -->
            <div x-show="shopTab === 'pakketten'" class="space-y-6">
                @include('partials.bmac_banner')

                <div class="bg-gradient-to-r from-purple-50 via-pink-50 to-purple-50 dark:from-purple-950/40 dark:via-pink-950/20 dark:to-purple-950/40 border border-purple-200/80 dark:border-purple-800/60 rounded-2xl p-4 text-center shadow-xs">
                    <span class="text-[10px] font-black uppercase tracking-widest text-purple-700 dark:text-purple-300 bg-purple-100 dark:bg-purple-950/80 border border-purple-200/80 dark:border-purple-800/70 px-3 py-0.5 rounded-full mb-1.5 inline-block">
                        GROOTVERBRUIK BONUS
                    </span>
                    <p class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-white mt-1">
                        Elke bundel vanaf 100 Sterren levert automatisch <strong class="text-purple-700 dark:text-purple-400">+1 extra XXL transportkoffer per stuk</strong> op!
                    </p>
                </div>

                <!-- Category Filter Pills for Bundles -->
                <div class="flex flex-wrap items-center justify-center gap-2 bg-slate-100/90 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/70 p-1.5 rounded-xl">
                    <button type="button" @click="bundleFilter = 'all'"
                            :class="bundleFilter === 'all' ? 'bg-purple-600 text-white shadow-xs font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-bold'"
                            class="px-4 py-2 rounded-lg text-xs uppercase transition cursor-pointer">
                        Alle Bundels
                    </button>
                    <button type="button" @click="bundleFilter = 'smokkel'"
                            :class="bundleFilter === 'smokkel' ? 'bg-purple-600 text-white shadow-xs font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-bold'"
                            class="px-4 py-2 rounded-lg text-xs uppercase transition cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-box text-xs"></i> Smokkel
                    </button>
                    <button type="button" @click="bundleFilter = 'kluisbuit'"
                            :class="bundleFilter === 'kluisbuit' ? 'bg-purple-600 text-white shadow-xs font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-bold'"
                            class="px-4 py-2 rounded-lg text-xs uppercase transition cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-vault text-xs"></i> Kluisbuit
                    </button>
                    <button type="button" @click="bundleFilter = 'syndicaat'"
                            :class="bundleFilter === 'syndicaat' ? 'bg-purple-600 text-white shadow-xs font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-bold'"
                            class="px-4 py-2 rounded-lg text-xs uppercase transition cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-crown text-xs"></i> Syndicaat
                    </button>
                </div>

                <!-- Bundle Cards Grid (replaces old vertical table list) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($bundles as $bKey => $b)
                    <div x-show="bundleFilter === 'all' || bundleFilter === '{{ $b['category'] }}'"
                         @click="selectedBundleId = '{{ $bKey }}'"
                         :class="selectedBundleId === '{{ $bKey }}' ? 'ring-2 ring-purple-600 border-purple-600 bg-purple-50/40 dark:bg-purple-950/30' : 'border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-purple-300'"
                         class="rounded-2xl border p-4 flex flex-col justify-between gap-3 transition cursor-pointer shadow-xs">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between gap-2">
                                <span class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 flex items-center justify-center text-base">
                                    <i class="fa-solid fa-boxes-packing"></i>
                                </span>
                                <span class="px-2.5 py-1 rounded-full bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 font-black text-xs inline-flex items-center gap-1">
                                    {{ number_format($b['cost_stars']) }} <i class="fa-solid fa-star text-amber-500 text-[10px]"></i>
                                </span>
                            </div>
                            <h4 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white">{{ $b['name'] }}</h4>
                            <div class="grid grid-cols-2 gap-1.5 text-[11px] font-bold text-slate-600 dark:text-slate-300 pt-1">
                                <span><i class="fa-solid fa-coins text-emerald-500 mr-1"></i>€{{ number_format($b['cash'], 0, ',', '.') }}</span>
                                <span><i class="fa-solid fa-key text-purple-500 mr-1"></i>{{ $b['codes'] }} codes</span>
                                <span><i class="fa-solid fa-briefcase text-indigo-500 mr-1"></i>{{ $b['koffers'] }} koffers</span>
                                <span><i class="fa-solid fa-mask text-pink-500 mr-1"></i>{{ $b['masks'] }} maskers</span>
                            </div>
                        </div>
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] font-black">
                            <span :class="selectedBundleId === '{{ $bKey }}' ? 'text-purple-700 dark:text-purple-300' : 'text-slate-400'"
                                  x-text="selectedBundleId === '{{ $bKey }}' ? '✓ Geselecteerd voor afrekening' : 'Klik om te selecteren'"></span>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Selected Bundle Checkout & Gifting Card -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-5"
                     x-data="{ currentBundle() { return bundles[selectedBundleId] || bundles['afdelingsbaas_deal'] || Object.values(bundles)[0]; } }">
                    <div class="border-b border-slate-100 dark:border-slate-800 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-wider text-purple-600 dark:text-purple-400 block">Geselecteerde Zending</span>
                            <h3 class="text-lg font-extrabold text-slate-900 dark:text-white" x-text="currentBundle().name"></h3>
                        </div>
                        <div class="text-sm font-extrabold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/70 px-3.5 py-1.5 rounded-full w-fit inline-flex items-center gap-1.5">
                            <span>Tarief: <span x-text="currentBundle().cost_stars"></span> Sterren</span>
                            <i class="fa-solid fa-star text-amber-500 text-xs"></i>
                        </div>
                    </div>

                    <form action="{{ route('shop.buy-bundle') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="bundle_id" :value="selectedBundleId">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Aantal zendingen (1–10):
                                </label>
                                <input type="number" name="quantity" min="1" max="10" x-model="bundleQty" required
                                       class="w-full bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl px-3.5 py-2.5 text-xs font-bold focus:outline-none focus:border-purple-500">
                            </div>

                            <!-- Gifting Selector with Safe JSON Encoding -->
                            <div x-data="{
                                search: '',
                                selectedId: '',
                                selectedName: '',
                                openDropdown: false,
                                patients: @js(array_merge([
                                    [
                                        'id' => '',
                                        'name' => 'Mijn eigen account (' . $character->name . ')',
                                        'level' => (int) $character->level,
                                        'respect' => (int) $character->respect,
                                        'isSelf' => true,
                                    ],
                                ], collect($otherPatients)->map(fn($p) => [
                                    'id' => (string) $p->id,
                                    'name' => $p->name,
                                    'level' => (int) $p->level,
                                    'respect' => (int) $p->respect,
                                    'isSelf' => false,
                                ])->values()->all())),
                                get filtered() {
                                    if (!this.search.trim()) return this.patients.slice(0, 25);
                                    return this.patients.filter(p => p.name.toLowerCase().includes(this.search.toLowerCase())).slice(0, 25);
                                },
                                select(patient) {
                                    this.selectedId = patient.id;
                                    this.selectedName = patient.name;
                                    this.search = patient.name;
                                    this.openDropdown = false;
                                    recipientId = patient.id;
                                }
                            }" class="relative">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Bestemmeling (eigen dossier of medepatiënt):
                                </label>
                                <input type="hidden" name="recipient_id" :value="selectedId">
                                <input type="hidden" name="recipient_name" :value="search">

                                <div class="relative flex items-center">
                                    <input type="text"
                                           x-model="search"
                                           @focus="openDropdown = true"
                                           @input="openDropdown = true; selectedId = ''"
                                           @click.outside="openDropdown = false"
                                           autocomplete="off"
                                           placeholder="Mijn eigen account ({{ $character->name }}) of zoek naam..."
                                           class="w-full bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl px-3.5 py-2.5 text-xs font-bold focus:outline-none focus:border-purple-500">
                                </div>

                                <div x-show="openDropdown"
                                     x-transition
                                     class="absolute z-50 left-0 right-0 mt-1 max-h-52 overflow-y-auto bg-white dark:bg-slate-900 rounded-xl shadow-lg border border-slate-200/90 dark:border-slate-800 divide-y divide-slate-100 dark:divide-slate-800/80">
                                    <template x-for="p in filtered" :key="p.name">
                                        <button type="button"
                                                @click="select(p)"
                                                class="w-full text-left px-3.5 py-2.5 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 flex items-center justify-between text-xs transition cursor-pointer">
                                            <div>
                                                <span class="font-extrabold text-slate-900 dark:text-white block" x-text="p.name"></span>
                                                <span class="text-[10px] text-slate-400" x-text="'Level ' + p.level + ' • ' + p.respect + ' Respect'"></span>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <button type="submit"
                                :disabled="({{ $character->stars ?? 0 }}) < (currentBundle().cost_stars * bundleQty)"
                                class="w-full py-3 px-6 rounded-xl bg-purple-600 hover:bg-purple-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-black text-xs uppercase tracking-wider transition active:scale-95 shadow-xs cursor-pointer flex items-center justify-center gap-2">
                            <i class="fa-solid fa-paper-plane text-xs"></i>
                            <span>Zending Vrijgeven (<span x-text="currentBundle().cost_stars * bundleQty"></span> Sterren)</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- TAB: PLUNJEZAK & INVENTARIS -->
            <div x-show="shopTab === 'inventaris'" class="space-y-6">
                <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-box-archive text-purple-600 dark:text-purple-400"></i>
                                <span>Persoonlijke Plunjezak &amp; Voorraadbeheer</span>
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Gebruik rantsoenen voor direct herstel of beheer je actieve uitrusting.</p>
                        </div>
                        <button type="button" @click="setShopTab('items')" class="px-3.5 py-1.5 rounded-xl bg-purple-50 dark:bg-purple-950/60 hover:bg-purple-100 text-purple-700 dark:text-purple-300 font-black text-xs transition cursor-pointer flex items-center gap-1.5 w-fit">
                            <i class="fa-solid fa-plus"></i>
                            <span>Voorraad Aanvullen</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4">
                        <div class="p-3 bg-slate-50/90 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700/70 space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-slate-600 dark:text-slate-400"><i class="fa-solid fa-bolt text-amber-500 mr-1"></i>Energie</span>
                                <span class="text-slate-900 dark:text-white font-mono">{{ $character->energy }} / {{ $character->max_energy }}</span>
                            </div>
                            <div class="w-full h-2.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-amber-400 to-emerald-500 rounded-full"
                                     style="width: {{ min(100, round(($character->energy / max(1, $character->max_energy)) * 100)) }}%"></div>
                            </div>
                        </div>

                        <div class="p-3 bg-slate-50/90 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700/70 space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-slate-600 dark:text-slate-400"><i class="fa-solid fa-heart-pulse text-rose-500 mr-1"></i>Gezondheid (HP)</span>
                                <span class="text-slate-900 dark:text-white font-mono">{{ $character->health }} / {{ $character->max_health }}</span>
                            </div>
                            <div class="w-full h-2.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-rose-500 to-pink-500 rounded-full"
                                     style="width: {{ min(100, round(($character->health / max(1, $character->max_health)) * 100)) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs">
                    @if($inventory->where('quantity', '>', 0)->isEmpty())
                        <div class="text-center py-12 space-y-3">
                            <h4 class="text-sm font-extrabold text-slate-800 dark:text-white">Je plunjezak bevat momenteel geen artikelen</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                                Sla rantsoenen, medicatie of uitrustingsstukken in via het assortiments-grid.
                            </p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($inventory->where('quantity', '>', 0) as $inv)
                            @php
                                $invItemData = $getItemData($inv->item);
                                $isConsumableRecovery = ($inv->item->energy_restore > 0 || $inv->item->health_restore > 0 || in_array($inv->item->slug, ['sigaretten', 'ritalin', 'telefoon']));
                            @endphp
                            <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-4 flex flex-col justify-between gap-3 shadow-xs">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div class="w-10 h-10 rounded-xl {{ $invItemData['badgeClass'] }} flex items-center justify-center text-lg">
                                            <i class="{{ $invItemData['icon'] }}"></i>
                                        </div>
                                        <span class="px-2.5 py-0.5 rounded-full bg-purple-600 text-white font-black text-xs font-mono">
                                            {{ $inv->quantity }}x
                                        </span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-black text-slate-900 dark:text-white">{{ $inv->item->name }}</h4>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $inv->item->description }}</p>
                                    </div>
                                </div>

                                <div class="pt-2 border-t border-slate-200 dark:border-slate-800">
                                    @if($inv->item->category === 'equipment' || ! $inv->item->isConsumableItem())
                                        <div class="w-full py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-extrabold text-xs flex items-center justify-center gap-2">
                                            <i class="fa-solid fa-shield-halved text-purple-600"></i>
                                            <span>Passief Geactiveerd</span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <form action="{{ route('shop.consume', $inv->id) }}" method="POST" class="flex-1">
                                                @csrf
                                                <button type="submit" class="w-full py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs transition cursor-pointer">
                                                    1x Innemen
                                                </button>
                                            </form>
                                            @if($isConsumableRecovery && $inv->quantity > 1)
                                            <form action="{{ route('shop.consume', $inv->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="fill_max" value="1">
                                                <button type="submit" class="py-2 px-3 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs transition cursor-pointer">
                                                    Vol
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- TAB: STERRENKIOSK -->
            <div x-show="shopTab === 'sterren'" class="space-y-6">
                @include('partials.bmac_banner')

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- 1. Full Restore -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-500 flex items-center justify-center text-lg"><i class="fa-solid fa-bolt-lightning"></i></span>
                                <span class="px-2.5 py-1 rounded-full bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 font-black text-xs">5 ⭐</span>
                            </div>
                            <h4 class="text-sm font-extrabold text-slate-900 dark:text-white">Vitale Reset (100% Energie &amp; HP)</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Brengt zowel je energiepeil als levenspunten onmiddellijk terug naar 100%.</p>
                        </div>
                        <form action="{{ route('shop.buy-star-item') }}" method="POST" class="mt-4">
                            @csrf
                            <input type="hidden" name="item_type" value="full_restore">
                            <button type="submit" class="w-full py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs cursor-pointer">Direct Resetten (5 ⭐)</button>
                        </form>
                    </div>

                    <!-- 2. Prison Escape -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-500 flex items-center justify-center text-lg"><i class="fa-solid fa-lock-open"></i></span>
                                <span class="px-2.5 py-1 rounded-full bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 font-black text-xs">10 ⭐</span>
                            </div>
                            <h4 class="text-sm font-extrabold text-slate-900 dark:text-white">Directe Celvrijlating</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Wist je actuele celstraf en opent onmiddellijk de sluisdeur.</p>
                        </div>
                        <form action="{{ route('shop.buy-star-item') }}" method="POST" class="mt-4">
                            @csrf
                            <input type="hidden" name="item_type" value="prison_escape">
                            <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs cursor-pointer">Vrijlating Kopen (10 ⭐)</button>
                        </form>
                    </div>

                    <!-- 2b. Revive Serum -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-500 flex items-center justify-center text-lg"><i class="fa-solid fa-syringe"></i></span>
                                <span class="px-2.5 py-1 rounded-full bg-rose-50 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 font-black text-xs">€1,5M / {{ \App\Models\Character::REVIVE_SERUM_STARS }} ⭐</span>
                            </div>
                            <h4 class="text-sm font-extrabold text-slate-900 dark:text-white">{{ \App\Models\Character::REVIVE_SERUM_NAME }}</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Heft Code Blauw op, herstelt 100% HP en zet je liquidatieteller ({{ $character->getLiquidationStreakDeaths() }}/10) op 0/10.</p>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-4">
                            <form action="{{ route('hitman.revive_serum') }}" method="POST">
                                @csrf
                                <input type="hidden" name="currency" value="cash">
                                <button type="submit" class="w-full py-2.5 px-2 rounded-xl bg-emerald-600 text-white font-black text-xs cursor-pointer">€1.500.000</button>
                            </form>
                            <form action="{{ route('hitman.revive_serum') }}" method="POST">
                                @csrf
                                <input type="hidden" name="currency" value="stars">
                                <button type="submit" class="btn-star-gold w-full py-2.5 px-2 rounded-xl font-black text-xs cursor-pointer">{{ \App\Models\Character::REVIVE_SERUM_STARS }} Sterren</button>
                            </form>
                        </div>
                    </div>

                    <!-- 3. Maskers Pack (5x) -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-500 flex items-center justify-center text-lg"><i class="fa-solid fa-masks-theater"></i></span>
                                <span class="px-2.5 py-1 rounded-full bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 font-black text-xs">15 ⭐</span>
                            </div>
                            <h4 class="text-sm font-extrabold text-slate-900 dark:text-white">5x Bivakmutsen</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Onmisbaar voor anonieme zakkenroloperaties in de vleugels.</p>
                        </div>
                        <form action="{{ route('shop.buy-star-item') }}" method="POST" class="mt-4">
                            @csrf
                            <input type="hidden" name="item_type" value="mask_5">
                            <button type="submit" class="w-full py-2.5 rounded-xl bg-purple-600 text-white font-black text-xs cursor-pointer">5x Maskers (15 ⭐)</button>
                        </form>
                    </div>

                    <!-- 4a. Munitievoorraad (50 Kogels) -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-500 flex items-center justify-center text-lg"><i class="fa-solid fa-boxes-stacked"></i></span>
                                <span class="px-2.5 py-1 rounded-full bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 font-black text-xs">50 ⭐</span>
                            </div>
                            <h4 class="text-sm font-extrabold text-slate-900 dark:text-white">Patronendoos (+50 Kogels)</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Vult je wapenkluis direct aan met 50 scherpe patronen.</p>
                        </div>
                        <form action="{{ route('shop.buy-star-item') }}" method="POST" class="mt-4">
                            @csrf
                            <input type="hidden" name="item_type" value="bullets_50">
                            <button type="submit" class="w-full py-2.5 rounded-xl bg-amber-500 text-slate-950 font-black text-xs cursor-pointer">50 Kogels (50 ⭐)</button>
                        </form>
                    </div>

                    <!-- 4b. Kogelwerend Vest (12 Uur) -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 flex items-center justify-center text-lg"><i class="fa-solid fa-shield-halved"></i></span>
                                <span class="px-2.5 py-1 rounded-full bg-purple-100 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 font-black text-xs">150 ⭐</span>
                            </div>
                            <h4 class="text-sm font-extrabold text-slate-900 dark:text-white">Gepantserd Kevlar Vest (12u)</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400">12 uur ballistische bescherming met 45% terugslagkans op schutters.</p>
                            @if($character->hasVest())
                                <div data-testid="shop-vest-countdown" class="p-2.5 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 text-[11px] font-bold text-purple-800 dark:text-purple-300">
                                    Pantser actief: {{ gmdate('H:i:s', $character->getVestRemainingSeconds()) }}
                                </div>
                            @endif
                        </div>
                        <form action="{{ route('shop.buy-star-item') }}" method="POST" class="mt-4">
                            @csrf
                            <input type="hidden" name="item_type" value="bulletproof_vest">
                            <button type="submit" class="w-full py-2.5 rounded-xl bg-purple-600 text-white font-black text-xs cursor-pointer">Pantser Activeren (150 ⭐)</button>
                        </form>
                    </div>

                    <!-- 5. Vault Code Voucher -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 flex items-center justify-center text-lg"><i class="fa-solid fa-vault"></i></span>
                                <span class="px-2.5 py-1 rounded-full bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 font-black text-xs">25 ⭐</span>
                            </div>
                            <h4 class="text-sm font-extrabold text-slate-900 dark:text-white">Gecodeerde Kluis-Voucher</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Levert direct een unieke MedicijnKluis code in je berichtenbox.</p>
                        </div>
                        <form action="{{ route('shop.buy-star-item') }}" method="POST" class="mt-4">
                            @csrf
                            <input type="hidden" name="item_type" value="vault_code">
                            <button type="submit" class="w-full py-2.5 rounded-xl bg-amber-500 text-slate-950 font-black text-xs cursor-pointer">Voucher Kopen (25 ⭐)</button>
                        </form>
                    </div>

                    <!-- 6. 24h Protection -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="w-10 h-10 rounded-xl bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 flex items-center justify-center text-lg"><i class="fa-solid fa-shield-halved"></i></span>
                                <span class="px-2.5 py-1 rounded-full bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 font-black text-xs">30 ⭐</span>
                            </div>
                            <h4 class="text-sm font-extrabold text-slate-900 dark:text-white">24-Uurs Paviljoenimmuniteit</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Volledige 24-uurs afscherming tegen vijandige aanvallen en liquidaties.</p>
                            @if($character->hasIsolationProtection())
                                <div data-testid="shop-protection-countdown" class="p-2.5 rounded-xl bg-cyan-50 dark:bg-cyan-950/60 border border-cyan-200/80 text-[11px] font-bold text-cyan-800 dark:text-cyan-300">
                                    Schild actief: {{ gmdate('H:i:s', $character->getIsolationProtectionRemainingSeconds()) }}
                                </div>
                            @endif
                        </div>
                        <form action="{{ route('shop.buy-star-item') }}" method="POST" class="mt-4">
                            @csrf
                            <input type="hidden" name="item_type" value="protection_24h">
                            <button type="submit" class="w-full py-2.5 rounded-xl bg-cyan-600 text-white font-black text-xs cursor-pointer">Immuniteit (30 ⭐)</button>
                        </form>
                    </div>

                    <!-- 7. 3x XXL Koffers -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center text-lg"><i class="fa-solid fa-briefcase"></i></span>
                                <span class="px-2.5 py-1 rounded-full bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 font-black text-xs">35 ⭐</span>
                            </div>
                            <h4 class="text-sm font-extrabold text-slate-900 dark:text-white">3x XXL Transportkoffers</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Drie zwaar verzegelde koffers voor de kraakbank.</p>
                        </div>
                        <form action="{{ route('shop.buy-star-item') }}" method="POST" class="mt-4">
                            @csrf
                            <input type="hidden" name="item_type" value="xxl_cases_3">
                            <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-600 text-white font-black text-xs cursor-pointer">3x Koffers (35 ⭐)</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- TAB: SUPPORTER & DONATIES -->
            <div x-show="shopTab === 'donaties'" class="space-y-6">
                <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xs space-y-6">
                    <div class="border-b border-slate-100 dark:border-slate-800 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-black text-slate-900 dark:text-white">Serverondersteuning &amp; Reclamevrij Spelen</h2>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                                Draag vrijwillig bij aan het serveronderhoud en ontvang als dank direct Sterren, Maskers en VIP-privileges.
                            </p>
                        </div>
                        <a href="{{ route('vip.index') }}" class="px-4 py-2.5 rounded-xl bg-purple-600 text-white text-xs font-black shrink-0">
                            <i class="fa-solid fa-crown text-amber-400 mr-1"></i> VIP Overzicht
                        </a>
                    </div>
                    @include('partials.bmac_instructions')
                </div>
            </div>

        </div>

        <!-- Right Column (lg:col-span-3): Flipped Navigation & Telemetry Dock -->
        <div class="hidden lg:block lg:col-span-3 space-y-4 order-1 lg:order-2">
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs p-2.5 space-y-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                <div class="px-3 py-1.5 text-[10px] font-black uppercase tracking-wider text-slate-400">
                    Winkelafdelingen
                </div>
                <button type="button" @click="setShopTab('items')"
                        :class="shopTab === 'items' ? 'bg-purple-50 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70 font-black' : 'hover:bg-slate-100 dark:hover:bg-slate-800/60 text-slate-600 dark:text-slate-400 border border-transparent'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition cursor-pointer">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-table-cells-large text-emerald-600 dark:text-emerald-400 w-4 text-center"></i>
                        <span>Grid-Winkel &amp; Dagdeal</span>
                    </span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-400 text-slate-950 font-black">-{{ $dailyDealDiscountPct }}%</span>
                </button>

                <button type="button" @click="setShopTab('pakketten')"
                        :class="shopTab === 'pakketten' ? 'bg-purple-50 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70 font-black' : 'hover:bg-slate-100 dark:hover:bg-slate-800/60 text-slate-600 dark:text-slate-400 border border-transparent'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition cursor-pointer">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-boxes-packing text-purple-600 dark:text-purple-400 w-4 text-center"></i>
                        <span>Voorraadbundels</span>
                    </span>
                    <i class="fa-solid fa-chevron-left text-[10px] opacity-60"></i>
                </button>

                <button type="button" @click="setShopTab('sterren')"
                        :class="shopTab === 'sterren' ? 'bg-purple-50 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70 font-black' : 'hover:bg-slate-100 dark:hover:bg-slate-800/60 text-slate-600 dark:text-slate-400 border border-transparent'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition cursor-pointer">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-gem text-amber-500 w-4 text-center"></i>
                        <span>Sterrenkiosk</span>
                    </span>
                    <span class="text-[10px] text-amber-600 dark:text-amber-400 font-extrabold">{{ $character->stars ?? 0 }} ⭐</span>
                </button>

                <button type="button" @click="setShopTab('inventaris')"
                        :class="shopTab === 'inventaris' ? 'bg-purple-50 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70 font-black' : 'hover:bg-slate-100 dark:hover:bg-slate-800/60 text-slate-600 dark:text-slate-400 border border-transparent'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition cursor-pointer">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-box-archive text-purple-600 dark:text-purple-400 w-4 text-center"></i>
                        <span>Plunjezak</span>
                    </span>
                    <span class="px-2 py-0.5 rounded-full bg-purple-100 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 font-extrabold text-[10px]" x-text="totalInvCount + ' st'"></span>
                </button>

                <button type="button" @click="setShopTab('donaties')"
                        :class="shopTab === 'donaties' ? 'bg-gradient-to-r from-amber-500 to-amber-600 text-purple-950 font-black shadow-md' : 'hover:bg-amber-50 dark:hover:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-300/80 dark:border-amber-800/80'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition cursor-pointer">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-mug-hot text-amber-500 w-4 text-center"></i>
                        <span>Server Steunen</span>
                    </span>
                    <span class="text-[9px] px-1.5 py-0.5 rounded bg-amber-400 text-purple-950 font-black uppercase">VIP</span>
                </button>
            </div>

            <!-- Live Dossier & Wallet Telemetry Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs space-y-3">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block border-b border-slate-100 dark:border-slate-800 pb-2">
                    Betaalmiddelen &amp; Dossier
                </span>
                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Contant:</span>
                        <span class="font-extrabold text-emerald-600 dark:text-emerald-400 font-mono">€{{ number_format($character->cash, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Bankrekening:</span>
                        <span class="font-extrabold text-emerald-600 dark:text-emerald-400 font-mono">€{{ number_format($character->bank, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Sterren:</span>
                        <span class="font-extrabold text-amber-600 dark:text-amber-400">{{ $character->stars ?? 0 }} ⭐</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Koffers:</span>
                        <span class="font-extrabold text-purple-700 dark:text-purple-400">{{ $character->unopened_bags ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Charisma-Bonus:</span>
                        <span class="font-extrabold text-pink-600 dark:text-pink-400">-{{ $charismaDiscountPct }}% korting</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
