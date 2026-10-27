@extends('layouts.app')

@section('content')
@php
    $getItemData = function($item) {
        $slug = $item->slug ?? '';
        $nameLower = strtolower($item->name ?? '');

        // Category mapping
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

        // Icon mapping (100% verified FontAwesome 6 Free icons - no broken or missing icons!)
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

        // Tailored color badges
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
@endphp

<div class="space-y-6" x-data="{ 
    shopTab: '{{ session('shop_tab', request('tab', 'pakketten')) }}',
    bundleFilter: 'all',
    itemFilter: 'all',
    selectedBundleId: 'big_bucks',
    bundleQty: 1,
    recipientId: '',
    bundles: {{ json_encode($bundles) }},
    @php
        $ownedCountsMap = $inventory->pluck('quantity', 'item_id')->toArray();
        $bivakItem = $items->firstWhere('slug', 'bivakmuts-masker');
        if ($bivakItem) {
            $ownedCountsMap[$bivakItem->id] = (int) ($character->masks ?? 0);
        }
    @endphp
    ownedCounts: {{ json_encode($ownedCountsMap) }},
    totalInvCount: {{ (int) $inventory->sum('quantity') }},
    storageUsed: {{ (int) $character->getStorageUsed() }},
    storageCapacity: {{ (int) $character->getStorageCapacity() }},
    get storageRemaining() {
        return Math.max(0, this.storageCapacity - this.storageUsed);
    },
    itemBuying: {},
    itemToast: {},
    init() {
        if (!'{{ session('shop_tab') }}' && !'{{ request('tab') }}' && sessionStorage.getItem('tbs_shop_tab')) {
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
                        text: qty + 'x Gekocht! (Voorraad: ' + data.owned_count + 'x)'
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
                        text: data.message || 'Niet genoeg geld of aankoop mislukt.'
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

    <!-- Top Hero Banner -->
    <div class="rounded-2xl shadow-xs p-6 sm:p-8 text-slate-800 dark:text-slate-100 relative overflow-hidden bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 bg-purple-50 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 text-xs font-bold px-3 py-1 rounded-full border border-purple-200 dark:border-purple-800 mb-2">
                    <i class="fa-solid fa-store"></i> De Kliniek Kiosk & Winkel
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Kliniek Shop</h1>
                <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-1 max-w-xl leading-relaxed">
                    Scoor exclusieve bundels, kluiscodes, maskers en koffers met je verdiende Sterren of koop handige benodigdheden in de kantine met contante Euro's!
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs font-bold text-amber-700 dark:text-amber-400 flex items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-star text-amber-500"></i> {{ $character->stars ?? 0 }} Sterren
                </span>
                <span class="px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs font-bold text-purple-700 dark:text-purple-400 flex items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-mask text-purple-600"></i> {{ $character->masks ?? 0 }} Maskers
                </span>
            </div>
        </div>
    </div>

    <!-- MAIN TWO COLUMN SHOP LAYOUT -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left Column: Shop Sidebar Navigation -->
        <div class="lg:col-span-3 space-y-4">
            <div class="bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-xs p-2 space-y-1 text-xs font-bold text-slate-700 dark:text-slate-300">
                <button type="button" @click="setShopTab('pakketten')" 
                        :class="shopTab === 'pakketten' ? 'bg-purple-50 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60 text-slate-600 dark:text-slate-400 border border-transparent'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-lg transition cursor-pointer">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-basket-shopping text-purple-600 w-4 text-center"></i>
                        <span>Pakketten & Deals</span>
                    </span>
                    <i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i>
                </button>

                <button type="button" @click="setShopTab('sterren')" 
                        :class="shopTab === 'sterren' ? 'bg-purple-50 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60 text-slate-600 dark:text-slate-400 border border-transparent'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-lg transition cursor-pointer">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-gem text-amber-500 w-4 text-center"></i>
                        <span>Sterren Items</span>
                    </span>
                    <span class="text-[10px] text-amber-600 dark:text-amber-400 font-extrabold flex items-center gap-1">{{ $character->stars ?? 0 }} <i class="fa-solid fa-star text-amber-500 text-[10px]"></i></span>
                </button>

                <button type="button" @click="setShopTab('items')" 
                        :class="shopTab === 'items' ? 'bg-purple-50 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60 text-slate-600 dark:text-slate-400 border border-transparent'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-lg transition cursor-pointer">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-utensils text-emerald-600 w-4 text-center"></i>
                        <span>Kantine Items</span>
                    </span>
                    <span class="text-[10px] text-slate-400">Euro (€)</span>
                </button>

                <!-- Mijn Inventaris Tab -->
                <button type="button" @click="setShopTab('inventaris')" 
                        :class="shopTab === 'inventaris' ? 'bg-purple-50 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60 text-slate-600 dark:text-slate-400 border border-transparent'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-lg transition cursor-pointer">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-box-archive text-purple-600 w-4 text-center"></i>
                        <span>Mijn Inventaris</span>
                    </span>
                    <span class="px-2 py-0.5 rounded-full bg-purple-100 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 font-extrabold text-[10px]" x-text="totalInvCount + ' items'">
                        {{ $inventory->sum('quantity') }} items
                    </span>
                </button>
            </div>

            <!-- Character Wallet Badge Card -->
            <div class="bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs space-y-3">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-400 block border-b border-slate-100 dark:border-slate-800 pb-2">
                    Jouw Bezittingen
                </span>
                <div class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400 font-medium">Sterren:</span>
                        <span class="font-extrabold text-amber-600 dark:text-amber-400 flex items-center gap-1">{{ $character->stars ?? 0 }} <i class="fa-solid fa-star text-amber-500 text-[10px]"></i></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400 font-medium">Koffers:</span>
                        <span class="font-extrabold text-purple-700 dark:text-purple-400">{{ $character->unopened_bags ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400 font-medium">Maskers:</span>
                        <span class="font-extrabold text-indigo-700 dark:text-indigo-400">{{ $character->masks ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400 font-medium">Bank:</span>
                        <span class="font-extrabold text-emerald-600 dark:text-emerald-400">€{{ number_format($character->bank, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Content Area: Bundles & Showcase -->
        <div class="lg:col-span-9 space-y-6">

            <!-- TAB: PAKKETTEN & BUNDELS -->
            <div x-show="shopTab === 'pakketten'" class="space-y-6">

                <!-- Special Action Banner -->
                <div class="bg-gradient-to-r from-purple-50 via-pink-50 to-purple-50 dark:from-purple-950/40 dark:via-pink-950/20 dark:to-purple-950/40 border border-purple-200 dark:border-purple-800/60 rounded-xl p-4 text-center shadow-xs">
                    <span class="text-[10px] font-black uppercase tracking-widest text-purple-700 dark:text-purple-300 bg-purple-100 dark:bg-purple-950/80 border border-purple-200 dark:border-purple-800 px-3 py-0.5 rounded-full mb-1.5 inline-block">
                        SPECIALE ACTIE
                    </span>
                    <p class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-white mt-1">
                        Bij iedere aankoop vanaf 100 sterren ontvang je <strong class="text-purple-700 dark:text-purple-400">1 XXL koffer als bonus</strong>
                    </p>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Exclusief VIP en extra kluiscodes!</span>
                </div>

                <!-- Active Bundle Showcase Card -->
                <div class="bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-xs space-y-5"
                     x-data="{ currentBundle() { return bundles[selectedBundleId] || bundles['big_bucks']; } }">
                    
                    <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <h3 class="text-lg font-extrabold text-slate-900 dark:text-white" x-text="currentBundle().name"></h3>
                            <div class="text-sm font-extrabold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 px-3 py-1 rounded-full w-fit inline-flex items-center gap-1.5">
                                <span>Prijs: <span x-text="currentBundle().cost_stars"></span> Sterren</span>
                                <i class="fa-solid fa-star text-amber-500 text-xs"></i>
                            </div>
                        </div>

                        <!-- Bundle Contents List with Icons -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                            <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-100 dark:border-slate-700/60 text-xs text-slate-700 dark:text-slate-200 font-bold">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-coins"></i>
                                </div>
                                <span>€<strong x-text="new Intl.NumberFormat('nl-NL').format(currentBundle().cash)"></strong> Wit Bankgeld</span>
                            </div>
                            <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-100 dark:border-slate-700/60 text-xs text-slate-700 dark:text-slate-200 font-bold">
                                <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-950/70 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-key"></i>
                                </div>
                                <span><strong x-text="currentBundle().codes"></strong> Kluiscodes</span>
                            </div>
                            <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-100 dark:border-slate-700/60 text-xs text-slate-700 dark:text-slate-200 font-bold">
                                <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-briefcase"></i>
                                </div>
                                <span><strong x-text="currentBundle().koffers"></strong> Koffers</span>
                            </div>
                            <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-100 dark:border-slate-700/60 text-xs text-slate-700 dark:text-slate-200 font-bold">
                                <div class="w-8 h-8 rounded-lg bg-pink-100 dark:bg-pink-950/70 text-pink-600 dark:text-pink-400 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-mask"></i>
                                </div>
                                <span><strong x-text="currentBundle().masks"></strong> Maskers</span>
                            </div>
                        </div>
                    </div>

                    <!-- Buy / Gift Form -->
                    <form action="{{ route('shop.buy-bundle') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="bundle_id" :value="selectedBundleId">

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Aantal keer kopen:
                            </label>
                            <input type="number" name="quantity" min="1" max="10" x-model="bundleQty" required 
                                   class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 focus:border-purple-600 focus:ring-1 focus:ring-purple-600 rounded-lg px-3.5 py-2 text-xs font-bold text-slate-900 dark:text-white focus:outline-none">
                        </div>

                        <!-- Gifting Selector with Autocomplete Search Input -->
                        <div x-data="{
                            search: '',
                            selectedId: '',
                            selectedName: '',
                            openDropdown: false,
                            patients: [
                                { id: '', name: 'Mijn eigen account ({{ $character->name }})', level: {{ $character->level }}, respect: {{ $character->respect }}, isSelf: true },
                                @foreach($otherPatients as $p)
                                { id: '{{ $p->id }}', name: '{{ addslashes($p->name) }}', level: {{ $p->level }}, respect: {{ $p->respect }}, isSelf: false },
                                @endforeach
                            ],
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
                                Wil je dit product cadeau geven? Selecteer of typ ontvanger:
                            </label>
                            <input type="hidden" name="recipient_id" :value="selectedId">
                            <input type="hidden" name="recipient_name" :value="search">

                            <div class="relative flex items-center">
                                <div class="absolute left-3 top-1/2 -translate-y-1/2 flex items-center pointer-events-none text-purple-600 dark:text-purple-400 text-xs z-10">
                                    <i class="fa-solid fa-user-tag"></i>
                                </div>
                                <input type="text"
                                       x-model="search"
                                       @focus="openDropdown = true"
                                       @input="openDropdown = true; selectedId = ''"
                                       @click.outside="openDropdown = false"
                                       autocomplete="off"
                                       placeholder="Mijn eigen account ({{ $character->name }}) of typ naam..."
                                       class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 focus:border-purple-600 focus:ring-1 focus:ring-purple-600 rounded-lg py-2.5 text-xs font-bold text-slate-900 dark:text-white focus:outline-none transition tbs-search-input shadow-2xs"
                                       style="padding-left: 2.75rem !important; padding-right: 2.25rem !important;">
                                <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center pointer-events-none text-slate-400 text-xs z-10">
                                    <i class="fa-solid fa-chevron-down transition-transform duration-150" :class="openDropdown ? 'rotate-180' : ''"></i>
                                </div>
                            </div>

                            <!-- Supplemental Dropdown List -->
                            <div x-show="openDropdown" 
                                 x-transition
                                 class="absolute z-50 left-0 right-0 mt-1 max-h-52 overflow-y-auto bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
                                <template x-for="p in filtered" :key="p.name">
                                    <button type="button" 
                                            @click="select(p)"
                                            class="w-full text-left px-3.5 py-2.5 hover:bg-purple-50 dark:hover:bg-slate-700/60 flex items-center justify-between text-xs transition cursor-pointer group">
                                        <div class="flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black"
                                                  :class="p.isSelf ? 'bg-purple-100 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200'">
                                                <i :class="p.isSelf ? 'fa-solid fa-user' : 'fa-solid fa-user-ninja'"></i>
                                            </span>
                                            <div>
                                                <span class="font-extrabold text-slate-900 dark:text-white group-hover:text-purple-700 dark:group-hover:text-purple-400 block" x-text="p.name"></span>
                                                <span class="text-[10px] text-slate-400" x-text="'Level ' + p.level + ' • ' + p.respect + ' Respect'"></span>
                                            </div>
                                        </div>
                                        <span x-show="selectedName === p.name" class="text-purple-600 dark:text-purple-400 text-xs">
                                            <i class="fa-solid fa-check"></i>
                                        </span>
                                    </button>
                                </template>
                                <template x-if="filtered.length === 0">
                                    <div class="p-3 text-xs text-slate-400 text-center">
                                        Geen medepatiënt gevonden met '<span x-text="search"></span>'
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" 
                                :disabled="({{ $character->stars ?? 0 }}) < (currentBundle().cost_stars * bundleQty)"
                                class="w-full py-3 px-6 rounded-xl bg-purple-600 hover:bg-purple-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-extrabold text-xs uppercase tracking-wider transition shadow-xs cursor-pointer flex items-center justify-center gap-2">
                            <i class="fa-solid fa-lock text-xs"></i>
                            <span>Betalen (<span x-text="currentBundle().cost_stars * bundleQty"></span> Sterren)</span>
                        </button>
                    </form>
                </div>

                <!-- Category Selector -->
                <div class="flex items-center justify-center gap-2 bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 p-1.5 rounded-xl">
                    <button type="button" @click="bundleFilter = 'all'" 
                            :class="bundleFilter === 'all' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                            class="px-5 py-2 rounded-lg text-xs font-extrabold uppercase transition cursor-pointer">
                        Alle Deals
                    </button>
                    <button type="button" @click="bundleFilter = 'smokkel'" 
                            :class="bundleFilter === 'smokkel' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                            class="px-5 py-2 rounded-lg text-xs font-extrabold uppercase transition cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-box text-xs text-indigo-500"></i> Smokkel
                    </button>
                    <button type="button" @click="bundleFilter = 'kluisbuit'" 
                            :class="bundleFilter === 'kluisbuit' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                            class="px-5 py-2 rounded-lg text-xs font-extrabold uppercase transition cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-vault text-xs text-amber-500"></i> Kluisbuit
                    </button>
                    <button type="button" @click="bundleFilter = 'syndicaat'" 
                            :class="bundleFilter === 'syndicaat' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                            class="px-5 py-2 rounded-lg text-xs font-extrabold uppercase transition cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-crown text-xs text-purple-600"></i> Syndicaat
                    </button>
                </div>

                <!-- Deals List Table -->
                <div class="bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-xs divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($bundles as $bKey => $b)
                    <div x-show="bundleFilter === 'all' || bundleFilter === '{{ $b['category'] }}'"
                         @click="selectedBundleId = '{{ $bKey }}'"
                         :class="selectedBundleId === '{{ $bKey }}' ? 'bg-purple-50/70 dark:bg-purple-950/40 border-l-4 border-purple-600' : 'hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                         class="p-4 flex items-center justify-between gap-4 transition cursor-pointer">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 flex items-center justify-center text-lg shrink-0">
                                <i class="fa-solid fa-box-open"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-extrabold text-slate-900 dark:text-white">{{ $b['name'] }}</h4>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">{{ $b['codes'] }} codes &bull; {{ $b['koffers'] }} koffers &bull; {{ $b['masks'] }} maskers</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-extrabold text-amber-600 dark:text-amber-400 inline-flex items-center gap-1">{{ number_format($b['cost_stars']) }} Sterren <i class="fa-solid fa-star text-amber-500 text-[10px]"></i></span>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Bottom Disclaimer -->
                <p class="text-xs text-slate-400 text-center leading-relaxed">
                    Personen onder de 18 jaar dienen toestemming te hebben van hun ouders of voogd. De spelregels en voorwaarden zijn van toepassing op alle aankopen.
                </p>
            </div>

            <!-- TAB: REGULIERE ITEMS (CONTANT GELD / EURO) -->
            <div x-show="shopTab === 'items'" class="space-y-6">
                <div class="bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 flex items-center justify-center text-sm shadow-2xs">
                                <i class="fa-solid fa-utensils"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">Kantine & Uitrusting</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Koop snacks, drankjes, medische spullen en smokkelwaar met je contant geld.</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <div class="text-xs font-bold px-3 py-1.5 rounded-lg border flex items-center gap-1.5 transition"
                                 :class="storageRemaining <= 0 ? 'bg-rose-50 dark:bg-rose-950/60 border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300' : 'bg-slate-50 dark:bg-slate-800/80 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300'">
                                <i class="fa-solid fa-warehouse text-purple-600 dark:text-purple-400"></i>
                                <span>Opslagruimte: <span class="font-mono font-black" x-text="storageUsed + '/' + storageCapacity"></span></span>
                                <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold font-mono"
                                      :class="storageRemaining <= 0 ? 'bg-rose-600 text-white' : 'bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300'"
                                      x-text="storageRemaining > 0 ? storageRemaining + ' vrij' : 'VOL'"></span>
                            </div>
                            <button type="button" @click="setShopTab('inventaris')" class="text-xs font-bold text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 flex items-center gap-1.5 cursor-pointer bg-purple-50 dark:bg-purple-950/60 hover:bg-purple-100 dark:hover:bg-purple-900/60 px-3 py-1.5 rounded-lg transition w-fit">
                                <i class="fa-solid fa-box-archive"></i>
                                <span x-text="'Mijn inventaris (' + totalInvCount + ')'">Mijn inventaris ({{ $inventory->sum('quantity') }})</span>
                            </button>
                        </div>
                    </div>

                    <!-- Category Filter Tabs -->
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

                    <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar border-b border-slate-100">
                        <button type="button"
                                @click="itemFilter = 'all'"
                                :class="itemFilter === 'all' 
                                    ? 'bg-purple-600 text-white shadow-xs font-extrabold' 
                                    : 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold'"
                                class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-2 shrink-0">
                            <i class="fa-solid fa-border-all text-[11px]"></i>
                            <span>Alle Items</span>
                            <span class="text-[10px] px-1.5 py-0.2 rounded-full font-mono" 
                                  :class="itemFilter === 'all' ? 'bg-purple-700 text-purple-100' : 'bg-slate-200 text-slate-600'">
                                {{ count($items) }}
                            </span>
                        </button>
                        <button type="button"
                                @click="itemFilter = 'eten'"
                                :class="itemFilter === 'eten' 
                                    ? 'bg-purple-600 text-white shadow-xs font-extrabold' 
                                    : 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold'"
                                class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-2 shrink-0">
                            <i class="fa-solid fa-burger text-[11px]" :class="itemFilter === 'eten' ? 'text-white' : 'text-amber-500'"></i>
                            <span>Eten & Snacks</span>
                            <span class="text-[10px] px-1.5 py-0.2 rounded-full font-mono" 
                                  :class="itemFilter === 'eten' ? 'bg-purple-700 text-purple-100' : 'bg-slate-200 text-slate-600'">
                                {{ $categoryCounts['eten'] ?? 0 }}
                            </span>
                        </button>
                        <button type="button"
                                @click="itemFilter = 'drank'"
                                :class="itemFilter === 'drank' 
                                    ? 'bg-purple-600 text-white shadow-xs font-extrabold' 
                                    : 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold'"
                                class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-2 shrink-0">
                            <i class="fa-solid fa-bolt text-[11px]" :class="itemFilter === 'drank' ? 'text-white' : 'text-cyan-500'"></i>
                            <span>Drank & Energie</span>
                            <span class="text-[10px] px-1.5 py-0.2 rounded-full font-mono" 
                                  :class="itemFilter === 'drank' ? 'bg-purple-700 text-purple-100' : 'bg-slate-200 text-slate-600'">
                                {{ $categoryCounts['drank'] ?? 0 }}
                            </span>
                        </button>
                        <button type="button"
                                @click="itemFilter = 'medisch'"
                                :class="itemFilter === 'medisch' 
                                    ? 'bg-purple-600 text-white shadow-xs font-extrabold' 
                                    : 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold'"
                                class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-2 shrink-0">
                            <i class="fa-solid fa-kit-medical text-[11px]" :class="itemFilter === 'medisch' ? 'text-white' : 'text-rose-500'"></i>
                            <span>Medisch & Herstel</span>
                            <span class="text-[10px] px-1.5 py-0.2 rounded-full font-mono" 
                                  :class="itemFilter === 'medisch' ? 'bg-purple-700 text-purple-100' : 'bg-slate-200 text-slate-600'">
                                {{ $categoryCounts['medisch'] ?? 0 }}
                            </span>
                        </button>
                        <button type="button"
                                @click="itemFilter = 'kracht'"
                                :class="itemFilter === 'kracht' 
                                    ? 'bg-purple-600 text-white shadow-xs font-extrabold' 
                                    : 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold'"
                                class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-2 shrink-0">
                            <i class="fa-solid fa-dumbbell text-[11px]" :class="itemFilter === 'kracht' ? 'text-white' : 'text-indigo-500'"></i>
                            <span>Kracht & Uitrusting</span>
                            <span class="text-[10px] px-1.5 py-0.2 rounded-full font-mono" 
                                  :class="itemFilter === 'kracht' ? 'bg-purple-700 text-purple-100' : 'bg-slate-200 text-slate-600'">
                                {{ $categoryCounts['kracht'] ?? 0 }}
                            </span>
                        </button>
                        <button type="button"
                                @click="itemFilter = 'smokkel'"
                                :class="itemFilter === 'smokkel' 
                                    ? 'bg-purple-600 text-white shadow-xs font-extrabold' 
                                    : 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold'"
                                class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-2 shrink-0">
                            <i class="fa-solid fa-mask text-[11px]" :class="itemFilter === 'smokkel' ? 'text-white' : 'text-slate-600'"></i>
                            <span>Smokkel & Sleutels</span>
                            <span class="text-[10px] px-1.5 py-0.2 rounded-full font-mono" 
                                  :class="itemFilter === 'smokkel' ? 'bg-purple-700 text-purple-100' : 'bg-slate-200 text-slate-600'">
                                {{ $categoryCounts['smokkel'] ?? 0 }}
                            </span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($items as $item)
                        @php
                            $ownedCount = $item->slug === 'bivakmuts-masker'
                                ? (int) ($character->masks ?? 0)
                                : ($inventory->firstWhere('item_id', $item->id)?->quantity ?? 0);
                            $itemData = $getItemData($item);
                        @endphp
                        <div x-show="itemFilter === 'all' || itemFilter === '{{ $itemData['cat'] }}'"
                             class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 hover:border-purple-300 dark:hover:border-purple-500 rounded-xl p-4 flex flex-col justify-between gap-3 transition shadow-xs">
                            <div class="space-y-2">
                                <div class="flex items-start justify-between gap-2 min-h-[40px]">
                                    <div class="w-10 h-10 rounded-xl {{ $itemData['badgeClass'] }} flex items-center justify-center text-base shrink-0 shadow-2xs">
                                        <i class="{{ $itemData['icon'] }}"></i>
                                    </div>
                                    <div class="h-6 flex items-center">
                                        <template x-if="ownedCounts[{{ $item->id }}] > 0">
                                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300 text-[10px] font-black shrink-0 transition"
                                                  x-text="'{{ $item->category === 'equipment' ? '1x in bezit (Uniek)' : '' }}' || (ownedCounts[{{ $item->id }}] + 'x in bezit')">
                                                {{ $item->category === 'equipment' ? '1x in bezit (Uniek)' : $ownedCount.'x in bezit' }}
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="text-xs font-extrabold text-slate-900 dark:text-white">{{ $item->name }}</h4>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2 mt-0.5">{{ $item->description ?: 'Heerlijke snack uit de kliniekkantine.' }}</p>
                                </div>

                                <!-- Effect Pills -->
                                <div class="flex flex-wrap gap-1.5 pt-1">
                                    @if($item->slug === 'adrenaline-injectie')
                                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] font-bold inline-flex items-center gap-1">
                                        <span>100% Volle Energie</span> <i class="fa-solid fa-bolt text-amber-500 text-[9px]"></i>
                                    </span>
                                    @elseif($item->energy_restore > 0)
                                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] font-bold inline-flex items-center gap-1">
                                        <span>+{{ $item->energy_restore }} Energie</span> <i class="fa-solid fa-bolt text-amber-500 text-[9px]"></i>
                                    </span>
                                    @endif
                                    @if($item->slug === 'ehbo-noodverbanddoos')
                                    <span class="px-2 py-0.5 rounded-md bg-rose-50 border border-rose-200 text-rose-700 text-[10px] font-bold inline-flex items-center gap-1">
                                        <span>100% Volle HP</span> <i class="fa-solid fa-heart text-rose-500 text-[9px]"></i>
                                    </span>
                                    @elseif($item->health_restore > 0)
                                    <span class="px-2 py-0.5 rounded-md bg-rose-50 border border-rose-200 text-rose-700 text-[10px] font-bold inline-flex items-center gap-1">
                                        <span>+{{ $item->health_restore }} HP</span> <i class="fa-solid fa-heart text-rose-500 text-[9px]"></i>
                                    </span>
                                    @endif
                                    @if($item->slug === 'bivakmuts-masker')
                                    <span class="px-2 py-0.5 rounded-md bg-indigo-50 border border-indigo-200 text-indigo-700 text-[10px] font-bold inline-flex items-center gap-1">
                                        <span>+1 Masker</span> <i class="fa-solid fa-masks-theater text-indigo-500 text-[9px]"></i>
                                    </span>
                                    @endif
                                    @if($item->slug === 'smokkelpakketje')
                                    <span class="px-2 py-0.5 rounded-md bg-purple-50 border border-purple-200 text-purple-700 text-[10px] font-bold inline-flex items-center gap-1">
                                        <span>+1 Pakket/Koffer</span> <i class="fa-solid fa-box text-purple-500 text-[9px]"></i>
                                    </span>
                                    @endif
                                    @if($item->slug === 'ijzerzaagje')
                                    <span class="px-2 py-0.5 rounded-md bg-amber-50 border border-amber-200 text-amber-700 text-[10px] font-bold inline-flex items-center gap-1">
                                        <span>-30m Celstraf</span> <i class="fa-solid fa-hourglass-half text-amber-500 text-[9px]"></i>
                                    </span>
                                    @endif
                                    @if($item->strength_bonus > 0)
                                    <span class="px-2 py-0.5 rounded-md bg-purple-50 border border-purple-200 text-purple-700 text-[10px] font-bold inline-flex items-center gap-1">
                                        <span>+{{ $item->strength_bonus }} Kracht</span> <i class="fa-solid fa-dumbbell text-purple-500 text-[9px]"></i>
                                    </span>
                                    @endif
                                    @if($item->defense_bonus > 0)
                                    <span class="px-2 py-0.5 rounded-md bg-blue-50 border border-blue-200 text-blue-700 text-[10px] font-bold inline-flex items-center gap-1">
                                        <span>+{{ $item->defense_bonus }} Verdediging</span> <i class="fa-solid fa-shield text-blue-500 text-[9px]"></i>
                                    </span>
                                    @endif
                                    @if($item->stamina_bonus > 0)
                                    <span class="px-2 py-0.5 rounded-md bg-amber-50 border border-amber-200 text-amber-700 text-[10px] font-bold inline-flex items-center gap-1">
                                        <span>+{{ $item->stamina_bonus }} Conditie</span> <i class="fa-solid fa-person-running text-amber-500 text-[9px]"></i>
                                    </span>
                                    @endif
                                    @if($item->intelligence_bonus > 0)
                                    <span class="px-2 py-0.5 rounded-md bg-indigo-50 border border-indigo-200 text-indigo-700 text-[10px] font-bold inline-flex items-center gap-1">
                                        <span>+{{ $item->intelligence_bonus }} Kennis</span> <i class="fa-solid fa-brain text-indigo-500 text-[9px]"></i>
                                    </span>
                                    @endif
                                    @if(($item->charisma_bonus ?? 0) > 0)
                                    <span class="px-2 py-0.5 rounded-md bg-amber-50 border border-amber-200 text-amber-700 text-[10px] font-bold inline-flex items-center gap-1" title="Biedt extra winkelkorting en sociale voordelen">
                                        <span>+{{ $item->charisma_bonus }} Charisma</span> <i class="fa-solid fa-wand-magic-sparkles text-amber-500 text-[9px]"></i>
                                    </span>
                                    @endif
                                    @if(($item->discipline_bonus ?? 0) > 0)
                                    <span class="px-2 py-0.5 rounded-md bg-cyan-50 border border-cyan-200 text-cyan-700 text-[10px] font-bold inline-flex items-center gap-1">
                                        <span>+{{ $item->discipline_bonus }} Discipline</span> <i class="fa-solid fa-shield-halved text-cyan-500 text-[9px]"></i>
                                    </span>
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
                                $statRemainingMinutes = $isStatOnCooldown ? (int) now()->diffInMinutes($statCooldownUntil) : 0;
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
                            <div class="pt-2 border-t border-slate-200/70 dark:border-slate-700/60 space-y-2.5"
                                 x-data="{
                                     buyQty: 1,
                                     isEquipment: {{ $isEquipment ? 'true' : 'false' }},
                                     isRestrictedStat: {{ $isRestrictedStat ? 'true' : 'false' }},
                                     isOnCooldown: {{ $isStatOnCooldown ? 'true' : 'false' }},
                                     isDirectDelivery: {{ $isDirectDelivery ? 'true' : 'false' }},
                                     unitPrice: {{ $item->price_kc }},
                                     discountPct: {{ min(15, (int) floor($character->getEffectiveCharisma() / 10)) }},
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
                                    <span class="text-slate-500 dark:text-slate-400 font-medium">Prijs per stuk:</span>
                                    <div class="text-right">
                                        <span class="font-extrabold text-emerald-700 dark:text-emerald-400 font-mono">
                                            €{{ number_format($item->price_kc, 0, ',', '.') }}
                                        </span>
                                        <template x-if="!isEquipment && !isRestrictedStat && buyQty > 1">
                                            <span class="text-[10px] text-slate-400 dark:text-slate-400 block font-mono" x-text="'Totaal: €' + new Intl.NumberFormat('nl-NL').format(totalCost)"></span>
                                        </template>
                                    </div>
                                </div>

                                <!-- Aantal Knoppen (voor consumables/snacks) OF Unieke Uitrusting Badge OF 6-Uurs Cooldown Badge -->
                                <template x-if="isEquipment">
                                    <div class="flex items-center justify-between py-1.5 px-3 rounded-xl bg-slate-100/80 dark:bg-slate-800/80 border border-slate-200/70 dark:border-slate-700/60 text-[11px]">
                                        <span class="text-slate-600 dark:text-slate-300 font-bold flex items-center gap-1.5">
                                            <i class="fa-solid fa-shield-halved text-purple-600 dark:text-purple-400"></i>
                                            <span>Unieke Uitrusting</span>
                                        </span>
                                        <template x-if="isOwned">
                                            <span class="text-emerald-600 dark:text-emerald-400 font-black flex items-center gap-1">
                                                <i class="fa-solid fa-circle-check"></i>
                                                <span>Reeds in bezit</span>
                                            </span>
                                        </template>
                                        <template x-if="!isOwned">
                                            <span class="text-slate-400 dark:text-slate-500 font-bold font-mono">1x max</span>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="isRestrictedStat">
                                    <div class="flex items-center justify-between py-1.5 px-3 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 text-[11px]">
                                        <span class="text-amber-800 dark:text-amber-300 font-extrabold flex items-center gap-1.5">
                                            <i class="fa-solid fa-clock text-amber-500"></i>
                                            <span>Max. 1x per 6 uur</span>
                                        </span>
                                        <span class="text-slate-500 dark:text-slate-400 font-bold font-mono">1x per beurt</span>
                                    </div>
                                </template>
                                <template x-if="!isEquipment && !isRestrictedStat">
                                    <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800/80 p-1 rounded-xl border border-slate-200/70 dark:border-slate-700/60">
                                        <button type="button" @click="setQty(1)" :class="buyQty === 1 ? 'bg-white dark:bg-slate-700 text-purple-700 dark:text-purple-300 shadow-2xs font-black' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 font-bold'" class="flex-1 py-1 rounded-lg text-[11px] transition cursor-pointer">1x</button>
                                        <button type="button" @click="setQty(5)" :class="buyQty === 5 ? 'bg-white dark:bg-slate-700 text-purple-700 dark:text-purple-300 shadow-2xs font-black' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 font-bold'" class="flex-1 py-1 rounded-lg text-[11px] transition cursor-pointer">5x</button>
                                        <button type="button" @click="setQty(10)" :class="buyQty === 10 ? 'bg-white dark:bg-slate-700 text-purple-700 dark:text-purple-300 shadow-2xs font-black' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 font-bold'" class="flex-1 py-1 rounded-lg text-[11px] transition cursor-pointer">10x</button>
                                        <button type="button" @click="buyQty = Math.max(1, maxBuyable)" :class="buyQty > 10 || (maxBuyable > 0 && buyQty === maxBuyable) ? 'bg-white dark:bg-slate-700 text-purple-700 dark:text-purple-300 shadow-2xs font-black' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 font-bold'" class="flex-1 py-1 rounded-lg text-[11px] transition cursor-pointer" :title="'Max berekend op je saldo (' + maxBuyable + 'x)'">
                                            <span x-text="maxBuyable > 0 ? 'Max (' + maxBuyable + ')' : 'Max'">Max</span>
                                        </button>
                                    </div>
                                </template>

                                <!-- Realtime Status Indicatie -->
                                @if($isStatOnCooldown)
                                <div class="text-[11px] text-amber-700 dark:text-amber-300 font-bold text-center flex items-center justify-center gap-1.5 py-1 px-2 bg-amber-50 dark:bg-amber-950/80 rounded-lg border border-amber-300 dark:border-amber-800">
                                    <i class="fa-solid fa-hourglass-half text-amber-500 text-[10px]"></i>
                                    <span>Cooldown actief: nog {{ $statRemainingText }}</span>
                                </div>
                                @endif

                                <template x-if="!isDirectDelivery && storageRemaining <= 0">
                                    <div class="text-[10px] text-rose-500 dark:text-rose-400 font-bold text-center flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-triangle-exclamation text-[9px]"></i>
                                        <span>Opslagruimte vol (0 vrij in Depot)</span>
                                    </div>
                                </template>
                                <template x-if="isDirectDelivery">
                                    <div class="text-[10px] text-purple-600 dark:text-purple-400 font-bold text-center">
                                        @if($item->slug === 'bivakmuts-masker')
                                            <span>🎭 Voegt toe aan Maskers (geen opslag nodig)</span>
                                        @elseif($item->slug === 'smokkelpakketje')
                                            <span>📦 Voegt toe aan Koffers & Buit (geen opslag nodig)</span>
                                        @elseif($isRestrictedStat)
                                            <span>💪 Permanente Stat Boost (max 1x per 6u)</span>
                                        @else
                                            <span>⚡ Directe Stat Boost (geen opslag nodig)</span>
                                        @endif
                                    </div>
                                </template>
                                <template x-if="!isDirectDelivery && !isEquipment && storageRemaining > 0 && maxByStorage < maxByMoney && maxByStorage < 100">
                                    <div class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold text-center" x-text="'Max ' + maxByStorage + 'x passend in vrije voorraad (' + storageRemaining + ' vrij)'"></div>
                                </template>
                                <template x-if="(isDirectDelivery || storageRemaining > 0) && maxByMoney < 1 && !isOwned">
                                    <div class="text-[10px] text-rose-500 dark:text-rose-400 font-semibold text-center">Niet genoeg saldo voor dit item</div>
                                </template>

                                <form action="{{ route('shop.buy', $item->id) }}" method="POST" data-turbo="false" @submit.prevent="buyItemViaAjax($event, {{ $item->id }}, (isEquipment || isRestrictedStat) ? 1 : buyQty)">
                                    @csrf
                                    <input type="hidden" name="quantity" :value="(isEquipment || isRestrictedStat) ? 1 : buyQty">
                                    
                                    <template x-if="isOwned">
                                        <div class="w-full py-2.5 rounded-xl bg-slate-200/80 dark:bg-slate-800/80 text-emerald-700 dark:text-emerald-400 font-extrabold text-xs flex items-center justify-center gap-1.5 border border-emerald-500/30 dark:border-emerald-500/20 shadow-2xs select-none">
                                            <i class="fa-solid fa-circle-check text-xs"></i>
                                            <span>Al in bezit (Uniek)</span>
                                        </div>
                                    </template>
                                    <template x-if="!isOwned">
                                        <button type="submit" 
                                                :disabled="itemBuying[{{ $item->id }}] || (!isDirectDelivery && storageRemaining <= 0) || maxByMoney < 1 || isOnCooldown"
                                                :class="(!isDirectDelivery && storageRemaining <= 0) || maxByMoney < 1 || isOnCooldown ? 'opacity-60 cursor-not-allowed bg-slate-500' : 'bg-purple-600 hover:bg-purple-700 active:scale-95 cursor-pointer'"
                                                class="w-full py-2.5 rounded-xl disabled:opacity-50 text-white font-extrabold text-xs transition flex items-center justify-center gap-1.5 shadow-sm">
                                            <template x-if="itemBuying[{{ $item->id }}]">
                                                <span class="inline-flex items-center gap-1.5 text-purple-200">
                                                    <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                                                    <span>Inpakken...</span>
                                                </span>
                                            </template>
                                            <template x-if="!itemBuying[{{ $item->id }}] && (!itemToast[{{ $item->id }}] || itemToast[{{ $item->id }}].type !== 'success')">
                                                <span class="inline-flex items-center gap-1.5">
                                                    <template x-if="isOnCooldown">
                                                        <span><i class="fa-solid fa-lock text-[11px] mr-1"></i> Cooldown (nog {{ $statRemainingText }})</span>
                                                    </template>
                                                    <template x-if="!isOnCooldown && !isDirectDelivery && storageRemaining <= 0">
                                                        <span>Opslag Vol</span>
                                                    </template>
                                                    <template x-if="!isOnCooldown && (isDirectDelivery || storageRemaining > 0) && maxByMoney < 1">
                                                        <span>Onvoldoende Saldo</span>
                                                    </template>
                                                    <template x-if="!isOnCooldown && (isDirectDelivery || storageRemaining > 0) && maxByMoney >= 1">
                                                        <span class="inline-flex items-center gap-1.5">
                                                            <i class="fa-solid fa-cart-shopping text-xs"></i>
                                                            <span x-text="isEquipment ? 'Uitrusting Kopen' : (buyQty > 1 ? 'Koop ' + buyQty + 'x direct' : 'Kopen')"></span>
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
                                    <!-- Inline error toast if not enough cash without shifting layout -->
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

            <!-- TAB: MIJN INVENTARIS (NEW & BEAUTIFUL) -->
            <div x-show="shopTab === 'inventaris'" class="space-y-6">
                <!-- Status Bar with Live Energy & Health -->
                <div class="bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-box-archive text-purple-600 dark:text-purple-400"></i>
                                <span>Mijn Rugzak & Inventaris</span>
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Hier bewaar je al je gekochte kantinesnacks, maaltijden en benodigdheden.</p>
                        </div>
                        <button type="button" @click="setShopTab('items')" class="px-3.5 py-1.5 rounded-lg bg-purple-50 dark:bg-purple-950/60 hover:bg-purple-100 dark:hover:bg-purple-900/60 text-purple-700 dark:text-purple-300 font-bold text-xs transition cursor-pointer flex items-center gap-1.5 w-fit">
                            <i class="fa-solid fa-plus"></i>
                            <span>Snacks Kopen in Kantine</span>
                        </button>
                    </div>

                    <!-- Energy and Health Progress -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4">
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700/80 space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                                    <i class="fa-solid fa-bolt text-amber-500"></i> Energie
                                </span>
                                <span class="text-slate-900 dark:text-white font-mono">{{ $character->energy }} / {{ $character->max_energy }}</span>
                            </div>
                            <div class="w-full h-2.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-amber-400 to-emerald-500 rounded-full transition-all duration-300" 
                                     style="width: {{ min(100, round(($character->energy / max(1, $character->max_energy)) * 100)) }}%"></div>
                            </div>
                        </div>

                        <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700/80 space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                                    <i class="fa-solid fa-heart-pulse text-rose-500"></i> Gezondheid (HP)
                                </span>
                                <span class="text-slate-900 dark:text-white font-mono">{{ $character->health }} / {{ $character->max_health }}</span>
                            </div>
                            <div class="w-full h-2.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-rose-500 to-pink-500 rounded-full transition-all duration-300" 
                                     style="width: {{ min(100, round(($character->health / max(1, $character->max_health)) * 100)) }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Maskers & Buit Stats Banner in Inventaris -->
                    <div class="mt-4 p-3 bg-purple-50/70 dark:bg-purple-950/40 rounded-xl border border-purple-200/80 dark:border-purple-800/60 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-purple-600 text-white flex items-center justify-center text-sm shadow-2xs">
                                <i class="fa-solid fa-mask"></i>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-800 dark:text-white flex items-center gap-1.5">
                                    Zakkenroller Maskers: <strong class="text-purple-600 dark:text-purple-400">{{ $character->masks ?? 0 }} Masker(s)</strong>
                                </span>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 block">Klaar voor gebruik bij Zakkenrollen in de gangen en Bende Heists.</span>
                            </div>
                        </div>
                        <a href="{{ route('pickpocket.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-black text-xs transition shadow-2xs shrink-0 flex items-center gap-1">
                            <span>Naar Zakkenrollen</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>

                    @php
                        $foodAndRecovery = $inventory->filter(function($inv) {
                            return ($inv->quantity > 0) && ($inv->item && $inv->item->category !== 'equipment') && (
                                $inv->item->energy_restore > 0 || 
                                $inv->item->health_restore > 0 || 
                                in_array($inv->item->slug, ['sigaretten', 'ritalin', 'telefoon', 'adrenaline-injectie', 'ehbo-noodverbanddoos'])
                            );
                        });
                        $needsRecovery = ($character->energy < $character->max_energy) || ($character->health < $character->max_health);
                    @endphp

                    <!--  SNEL ETEN & HERSTELLEN BALK (1-KLIK) -->
                    <div class="mt-4 p-4 rounded-xl border {{ $needsRecovery ? 'bg-gradient-to-r from-amber-50/70 via-emerald-50/50 to-white dark:from-emerald-950/40 dark:via-slate-900 dark:to-slate-900 border-emerald-300/80 dark:border-emerald-800/60' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700' }}">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200/70 dark:border-slate-800">
                            <div>
                                <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wide flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs shadow-2xs">
                                        <i class="fa-solid fa-utensils"></i>
                                    </span>
                                    <span>Snel Eten & Herstellen</span>
                                    @if(! $needsRecovery)
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 text-[10px] font-bold">100% Volledig Hersteld!</span>
                                    @endif
                                </h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                    Herstel je Energie en Gezondheid direct in 1 klik zonder eindeloos los te klikken.
                                </p>
                            </div>

                            @if($foodAndRecovery->isEmpty())
                                <button type="button" @click="setShopTab('items')" class="px-3.5 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs transition cursor-pointer flex items-center gap-1.5 shadow-xs w-fit">
                                    <i class="fa-solid fa-store"></i>
                                    <span>Naar Kantine voor Eten</span>
                                </button>
                            @endif
                        </div>

                        @if($foodAndRecovery->isNotEmpty())
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-3">
                                @foreach($foodAndRecovery as $quickInv)
                                @php
                                    $qItemData = $getItemData($quickInv->item);
                                @endphp
                                <div class="bg-white dark:bg-slate-900/90 p-3 rounded-lg border border-slate-200 dark:border-slate-800 shadow-2xs flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-9 h-9 rounded-lg {{ $qItemData['badgeClass'] }} flex items-center justify-center text-sm shrink-0 shadow-2xs">
                                            <i class="{{ $qItemData['icon'] }}"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-xs font-black text-slate-900 dark:text-white truncate flex items-center gap-1.5">
                                                <span>{{ $quickInv->item->name }}</span>
                                                <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold border border-slate-200/50 dark:border-slate-700">{{ $quickInv->quantity }}x</span>
                                            </div>
                                            <div class="text-[10px] text-slate-500 dark:text-slate-400 flex items-center gap-2 pt-0.5">
                                                @if($quickInv->item->energy_restore > 0)
                                                    <span class="text-emerald-700 dark:text-emerald-400 font-bold">+{{ $quickInv->item->energy_restore }}</span>
                                                @endif
                                                @if($quickInv->item->health_restore > 0)
                                                    <span class="text-rose-700 dark:text-rose-400 font-bold">+{{ $quickInv->item->health_restore }}️</span>
                                                @endif
                                                @if(in_array($quickInv->item->slug, ['sigaretten', 'ritalin', 'telefoon']))
                                                    <span class="text-purple-700 dark:text-purple-400 font-bold">Herstelboost</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <!-- 1x Eten Knop -->
                                        <form action="{{ route('shop.consume', $quickInv->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" 
                                                    title="1 stuk consumeren"
                                                    class="px-2.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:hover:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 font-black text-xs transition cursor-pointer flex items-center gap-1 active:scale-95">
                                                <span>1x</span>
                                            </button>
                                        </form>

                                        <!-- Eet tot 100% Vol Knop -->
                                        @if($needsRecovery)
                                        <form action="{{ route('shop.consume', $quickInv->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="fill_max" value="1">
                                            <button type="submit" 
                                                    title="Eet automatisch het benodigde aantal om 100% vol te raken"
                                                    class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs transition cursor-pointer flex items-center gap-1.5 shadow-2xs active:scale-95">
                                                <i class="fa-solid fa-bolt text-[11px]"></i>
                                                <span>Eet tot Vol</span>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="pt-3 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between gap-3">
                                <span class="flex items-center gap-2">
                                    <i class="fa-solid fa-circle-info text-amber-500"></i>
                                    Je hebt geen eten of drinken in je rugzak. Koop snacks in de kantine om snel te herstellen na gevechten of misdaden!
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Items Grid or Empty State -->
                <div class="bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-xs">
                    @if($inventory->where('quantity', '>', 0)->isEmpty())
                        <div class="text-center py-12 space-y-3">
                            <div class="w-16 h-16 mx-auto rounded-2xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-2xl border border-purple-100 dark:border-purple-800 shadow-xs">
                                <i class="fa-solid fa-box-open"></i>
                            </div>
                            <h4 class="text-sm font-extrabold text-slate-800 dark:text-white">Je inventaris is momenteel leeg</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                                Je hebt nog geen items of snacks in je rugzak. Bezoek de kantine om lekkere frikandelbroodjes of drankjes te kopen!
                            </p>
                            <button type="button" @click="setShopTab('items')" class="mt-2 px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs transition cursor-pointer inline-flex items-center gap-2 shadow-xs">
                                <i class="fa-solid fa-store"></i>
                                <span>Kantine Bekijken</span>
                            </button>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($inventory->where('quantity', '>', 0) as $inv)
                            @php
                                $invItemData = $getItemData($inv->item);
                            @endphp
                            <div class="bg-gradient-to-b from-white to-slate-50 dark:from-slate-800/80 dark:to-slate-900/90 border border-slate-200 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-600/70 rounded-xl p-4 flex flex-col justify-between gap-3 shadow-xs transition group">
                                <div class="space-y-2.5">
                                    <div class="flex items-center justify-between">
                                        <div class="w-12 h-12 rounded-xl {{ $invItemData['badgeClass'] }} flex items-center justify-center text-xl shrink-0 group-hover:scale-105 transition-transform shadow-xs">
                                            <i class="{{ $invItemData['icon'] }}"></i>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full bg-purple-600 text-white font-black text-xs font-mono shadow-xs">
                                            {{ $inv->quantity }}x
                                        </span>
                                    </div>

                                    <div>
                                        <h4 class="text-sm font-black text-slate-900 dark:text-white">{{ $inv->item->name }}</h4>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">{{ $inv->item->description ?: 'Eet dit item om je energie of gezondheid te herstellen.' }}</p>
                                    </div>

                                    <!-- Effects Pills -->
                                    <div class="flex flex-wrap gap-1.5 pt-1">
                                        @if($inv->item->slug === 'adrenaline-injectie')
                                        <span class="px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300 border border-emerald-200/50 dark:border-emerald-800/60 text-[10px] font-black flex items-center gap-1">
                                            <i class="fa-solid fa-bolt text-emerald-600 dark:text-emerald-400"></i> 100% Volle Energie
                                        </span>
                                        @elseif($inv->item->energy_restore > 0)
                                        <span class="px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300 border border-emerald-200/50 dark:border-emerald-800/60 text-[10px] font-black flex items-center gap-1">
                                            <i class="fa-solid fa-bolt text-emerald-600 dark:text-emerald-400"></i> +{{ $inv->item->energy_restore }} Energie
                                        </span>
                                        @endif
                                        @if($inv->item->slug === 'ehbo-noodverbanddoos')
                                        <span class="px-2 py-0.5 rounded-md bg-rose-100 dark:bg-rose-950/70 text-rose-800 dark:text-rose-300 border border-rose-200/50 dark:border-rose-800/60 text-[10px] font-black flex items-center gap-1">
                                            <i class="fa-solid fa-heart text-rose-600 dark:text-rose-400"></i> 100% Volle HP
                                        </span>
                                        @elseif($inv->item->health_restore > 0)
                                        <span class="px-2 py-0.5 rounded-md bg-rose-100 dark:bg-rose-950/70 text-rose-800 dark:text-rose-300 border border-rose-200/50 dark:border-rose-800/60 text-[10px] font-black flex items-center gap-1">
                                            <i class="fa-solid fa-heart text-rose-600 dark:text-rose-400"></i> +{{ $inv->item->health_restore }} HP
                                        </span>
                                        @endif
                                        @if($inv->item->slug === 'bivakmuts-masker')
                                        <span class="px-2 py-0.5 rounded-md bg-indigo-100 dark:bg-indigo-950/70 text-indigo-800 dark:text-indigo-300 border border-indigo-200/50 dark:border-indigo-800/60 text-[10px] font-black flex items-center gap-1">
                                            <i class="fa-solid fa-mask text-indigo-600 dark:text-indigo-400"></i> +1 Masker
                                        </span>
                                        @endif
                                        @if($inv->item->slug === 'smokkelpakketje')
                                        <span class="px-2 py-0.5 rounded-md bg-purple-100 dark:bg-purple-950/70 text-purple-800 dark:text-purple-300 border border-purple-200/50 dark:border-purple-800/60 text-[10px] font-black flex items-center gap-1">
                                            <i class="fa-solid fa-box text-purple-600 dark:text-purple-400"></i> +1 Pakket
                                        </span>
                                        @endif
                                        @if($inv->item->slug === 'ijzerzaagje')
                                        <span class="px-2 py-0.5 rounded-md bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 border border-amber-200/50 dark:border-amber-800/60 text-[10px] font-black flex items-center gap-1">
                                            <i class="fa-solid fa-screwdriver-wrench text-amber-600 dark:text-amber-400"></i> -30m Straf
                                        </span>
                                        @endif
                                        @if($inv->item->strength_bonus > 0)
                                        <span class="px-2 py-0.5 rounded-md bg-purple-100 dark:bg-purple-950/70 text-purple-800 dark:text-purple-300 border border-purple-200/50 dark:border-purple-800/60 text-[10px] font-black flex items-center gap-1">
                                            <i class="fa-solid fa-hand-back-fist text-purple-600 dark:text-purple-400"></i> +{{ $inv->item->strength_bonus }} Kracht
                                        </span>
                                        @endif
                                        @if($inv->item->defense_bonus > 0)
                                        <span class="px-2 py-0.5 rounded-md bg-blue-100 dark:bg-blue-950/70 text-blue-800 dark:text-blue-300 border border-blue-200/50 dark:border-blue-800/60 text-[10px] font-black flex items-center gap-1">
                                            <i class="fa-solid fa-shield text-blue-600 dark:text-blue-400"></i> +{{ $inv->item->defense_bonus }} Def
                                        </span>
                                        @endif
                                        @if($inv->item->stamina_bonus > 0)
                                        <span class="px-2 py-0.5 rounded-md bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 border border-amber-200/50 dark:border-amber-800/60 text-[10px] font-black flex items-center gap-1">
                                            <i class="fa-solid fa-person-running text-amber-600 dark:text-amber-400"></i> +{{ $inv->item->stamina_bonus }} Conditie
                                        </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Action Button -->
                                <div class="pt-2 border-t border-slate-200 dark:border-slate-800">
                                    @if($inv->item->category === 'equipment')
                                        <div class="w-full py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-extrabold text-xs flex items-center justify-center gap-2 border border-slate-200 dark:border-slate-700">
                                            <i class="fa-solid fa-shield-halved text-purple-600 dark:text-purple-400 text-xs"></i>
                                            <span>Actieve Uitrusting</span>
                                        </div>
                                    @else
                                        @php
                                            $isConsumableRecovery = ($inv->item->energy_restore > 0 || $inv->item->health_restore > 0 || in_array($inv->item->slug, ['sigaretten', 'ritalin', 'telefoon']));
                                        @endphp
                                        <div class="flex items-center gap-2">
                                            <form action="{{ route('shop.consume', $inv->id) }}" method="POST" class="flex-1">
                                                @csrf
                                                <button type="submit" 
                                                        class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs transition cursor-pointer flex items-center justify-center gap-1.5 shadow-xs active:scale-[0.98]">
                                                    @if(str_contains(strtolower($inv->item->name), 'broodje') || str_contains(strtolower($inv->item->name), 'frikandel') || str_contains(strtolower($inv->item->name), 'bami') || str_contains(strtolower($inv->item->name), 'kapsalon') || str_contains(strtolower($inv->item->name), 'bitterbal'))
                                                        <i class="fa-solid fa-utensils text-xs"></i>
                                                        <span>1x Opeten</span>
                                                    @elseif(str_contains(strtolower($inv->item->name), 'drank') || str_contains(strtolower($inv->item->name), 'koffie') || str_contains(strtolower($inv->item->name), 'shake'))
                                                        <i class="fa-solid fa-mug-hot text-xs"></i>
                                                        <span>1x Drinken</span>
                                                    @elseif(str_contains(strtolower($inv->item->name), 'sigaret'))
                                                        <i class="fa-solid fa-smoking text-xs"></i>
                                                        <span>1x Oproken</span>
                                                    @elseif(str_contains(strtolower($inv->item->name), 'ritalin') || str_contains(strtolower($inv->item->name), 'oxazepam'))
                                                        <i class="fa-solid fa-pills text-xs"></i>
                                                        <span>1x Innemen</span>
                                                    @elseif(str_contains(strtolower($inv->item->name), 'telefoon'))
                                                        <i class="fa-solid fa-mobile-screen text-xs"></i>
                                                        <span>Gebruiken</span>
                                                    @else
                                                        <i class="fa-solid fa-hand-holding-medical text-xs"></i>
                                                        <span>Gebruiken</span>
                                                    @endif
                                                </button>
                                            </form>

                                            @if($isConsumableRecovery && $inv->quantity > 1)
                                            <form action="{{ route('shop.consume', $inv->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="fill_max" value="1">
                                                <button type="submit" 
                                                        title="Eet tot 100% vol (gebruikt automatisch meerdere stuks)"
                                                        class="py-2.5 px-3 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-black text-xs transition cursor-pointer flex items-center gap-1 shadow-xs active:scale-[0.98]">
                                                    <i class="fa-solid fa-bolt text-xs"></i>
                                                    <span class="hidden sm:inline">Eet tot Vol</span>
                                                    <span class="sm:hidden">Vol</span>
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

            <!-- TAB: STERREN & DIAMANTEN -->
            <div x-show="shopTab === 'sterren'" class="space-y-6">
                <div class="bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-xs space-y-4">
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-gem text-amber-500"></i>
                        <span>Sterren & Maskers Shop</span>
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl text-center space-y-3">
                            <div class="w-12 h-12 mx-auto rounded-xl bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 flex items-center justify-center text-xl">
                                <i class="fa-solid fa-masks-theater text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-extrabold text-slate-900 dark:text-white">1x Masker</h4>
                                <p class="text-xs font-extrabold text-amber-600 dark:text-amber-400 mt-0.5 inline-flex items-center gap-1">15 Sterren <i class="fa-solid fa-star text-amber-500 text-[10px]"></i></p>
                            </div>
                            <form action="{{ route('shop.buy-star-item') }}" method="POST">
                                @csrf
                                <input type="hidden" name="item_type" value="mask_1">
                                <button type="submit" class="w-full py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs transition cursor-pointer">Kopen</button>
                            </form>
                        </div>
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl text-center space-y-3">
                            <div class="w-12 h-12 mx-auto rounded-xl bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 flex items-center justify-center text-xl">
                                <i class="fa-solid fa-masks-theater text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-extrabold text-slate-900 dark:text-white">5x Maskers</h4>
                                <p class="text-xs font-extrabold text-amber-600 dark:text-amber-400 mt-0.5 inline-flex items-center gap-1">65 Sterren <i class="fa-solid fa-star text-amber-500 text-[10px]"></i></p>
                            </div>
                            <form action="{{ route('shop.buy-star-item') }}" method="POST">
                                @csrf
                                <input type="hidden" name="item_type" value="mask_5">
                                <button type="submit" class="w-full py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs transition cursor-pointer">Kopen</button>
                            </form>
                        </div>
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl text-center space-y-3">
                            <div class="w-12 h-12 mx-auto rounded-xl bg-amber-100 dark:bg-amber-950/70 text-amber-700 dark:text-amber-300 flex items-center justify-center text-xl">
                                <i class="fa-solid fa-box-open text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-extrabold text-slate-900 dark:text-white">Exclusief Pakket</h4>
                                <p class="text-xs font-extrabold text-amber-600 dark:text-amber-400 mt-0.5 inline-flex items-center gap-1">150 Sterren <i class="fa-solid fa-star text-amber-500 text-[10px]"></i></p>
                            </div>
                            <form action="{{ route('shop.buy-star-item') }}" method="POST">
                                @csrf
                                <input type="hidden" name="item_type" value="package_150">
                                <button type="submit" class="w-full py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs transition cursor-pointer">Kopen</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Bottom Podium Ranglijst -->
    <div class="bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-xs space-y-5">
        <div class="flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3">
            <i class="fa-solid fa-trophy text-amber-500"></i>
            <span>RANGLIJST TOP PATIËNTEN</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 lg:grid-cols-10 gap-3 pt-2">
            @foreach($topRankings ?? [] as $index => $player)
            <div class="flex flex-col items-center text-center group">
                <div class="relative mb-2">
                    <img src="{{ $player->avatar_url }}" alt="{{ $player->name }}" 
                         class="w-14 h-14 sm:w-16 sm:h-16 rounded-full object-cover border-2 {{ $index === 0 ? 'border-amber-400 ring-2 ring-amber-400/30' : ($index === 1 ? 'border-purple-400' : 'border-slate-200 dark:border-slate-700') }} group-hover:scale-105 transition-transform shadow-xs">
                    
                    <span class="absolute -top-1 -left-1 w-6 h-6 rounded-full {{ $index === 0 ? 'bg-amber-400 text-slate-950 font-black' : ($index < 3 ? 'bg-purple-600 text-white font-bold' : 'bg-slate-800 text-white font-bold') }} text-xs flex items-center justify-center shadow">
                        {{ $index + 1 }}
                    </span>

                    <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 text-white text-[9px] flex items-center justify-center">
                        <i class="fa-solid fa-check"></i>
                    </span>
                </div>

                <a href="{{ route('profile.show', $player->name) }}" class="text-xs font-bold text-slate-900 dark:text-slate-200 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition truncate w-full block">
                    {{ $player->name }}
                </a>
                <span class="text-[10px] font-bold text-slate-400">
                    {{ $player->respect >= 1000000 ? number_format($player->respect / 1000000, 2) . 'M' : ($player->respect >= 1000 ? number_format($player->respect / 1000, 1) . 'K' : $player->respect) }}
                </span>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
