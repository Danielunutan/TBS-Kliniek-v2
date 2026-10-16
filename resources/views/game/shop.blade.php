@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ 
    shopTab: '{{ session('shop_tab', request('tab', 'pakketten')) }}',
    bundleFilter: 'all',
    selectedBundleId: 'big_bucks',
    bundleQty: 1,
    recipientId: '',
    bundles: {{ json_encode($bundles) }},
    ownedCounts: {{ json_encode($inventory->pluck('quantity', 'item_id')) }},
    totalInvCount: {{ (int) $inventory->sum('quantity') }},
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
        
        this.itemBuying[itemId] = true;
        this.itemToast[itemId] = null;
        
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
                this.ownedCounts[itemId] = data.owned_count;
                this.totalInvCount = data.total_inventory_count;
                
                if (window.stats) {
                    window.stats.cash = data.cash;
                    window.stats.cashFormatted = '€' + data.cash_formatted;
                }
                
                this.itemToast[itemId] = {
                    type: 'success',
                    text: qty + 'x Gekocht!'
                };
                setTimeout(() => {
                    if (this.itemToast[itemId] && this.itemToast[itemId].type === 'success') {
                        this.itemToast[itemId] = null;
                    }
                }, 2000);
            } else {
                this.itemToast[itemId] = {
                    type: 'error',
                    text: data.message || 'Niet genoeg geld of aankoop mislukt.'
                };
                setTimeout(() => {
                    if (this.itemToast[itemId] && this.itemToast[itemId].type === 'error') {
                        this.itemToast[itemId] = null;
                    }
                }, 3500);
            }
        } catch (err) {
            form.submit();
        } finally {
            this.itemBuying[itemId] = false;
        }
    }
}">

    <!-- Top Hero Banner -->
    <div class="rounded-2xl shadow-xs p-6 sm:p-8 text-slate-800 relative overflow-hidden bg-white border border-slate-200">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 bg-purple-50 text-purple-700 text-xs font-bold px-3 py-1 rounded-full border border-purple-200 mb-2">
                    <i class="fa-solid fa-store"></i> De Kliniek Kiosk & Winkel
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Kliniek Shop</h1>
                <p class="text-slate-500 text-xs sm:text-sm mt-1 max-w-xl leading-relaxed">
                    Scoor exclusieve bundels, kluiscodes, maskers en koffers met je verdiende Sterren of koop handige benodigdheden in de kantine met contante Euro's!
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-amber-700 flex items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-star text-amber-500"></i> {{ $character->stars ?? 0 }} Sterren
                </span>
                <span class="px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-purple-700 flex items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-mask text-purple-600"></i> {{ $character->masks ?? 0 }} Maskers
                </span>
            </div>
        </div>
    </div>

    <!-- MAIN TWO COLUMN SHOP LAYOUT -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left Column: Shop Sidebar Navigation -->
        <div class="lg:col-span-3 space-y-4">
            <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs p-2 space-y-1 text-xs font-bold text-slate-700">
                <button type="button" @click="setShopTab('pakketten')" 
                        :class="shopTab === 'pakketten' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'hover:bg-slate-50 text-slate-600 border border-transparent'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-lg transition cursor-pointer">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-basket-shopping text-purple-600 w-4 text-center"></i>
                        <span>Pakketten & Deals</span>
                    </span>
                    <i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i>
                </button>

                <button type="button" @click="setShopTab('sterren')" 
                        :class="shopTab === 'sterren' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'hover:bg-slate-50 text-slate-600 border border-transparent'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-lg transition cursor-pointer">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-gem text-amber-500 w-4 text-center"></i>
                        <span>Sterren Items</span>
                    </span>
                    <span class="text-[10px] text-amber-600 font-extrabold flex items-center gap-1">{{ $character->stars ?? 0 }} <i class="fa-solid fa-star text-amber-500 text-[10px]"></i></span>
                </button>

                <button type="button" @click="setShopTab('items')" 
                        :class="shopTab === 'items' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'hover:bg-slate-50 text-slate-600 border border-transparent'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-lg transition cursor-pointer">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-utensils text-emerald-600 w-4 text-center"></i>
                        <span>Kantine Items</span>
                    </span>
                    <span class="text-[10px] text-slate-400">Euro (€)</span>
                </button>

                <!-- Mijn Inventaris Tab -->
                <button type="button" @click="setShopTab('inventaris')" 
                        :class="shopTab === 'inventaris' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'hover:bg-slate-50 text-slate-600 border border-transparent'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-lg transition cursor-pointer">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-box-archive text-purple-600 w-4 text-center"></i>
                        <span>Mijn Inventaris</span>
                    </span>
                    <span class="px-2 py-0.5 rounded-full bg-purple-100 text-purple-700 font-extrabold text-[10px]" x-text="totalInvCount + ' items'">
                        {{ $inventory->sum('quantity') }} items
                    </span>
                </button>
            </div>

            <!-- Character Wallet Badge Card -->
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs space-y-3">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block border-b border-slate-100 pb-2">
                    Jouw Bezittingen
                </span>
                <div class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Sterren:</span>
                        <span class="font-extrabold text-amber-600 flex items-center gap-1">{{ $character->stars ?? 0 }} <i class="fa-solid fa-star text-amber-500 text-[10px]"></i></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Koffers:</span>
                        <span class="font-extrabold text-purple-700">{{ $character->unopened_bags ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Maskers:</span>
                        <span class="font-extrabold text-indigo-700">{{ $character->masks ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Bank:</span>
                        <span class="font-extrabold text-emerald-600">€{{ number_format($character->bank, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Content Area: Bundles & Showcase -->
        <div class="lg:col-span-9 space-y-6">

            <!-- TAB: PAKKETTEN & BUNDELS -->
            <div x-show="shopTab === 'pakketten'" class="space-y-6">

                <!-- Special Action Banner -->
                <div class="bg-gradient-to-r from-purple-50 via-pink-50 to-purple-50 border border-purple-200 rounded-xl p-4 text-center shadow-xs">
                    <span class="text-[10px] font-black uppercase tracking-widest text-purple-700 bg-purple-100 border border-purple-200 px-3 py-0.5 rounded-full mb-1.5 inline-block">
                        SPECIALE ACTIE
                    </span>
                    <p class="text-xs sm:text-sm font-extrabold text-slate-900 mt-1">
                        Bij iedere aankoop vanaf 100 sterren ontvang je <strong class="text-purple-700">1 XXL koffer als bonus</strong>
                    </p>
                    <span class="text-xs text-slate-500 font-medium">Exclusief VIP en extra kluiscodes!</span>
                </div>

                <!-- Active Bundle Showcase Card -->
                <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-5"
                     x-data="{ currentBundle() { return bundles[selectedBundleId] || bundles['big_bucks']; } }">
                    
                    <div class="border-b border-slate-100 pb-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <h3 class="text-lg font-extrabold text-slate-900" x-text="currentBundle().name"></h3>
                            <div class="text-sm font-extrabold text-amber-600 bg-amber-50 border border-amber-200 px-3 py-1 rounded-full w-fit inline-flex items-center gap-1.5">
                                <span>Prijs: <span x-text="currentBundle().cost_stars"></span> Sterren</span>
                                <i class="fa-solid fa-star text-amber-500 text-xs"></i>
                            </div>
                        </div>

                        <!-- Bundle Contents List with Icons -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4 pt-4 border-t border-slate-100">
                            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs text-slate-700 font-bold">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-coins"></i>
                                </div>
                                <span>€<strong x-text="new Intl.NumberFormat('nl-NL').format(currentBundle().cash)"></strong> Wit Bankgeld</span>
                            </div>
                            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs text-slate-700 font-bold">
                                <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-key"></i>
                                </div>
                                <span><strong x-text="currentBundle().codes"></strong> Kluiscodes</span>
                            </div>
                            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs text-slate-700 font-bold">
                                <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-briefcase"></i>
                                </div>
                                <span><strong x-text="currentBundle().koffers"></strong> Koffers</span>
                            </div>
                            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs text-slate-700 font-bold">
                                <div class="w-8 h-8 rounded-lg bg-pink-100 text-pink-600 flex items-center justify-center shrink-0">
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
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Aantal keer kopen:
                            </label>
                            <input type="number" name="quantity" min="1" max="10" x-model="bundleQty" required 
                                   class="w-full bg-white border border-slate-300 focus:border-purple-600 focus:ring-1 focus:ring-purple-600 rounded-lg px-3.5 py-2 text-xs font-bold text-slate-900 focus:outline-none">
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
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Wil je dit product cadeau geven? Selecteer of typ ontvanger:
                            </label>
                            <input type="hidden" name="recipient_id" :value="selectedId">
                            <input type="hidden" name="recipient_name" :value="search">

                            <div class="relative flex items-center">
                                <div class="absolute left-3 top-1/2 -translate-y-1/2 flex items-center pointer-events-none text-purple-600 text-xs z-10">
                                    <i class="fa-solid fa-user-tag"></i>
                                </div>
                                <input type="text"
                                       x-model="search"
                                       @focus="openDropdown = true"
                                       @input="openDropdown = true; selectedId = ''"
                                       @click.outside="openDropdown = false"
                                       autocomplete="off"
                                       placeholder="Mijn eigen account ({{ $character->name }}) of typ naam..."
                                       class="w-full bg-white border border-slate-300 focus:border-purple-600 focus:ring-1 focus:ring-purple-600 rounded-lg py-2.5 text-xs font-bold text-slate-900 focus:outline-none transition tbs-search-input shadow-2xs"
                                       style="padding-left: 2.75rem !important; padding-right: 2.25rem !important;">
                                <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center pointer-events-none text-slate-400 text-xs z-10">
                                    <i class="fa-solid fa-chevron-down transition-transform duration-150" :class="openDropdown ? 'rotate-180' : ''"></i>
                                </div>
                            </div>

                            <!-- Supplemental Dropdown List -->
                            <div x-show="openDropdown" 
                                 x-transition
                                 class="absolute z-50 left-0 right-0 mt-1 max-h-52 overflow-y-auto bg-white rounded-xl shadow-lg border border-slate-200 divide-y divide-slate-100">
                                <template x-for="p in filtered" :key="p.name">
                                    <button type="button" 
                                            @click="select(p)"
                                            class="w-full text-left px-3.5 py-2.5 hover:bg-purple-50 flex items-center justify-between text-xs transition cursor-pointer group">
                                        <div class="flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black"
                                                  :class="p.isSelf ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-700'">
                                                <i :class="p.isSelf ? 'fa-solid fa-user' : 'fa-solid fa-user-ninja'"></i>
                                            </span>
                                            <div>
                                                <span class="font-extrabold text-slate-900 group-hover:text-purple-700 block" x-text="p.name"></span>
                                                <span class="text-[10px] text-slate-400" x-text="'Level ' + p.level + ' • ' + p.respect + ' Respect'"></span>
                                            </div>
                                        </div>
                                        <span x-show="selectedName === p.name" class="text-purple-600 text-xs">
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
                <div class="flex items-center justify-center gap-2 bg-slate-100 border border-slate-200 p-1.5 rounded-xl">
                    <button type="button" @click="bundleFilter = 'all'" 
                            :class="bundleFilter === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="px-5 py-2 rounded-lg text-xs font-extrabold uppercase transition cursor-pointer">
                        Alle Deals
                    </button>
                    <button type="button" @click="bundleFilter = 'moord'" 
                            :class="bundleFilter === 'moord' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="px-5 py-2 rounded-lg text-xs font-extrabold uppercase transition cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-gun text-xs text-rose-500"></i> Moord
                    </button>
                    <button type="button" @click="bundleFilter = 'slay'" 
                            :class="bundleFilter === 'slay' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="px-5 py-2 rounded-lg text-xs font-extrabold uppercase transition cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-fire text-xs text-amber-500"></i> Slay
                    </button>
                    <button type="button" @click="bundleFilter = 'mystery'" 
                            :class="bundleFilter === 'mystery' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="px-5 py-2 rounded-lg text-xs font-extrabold uppercase transition cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-wand-magic-sparkles text-xs text-purple-600"></i> Mystery
                    </button>
                </div>

                <!-- Deals List Table -->
                <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs divide-y divide-slate-100">
                    @foreach($bundles as $bKey => $b)
                    <div x-show="bundleFilter === 'all' || bundleFilter === '{{ $b['category'] }}'"
                         @click="selectedBundleId = '{{ $bKey }}'"
                         :class="selectedBundleId === '{{ $bKey }}' ? 'bg-purple-50/70 border-l-4 border-purple-600' : 'hover:bg-slate-50'"
                         class="p-4 flex items-center justify-between gap-4 transition cursor-pointer">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-lg shrink-0">
                                <i class="fa-solid fa-box-open"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-extrabold text-slate-900">{{ $b['name'] }}</h4>
                                <span class="text-[11px] text-slate-500">{{ $b['codes'] }} codes &bull; {{ $b['koffers'] }} koffers &bull; {{ $b['masks'] }} maskers</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-extrabold text-amber-600 inline-flex items-center gap-1">{{ number_format($b['cost_stars']) }} Sterren <i class="fa-solid fa-star text-amber-500 text-[10px]"></i></span>
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
                <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-utensils text-purple-600"></i>
                            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">Kantine & Uitrusting</h3>
                        </div>
                        <button type="button" @click="setShopTab('inventaris')" class="text-xs font-bold text-purple-600 hover:text-purple-700 flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-box-archive"></i>
                            <span x-text="'Naar mijn inventaris (' + totalInvCount + ')'">Naar mijn inventaris ({{ $inventory->sum('quantity') }})</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($items as $item)
                        @php
                            $ownedCount = $inventory->firstWhere('item_id', $item->id)?->quantity ?? 0;
                        @endphp
                        <div class="bg-slate-50 border border-slate-200 hover:border-purple-300 rounded-xl p-4 flex flex-col justify-between gap-3 transition shadow-xs">
                            <div class="space-y-2">
                                <div class="flex items-start justify-between gap-2 min-h-[40px]">
                                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-base shrink-0">
                                        @if(str_contains(strtolower($item->name), 'frikandel'))
                                            <i class="fa-solid fa-hotdog"></i>
                                        @elseif(str_contains(strtolower($item->name), 'energie') || str_contains(strtolower($item->name), 'drank'))
                                            <i class="fa-solid fa-bolt text-amber-500"></i>
                                        @elseif(str_contains(strtolower($item->name), 'appel') || str_contains(strtolower($item->name), 'fruit'))
                                            <i class="fa-solid fa-apple-whole text-rose-500"></i>
                                        @elseif(str_contains(strtolower($item->name), 'koffie'))
                                            <i class="fa-solid fa-mug-hot text-amber-700"></i>
                                        @else
                                            <i class="fa-solid {{ $item->icon ?: 'fa-utensils' }}"></i>
                                        @endif
                                    </div>
                                    <div class="h-6 flex items-center">
                                        <template x-if="ownedCounts[{{ $item->id }}] > 0">
                                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-black shrink-0 transition"
                                                  x-text="ownedCounts[{{ $item->id }}] + 'x op voorraad'">
                                                {{ $ownedCount }}x op voorraad
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="text-xs font-extrabold text-slate-900">{{ $item->name }}</h4>
                                    <p class="text-[11px] text-slate-500 line-clamp-2 mt-0.5">{{ $item->description ?: 'Heerlijke snack uit de kliniekkantine.' }}</p>
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
                                </div>
                            </div>

                            <div class="pt-2 border-t border-slate-200/70 space-y-2.5"
                                 x-data="{
                                     buyQty: 1,
                                     unitPrice: {{ $item->price_kc }},
                                     discountPct: {{ min(15, (int) floor($character->getEffectiveCharisma() / 10)) }},
                                     get actualPrice() {
                                         return this.discountPct > 0 ? Math.round(this.unitPrice * (1 - (this.discountPct / 100))) : this.unitPrice;
                                     },
                                     get totalCost() {
                                         return this.actualPrice * this.buyQty;
                                     },
                                     get maxAffordable() {
                                         let cash = (typeof stats !== 'undefined' && stats.cash !== undefined) ? stats.cash : {{ (int) $character->cash }};
                                         return Math.max(1, Math.min(100, Math.floor(cash / Math.max(1, this.actualPrice))));
                                     }
                                 }">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-500 font-medium">Prijs per stuk:</span>
                                    <div class="text-right">
                                        <span class="font-extrabold text-emerald-700 font-mono">
                                            €{{ number_format($item->price_kc, 0, ',', '.') }}
                                        </span>
                                        <template x-if="buyQty > 1">
                                            <span class="text-[10px] text-slate-400 block font-mono" x-text="'Totaal: €' + new Intl.NumberFormat('nl-NL').format(totalCost)"></span>
                                        </template>
                                    </div>
                                </div>

                                <!-- Snelle Aantal Knoppen (1x, 5x, 10x, Max) -->
                                <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl border border-slate-200/70">
                                    <button type="button" @click="buyQty = 1" :class="buyQty === 1 ? 'bg-white text-purple-700 shadow-2xs font-black' : 'text-slate-500 hover:text-slate-800 font-bold'" class="flex-1 py-1 rounded-lg text-[11px] transition cursor-pointer">1x</button>
                                    <button type="button" @click="buyQty = 5" :class="buyQty === 5 ? 'bg-white text-purple-700 shadow-2xs font-black' : 'text-slate-500 hover:text-slate-800 font-bold'" class="flex-1 py-1 rounded-lg text-[11px] transition cursor-pointer">5x</button>
                                    <button type="button" @click="buyQty = 10" :class="buyQty === 10 ? 'bg-white text-purple-700 shadow-2xs font-black' : 'text-slate-500 hover:text-slate-800 font-bold'" class="flex-1 py-1 rounded-lg text-[11px] transition cursor-pointer">10x</button>
                                    <button type="button" @click="buyQty = Math.max(1, maxAffordable)" :class="buyQty > 10 ? 'bg-white text-purple-700 shadow-2xs font-black' : 'text-slate-500 hover:text-slate-800 font-bold'" class="flex-1 py-1 rounded-lg text-[11px] transition cursor-pointer" title="Koop zoveel als je contant kunt betalen">Max</button>
                                </div>

                                <form action="{{ route('shop.buy', $item->id) }}" method="POST" @submit.prevent="buyItemViaAjax($event, {{ $item->id }}, buyQty)">
                                    @csrf
                                    <input type="hidden" name="quantity" :value="buyQty">
                                    
                                    <button type="submit" 
                                            :disabled="((typeof stats !== 'undefined' && stats.cash !== undefined) ? stats.cash : {{ (int) $character->cash }}) < totalCost || itemBuying[{{ $item->id }}]"
                                            class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed text-white font-extrabold text-xs transition cursor-pointer flex items-center justify-center gap-1.5 shadow-xs active:scale-[0.98]">
                                        <template x-if="itemBuying[{{ $item->id }}]">
                                            <span class="inline-flex items-center gap-1.5 text-slate-300">
                                                <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                                                <span>Inpakken...</span>
                                            </span>
                                        </template>
                                        <template x-if="!itemBuying[{{ $item->id }}] && (!itemToast[{{ $item->id }}] || itemToast[{{ $item->id }}].type !== 'success')">
                                            <span class="inline-flex items-center gap-1.5">
                                                <i class="fa-solid fa-cart-shopping text-xs"></i>
                                                <span x-text="buyQty > 1 ? 'Koop ' + buyQty + 'x direct' : 'Kopen'">Kopen</span>
                                            </span>
                                        </template>
                                        <template x-if="!itemBuying[{{ $item->id }}] && itemToast[{{ $item->id }}] && itemToast[{{ $item->id }}].type === 'success'">
                                            <span class="inline-flex items-center gap-1.5 text-emerald-400 font-black">
                                                <i class="fa-solid fa-circle-check text-xs"></i>
                                                <span x-text="itemToast[{{ $item->id }}].text"></span>
                                            </span>
                                        </template>
                                    </button>

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
                <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-box-archive text-purple-600"></i>
                                <span>Mijn Rugzak & Inventaris</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Hier bewaar je al je gekochte kantinesnacks, maaltijden en benodigdheden.</p>
                        </div>
                        <button type="button" @click="setShopTab('items')" class="px-3.5 py-1.5 rounded-lg bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-xs transition cursor-pointer flex items-center gap-1.5 w-fit">
                            <i class="fa-solid fa-plus"></i>
                            <span>Snacks Kopen in Kantine</span>
                        </button>
                    </div>

                    <!-- Energy and Health Progress -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-slate-600 flex items-center gap-1.5">
                                    <i class="fa-solid fa-bolt text-amber-500"></i> Energie
                                </span>
                                <span class="text-slate-900 font-mono">{{ $character->energy }} / {{ $character->max_energy }}</span>
                            </div>
                            <div class="w-full h-2.5 bg-slate-200 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-amber-400 to-emerald-500 rounded-full transition-all duration-300" 
                                     style="width: {{ min(100, round(($character->energy / max(1, $character->max_energy)) * 100)) }}%"></div>
                            </div>
                        </div>

                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-slate-600 flex items-center gap-1.5">
                                    <i class="fa-solid fa-heart-pulse text-rose-500"></i> Gezondheid (HP)
                                </span>
                                <span class="text-slate-900 font-mono">{{ $character->health }} / {{ $character->max_health }}</span>
                            </div>
                            <div class="w-full h-2.5 bg-slate-200 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-rose-500 to-pink-500 rounded-full transition-all duration-300" 
                                     style="width: {{ min(100, round(($character->health / max(1, $character->max_health)) * 100)) }}%"></div>
                            </div>
                        </div>
                    </div>

                    @php
                        $foodAndRecovery = $inventory->filter(function($inv) {
                            return ($inv->item->category !== 'equipment') && (
                                $inv->item->energy_restore > 0 || 
                                $inv->item->health_restore > 0 || 
                                in_array($inv->item->slug, ['sigaretten', 'ritalin', 'telefoon', 'adrenaline-injectie', 'ehbo-noodverbanddoos'])
                            );
                        });
                        $needsRecovery = ($character->energy < $character->max_energy) || ($character->health < $character->max_health);
                    @endphp

                    <!--  SNEL ETEN & HERSTELLEN BALK (1-KLIK) -->
                    <div class="mt-4 p-4 rounded-xl border {{ $needsRecovery ? 'bg-gradient-to-r from-amber-50/70 via-emerald-50/50 to-white border-emerald-300/80' : 'bg-slate-50 border-slate-200' }}">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200/70">
                            <div>
                                <h4 class="text-xs font-black text-slate-900 uppercase tracking-wide flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs shadow-2xs">
                                        <i class="fa-solid fa-utensils"></i>
                                    </span>
                                    <span>Snel Eten & Herstellen</span>
                                    @if(! $needsRecovery)
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">100% Volledig Hersteld!</span>
                                    @endif
                                </h4>
                                <p class="text-[11px] text-slate-500 mt-0.5">
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
                                <div class="bg-white p-3 rounded-lg border border-slate-200 shadow-2xs flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-9 h-9 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-sm shrink-0">
                                            @if(str_contains(strtolower($quickInv->item->name), 'frikandel'))
                                                <i class="fa-solid fa-hotdog"></i>
                                            @elseif(str_contains(strtolower($quickInv->item->name), 'drank') || str_contains(strtolower($quickInv->item->name), 'energie'))
                                                <i class="fa-solid fa-bolt text-amber-500"></i>
                                            @elseif(str_contains(strtolower($quickInv->item->name), 'koffie'))
                                                <i class="fa-solid fa-mug-hot text-amber-700"></i>
                                            @else
                                                <i class="fa-solid {{ $quickInv->item->icon ?: 'fa-utensils' }}"></i>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-xs font-black text-slate-900 truncate flex items-center gap-1.5">
                                                <span>{{ $quickInv->item->name }}</span>
                                                <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-bold">{{ $quickInv->quantity }}x</span>
                                            </div>
                                            <div class="text-[10px] text-slate-500 flex items-center gap-2 pt-0.5">
                                                @if($quickInv->item->energy_restore > 0)
                                                    <span class="text-emerald-700 font-bold">+{{ $quickInv->item->energy_restore }}</span>
                                                @endif
                                                @if($quickInv->item->health_restore > 0)
                                                    <span class="text-rose-700 font-bold">+{{ $quickInv->item->health_restore }}️</span>
                                                @endif
                                                @if(in_array($quickInv->item->slug, ['sigaretten', 'ritalin', 'telefoon']))
                                                    <span class="text-purple-700 font-bold">Herstelboost</span>
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
                                                    class="px-2.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 font-black text-xs transition cursor-pointer flex items-center gap-1 active:scale-95">
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
                            <div class="pt-3 text-xs text-slate-500 flex items-center justify-between gap-3">
                                <span class="flex items-center gap-2">
                                    <i class="fa-solid fa-circle-info text-amber-500"></i>
                                    Je hebt geen eten of drinken in je rugzak. Koop snacks in de kantine om snel te herstellen na gevechten of misdaden!
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Items Grid or Empty State -->
                <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs">
                    @if($inventory->isEmpty())
                        <div class="text-center py-12 space-y-3">
                            <div class="w-16 h-16 mx-auto rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xl border border-purple-100 shadow-xs">
                                <i class="fa-solid fa-box-open"></i>
                            </div>
                            <h4 class="text-sm font-extrabold text-slate-800">Je inventaris is momenteel leeg</h4>
                            <p class="text-xs text-slate-500 max-w-sm mx-auto">
                                Je hebt nog geen items of snacks in je rugzak. Bezoek de kantine om lekkere frikandelbroodjes of drankjes te kopen!
                            </p>
                            <button type="button" @click="setShopTab('items')" class="mt-2 px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs transition cursor-pointer inline-flex items-center gap-2 shadow-xs">
                                <i class="fa-solid fa-store"></i>
                                <span>Kantine Bekijken</span>
                            </button>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($inventory as $inv)
                            <div class="bg-gradient-to-b from-white to-slate-50 border border-slate-200 hover:border-purple-300 rounded-xl p-4 flex flex-col justify-between gap-3 shadow-xs transition group">
                                <div class="space-y-2.5">
                                    <div class="flex items-center justify-between">
                                        <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-xl shrink-0 group-hover:scale-105 transition-transform shadow-xs">
                                            @if(str_contains(strtolower($inv->item->name), 'frikandel'))
                                                <i class="fa-solid fa-hotdog"></i>
                                            @elseif(str_contains(strtolower($inv->item->name), 'energie') || str_contains(strtolower($inv->item->name), 'drank'))
                                                <i class="fa-solid fa-bolt text-amber-500"></i>
                                            @elseif(str_contains(strtolower($inv->item->name), 'appel') || str_contains(strtolower($inv->item->name), 'fruit'))
                                                <i class="fa-solid fa-apple-whole text-rose-500"></i>
                                            @elseif(str_contains(strtolower($inv->item->name), 'koffie'))
                                                <i class="fa-solid fa-mug-hot text-amber-700"></i>
                                            @else
                                                <i class="fa-solid {{ $inv->item->icon ?: 'fa-utensils' }}"></i>
                                            @endif
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full bg-purple-600 text-white font-black text-xs font-mono shadow-xs">
                                            {{ $inv->quantity }}x
                                        </span>
                                    </div>

                                    <div>
                                        <h4 class="text-sm font-black text-slate-900">{{ $inv->item->name }}</h4>
                                        <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">{{ $inv->item->description ?: 'Eet dit item om je energie of gezondheid te herstellen.' }}</p>
                                    </div>

                                    <!-- Effects Pills -->
                                    <div class="flex flex-wrap gap-1.5 pt-1">
                                        @if($inv->item->slug === 'adrenaline-injectie')
                                        <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-black flex items-center gap-1">
                                            <i class="fa-solid fa-bolt text-emerald-600"></i> 100% Volle Energie
                                        </span>
                                        @elseif($inv->item->energy_restore > 0)
                                        <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-black flex items-center gap-1">
                                            <i class="fa-solid fa-bolt text-emerald-600"></i> +{{ $inv->item->energy_restore }} Energie
                                        </span>
                                        @endif
                                        @if($inv->item->slug === 'ehbo-noodverbanddoos')
                                        <span class="px-2 py-0.5 rounded-md bg-rose-100 text-rose-800 text-[10px] font-black flex items-center gap-1">
                                            <i class="fa-solid fa-heart text-rose-600"></i> 100% Volle HP
                                        </span>
                                        @elseif($inv->item->health_restore > 0)
                                        <span class="px-2 py-0.5 rounded-md bg-rose-100 text-rose-800 text-[10px] font-black flex items-center gap-1">
                                            <i class="fa-solid fa-heart text-rose-600"></i> +{{ $inv->item->health_restore }} HP
                                        </span>
                                        @endif
                                        @if($inv->item->slug === 'bivakmuts-masker')
                                        <span class="px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-800 text-[10px] font-black flex items-center gap-1">
                                            <i class="fa-solid fa-mask text-indigo-600"></i> +1 Masker
                                        </span>
                                        @endif
                                        @if($inv->item->slug === 'smokkelpakketje')
                                        <span class="px-2 py-0.5 rounded-md bg-purple-100 text-purple-800 text-[10px] font-black flex items-center gap-1">
                                            <i class="fa-solid fa-box text-purple-600"></i> +1 Pakket
                                        </span>
                                        @endif
                                        @if($inv->item->slug === 'ijzerzaagje')
                                        <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 text-[10px] font-black flex items-center gap-1">
                                            <i class="fa-solid fa-screwdriver-wrench text-amber-600"></i> -30m Straf
                                        </span>
                                        @endif
                                        @if($inv->item->strength_bonus > 0)
                                        <span class="px-2 py-0.5 rounded-md bg-purple-100 text-purple-800 text-[10px] font-black flex items-center gap-1">
                                            <i class="fa-solid fa-hand-back-fist text-purple-600"></i> +{{ $inv->item->strength_bonus }} Kracht
                                        </span>
                                        @endif
                                        @if($inv->item->defense_bonus > 0)
                                        <span class="px-2 py-0.5 rounded-md bg-blue-100 text-blue-800 text-[10px] font-black flex items-center gap-1">
                                            <i class="fa-solid fa-shield text-blue-600"></i> +{{ $inv->item->defense_bonus }} Def
                                        </span>
                                        @endif
                                        @if($inv->item->stamina_bonus > 0)
                                        <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 text-[10px] font-black flex items-center gap-1">
                                            <i class="fa-solid fa-person-running text-amber-600"></i> +{{ $inv->item->stamina_bonus }} Conditie
                                        </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Action Button -->
                                <div class="pt-2 border-t border-slate-200">
                                    @if($inv->item->category === 'equipment')
                                        <div class="w-full py-2.5 rounded-xl bg-slate-100 text-slate-700 font-extrabold text-xs flex items-center justify-center gap-2 border border-slate-200">
                                            <i class="fa-solid fa-shield-halved text-purple-600 text-xs"></i>
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
                <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-4">
                    <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-gem text-amber-500"></i>
                        <span>Sterren & Maskers Shop</span>
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-center space-y-3">
                            <div class="w-12 h-12 mx-auto rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-xl">
                                <i class="fa-solid fa-masks-theater text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-extrabold text-slate-900">1x Masker</h4>
                                <p class="text-xs font-extrabold text-amber-600 mt-0.5 inline-flex items-center gap-1">15 Sterren <i class="fa-solid fa-star text-amber-500 text-[10px]"></i></p>
                            </div>
                            <form action="{{ route('shop.buy-star-item') }}" method="POST">
                                @csrf
                                <input type="hidden" name="item_type" value="mask_1">
                                <button type="submit" class="w-full py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs transition cursor-pointer">Kopen</button>
                            </form>
                        </div>
                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-center space-y-3">
                            <div class="w-12 h-12 mx-auto rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-xl">
                                <i class="fa-solid fa-masks-theater text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-extrabold text-slate-900">5x Maskers</h4>
                                <p class="text-xs font-extrabold text-amber-600 mt-0.5 inline-flex items-center gap-1">65 Sterren <i class="fa-solid fa-star text-amber-500 text-[10px]"></i></p>
                            </div>
                            <form action="{{ route('shop.buy-star-item') }}" method="POST">
                                @csrf
                                <input type="hidden" name="item_type" value="mask_5">
                                <button type="submit" class="w-full py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs transition cursor-pointer">Kopen</button>
                            </form>
                        </div>
                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-center space-y-3">
                            <div class="w-12 h-12 mx-auto rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl">
                                <i class="fa-solid fa-box-open text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-extrabold text-slate-900">Exclusief Pakket</h4>
                                <p class="text-xs font-extrabold text-amber-600 mt-0.5 inline-flex items-center gap-1">150 Sterren <i class="fa-solid fa-star text-amber-500 text-[10px]"></i></p>
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
    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-5">
        <div class="flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-3">
            <i class="fa-solid fa-trophy text-amber-500"></i>
            <span>RANGLIJST TOP PATIËNTEN</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 lg:grid-cols-10 gap-3 pt-2">
            @foreach($topRankings ?? [] as $index => $player)
            <div class="flex flex-col items-center text-center group">
                <div class="relative mb-2">
                    <img src="{{ $player->avatar_url }}" alt="{{ $player->name }}" 
                         class="w-14 h-14 sm:w-16 sm:h-16 rounded-full object-cover border-2 {{ $index === 0 ? 'border-amber-400 ring-2 ring-amber-400/30' : ($index === 1 ? 'border-purple-400' : 'border-slate-200') }} group-hover:scale-105 transition-transform shadow-xs">
                    
                    <span class="absolute -top-1 -left-1 w-6 h-6 rounded-full {{ $index === 0 ? 'bg-amber-400 text-slate-950 font-black' : ($index < 3 ? 'bg-purple-600 text-white font-bold' : 'bg-slate-800 text-white font-bold') }} text-xs flex items-center justify-center shadow">
                        {{ $index + 1 }}
                    </span>

                    <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 text-white text-[9px] flex items-center justify-center">
                        <i class="fa-solid fa-check"></i>
                    </span>
                </div>

                <a href="{{ route('profile.show', $player->name) }}" class="text-xs font-bold text-slate-900 group-hover:text-purple-600 transition truncate w-full block">
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
