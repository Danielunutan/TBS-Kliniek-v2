@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-slate-900 uppercase tracking-wide flex items-center gap-2">
                <i class="fa-solid fa-shop text-purple-700"></i>
                <span>De Kantinewinkel van Henkie</span>
            </h1>
            <p class="text-xs text-slate-600 mt-1">
                Koop warme snacks voor directe energie, trainingsattributen voor betere statistieken en geheime relikwieën van de kliniek.
            </p>
        </div>
        <div class="bg-amber-500 text-purple-950 px-4 py-2 rounded-lg text-xs font-black shadow-sm">
            <span>Contant: {{ number_format($character->cash, 0, ',', '.') }} KC</span>
        </div>
    </div>

    <!-- Shop Grid -->
    <div>
        <h2 class="text-sm font-black uppercase text-slate-800 tracking-wider flex items-center gap-2 mb-3">
            <i class="fa-solid fa-store text-amber-500"></i>
            <span>Assortiment in de Vitrine</span>
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($items as $item)
            @php
                $rarityClasses = match($item->rarity) {
                    'uncommon' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                    'rare' => 'bg-blue-100 text-blue-800 border-blue-200',
                    'epic' => 'bg-purple-100 text-purple-900 border-purple-200',
                    'legendary' => 'bg-amber-100 text-amber-900 border-amber-300 font-black',
                    default => 'bg-slate-100 text-slate-700 border-slate-200',
                };
                $canBuy = $character->cash >= $item->price_kc;
            @endphp
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
                <div class="p-5 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-900 flex items-center justify-center text-lg shrink-0">
                                <i class="fa-solid {{ $item->icon }}"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-900">{{ $item->name }}</h3>
                                <span class="inline-block text-[10px] uppercase font-bold px-1.5 py-0.2 rounded border {{ $rarityClasses }}">
                                    {{ ucfirst($item->rarity) }}
                                </span>
                            </div>
                        </div>
                        <span class="text-xs font-black text-amber-600 bg-amber-50 px-2 py-1 rounded border border-amber-200 shrink-0">
                            {{ number_format($item->price_kc, 0, ',', '.') }} KC
                        </span>
                    </div>

                    <p class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-2.5 rounded border border-slate-100">
                        {{ $item->description }}
                    </p>

                    <div class="flex items-center gap-2 text-[11px] font-semibold text-slate-600 flex-wrap">
                        @if($item->energy_restore > 0)
                            <span class="text-yellow-700"><i class="fa-solid fa-bolt text-yellow-500 mr-1"></i>+{{ $item->energy_restore }} Energie</span>
                        @endif
                        @if($item->health_restore > 0)
                            <span class="text-rose-700"><i class="fa-solid fa-heart-pulse text-rose-500 mr-1"></i>+{{ $item->health_restore }} Gezondheid</span>
                        @endif
                        @if($item->strength_bonus > 0)
                            <span class="text-purple-700"><i class="fa-solid fa-dumbbell mr-1"></i>+{{ $item->strength_bonus }} Kracht</span>
                        @endif
                        @if($item->defense_bonus > 0)
                            <span class="text-indigo-700"><i class="fa-solid fa-shield mr-1"></i>+{{ $item->defense_bonus }} Verdediging</span>
                        @endif
                        @if($item->intelligence_bonus > 0)
                            <span class="text-blue-700"><i class="fa-solid fa-brain mr-1"></i>+{{ $item->intelligence_bonus }} Intelligentie</span>
                        @endif
                    </div>
                </div>

                <div class="px-5 py-3 bg-slate-50 border-t border-slate-200">
                    <form method="POST" action="{{ route('shop.buy', $item) }}">
                        @csrf
                        <button type="submit" @if(! $canBuy || $character->isImprisoned()) disabled @endif class="w-full bg-amber-500 hover:bg-amber-400 disabled:opacity-40 disabled:cursor-not-allowed text-purple-950 font-black py-2 px-3 rounded text-xs shadow transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-cart-shopping"></i> Kopen ({{ number_format($item->price_kc, 0, ',', '.') }} KC)
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Inventory Section -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-sm font-black uppercase text-slate-800 tracking-wider flex items-center gap-2 mb-4">
            <i class="fa-solid fa-box-open text-purple-700"></i>
            <span>Jouw Inventaris (Mijn Buidel)</span>
        </h2>

        @if($inventory->isEmpty())
        <p class="text-xs text-slate-500 italic py-4 text-center">Je inventaris is leeg. Koop een snack of haal buit binnen met missies!</p>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($inventory as $inv)
            <div class="p-4 bg-slate-50 rounded-lg border border-slate-200 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded bg-white border border-slate-200 flex items-center justify-center text-purple-900 shrink-0">
                        <i class="fa-solid {{ $inv->item->icon }}"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-slate-800">{{ $inv->item->name }}</h4>
                        <span class="text-[11px] text-slate-500 font-semibold">Aantal: {{ $inv->quantity }}x</span>
                    </div>
                </div>

                @if($inv->item->category === 'consumable')
                <form method="POST" action="{{ route('shop.consume', $inv) }}">
                    @csrf
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-3 py-1.5 rounded text-xs shadow transition flex items-center gap-1 cursor-pointer">
                        <i class="fa-solid fa-utensils"></i> Nuttigen
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
