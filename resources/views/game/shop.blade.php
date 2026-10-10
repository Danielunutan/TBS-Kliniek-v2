@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ category: 'all' }">
    <!-- Header with Tabs (Matches bottom-left screen in mockup) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-slate-900 uppercase tracking-tight">
                Winkel
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Koop snacks, uitrusting en legendarische relikwieën in de kliniek.
            </p>
        </div>

        <!-- Filter tabs matching mockup -->
        <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-xs font-bold text-slate-600 flex-wrap">
            <button type="button" @click="category = 'all'" :class="category === 'all' ? 'bg-purple-900 text-white shadow-xs' : 'hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg transition">Alle items</button>
            <button type="button" @click="category = 'consumable'" :class="category === 'consumable' ? 'bg-purple-900 text-white shadow-xs' : 'hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg transition">Consumables</button>
            <button type="button" @click="category = 'equipment'" :class="category === 'equipment' ? 'bg-purple-900 text-white shadow-xs' : 'hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg transition">Uitrusting</button>
            <button type="button" @click="category = 'collectible'" :class="category === 'collectible' ? 'bg-purple-900 text-white shadow-xs' : 'hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg transition">Verzamelobjecten</button>
        </div>
    </div>

    <!-- Items Grid (Matches exact 4-columns layout in mockup) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($items as $item)
        @php
            $hasSpecialImg = match($item->slug) {
                'frikandelbroodje' => asset('assets/items/frikandelbroodje.jpg'),
                'gouden-frikandel' => asset('assets/items/gouden_frikandel.jpg'),
                default => null,
            };
            $canBuy = $character->cash >= $item->price_kc;
        @endphp
        <div x-show="category === 'all' || category === '{{ $item->category }}'" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4 flex flex-col justify-between hover:shadow-md transition-shadow">
            <div>
                <!-- Image or Icon Box -->
                <div class="w-full h-32 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center p-2 mb-3 relative overflow-hidden">
                    @if($hasSpecialImg)
                        <img src="{{ $hasSpecialImg }}" alt="{{ $item->name }}" class="h-full object-contain">
                    @else
                        <div class="w-16 h-16 rounded-2xl bg-purple-100 text-purple-900 flex items-center justify-center text-2xl shadow-xs">
                            <i class="fa-solid {{ $item->icon }}"></i>
                        </div>
                    @endif
                </div>

                <!-- Title & Stat Badge -->
                <h3 class="font-black text-slate-900 text-sm tracking-tight text-center truncate">{{ $item->name }}</h3>
                
                <div class="text-center mt-1 text-[11px] font-bold text-slate-500">
                    @if($item->energy_restore > 0)
                        <span class="text-yellow-600">+{{ $item->energy_restore }} Energie</span>
                    @elseif($item->strength_bonus > 0)
                        <span class="text-purple-600">+{{ $item->strength_bonus }} Kracht</span>
                    @elseif($item->stamina_bonus > 0)
                        <span class="text-emerald-600">+{{ $item->stamina_bonus }} Conditie</span>
                    @elseif($item->charisma_bonus > 0)
                        <span class="text-pink-600">+{{ $item->charisma_bonus }} Charisma</span>
                    @elseif($item->intelligence_bonus > 0)
                        <span class="text-blue-600">+{{ $item->intelligence_bonus }} Intelligentie</span>
                    @elseif($item->slug === 'gouden-frikandel')
                        <span class="text-amber-500">+50 Respect</span>
                    @else
                        <span class="text-indigo-600">Speciaal Item</span>
                    @endif
                </div>

                <!-- Price -->
                <div class="text-center mt-1 text-xs font-black text-slate-800">
                    <i class="fa-solid fa-coins text-amber-500 text-[10px] mr-0.5"></i> {{ number_format($item->price_kc, 0, ',', '.') }} KC
                </div>
            </div>

            <!-- Purple Buy Button -->
            <div class="mt-4">
                <form method="POST" action="{{ route('shop.buy', $item) }}">
                    @csrf
                    <button type="submit" @if(! $canBuy || $character->isImprisoned()) disabled @endif class="w-full bg-[#6d28d9] hover:bg-[#5b21b6] disabled:opacity-40 disabled:cursor-not-allowed text-white font-black py-2 px-3 rounded-xl text-xs shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                        Kopen
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Inventory / Buidel Section -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
        <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide mb-3 flex items-center gap-2">
            <i class="fa-solid fa-box text-purple-700"></i>
            <span>Mijn Buidel & Inventaris</span>
        </h2>
        @if($inventory->isEmpty())
            <p class="text-xs text-slate-400 italic">Je hebt nog geen items gekocht.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach($inventory as $inv)
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between gap-3 text-xs">
                    <div>
                        <h4 class="font-black text-slate-900">{{ $inv->item->name }}</h4>
                        <span class="text-[11px] text-slate-500 font-semibold">{{ $inv->quantity }}x in bezit</span>
                    </div>
                    @if($inv->item->category === 'consumable')
                    <form method="POST" action="{{ route('shop.consume', $inv) }}">
                        @csrf
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-2.5 py-1.5 rounded-lg text-xs shadow-xs cursor-pointer">
                            Nuttigen
                        </button>
                    </form>
                    @endif
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
