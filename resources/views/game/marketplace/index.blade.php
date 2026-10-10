@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'alle', createModalOpen: false }">

    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-purple-950 to-slate-900 rounded-2xl p-6 sm:p-8 text-white relative overflow-hidden shadow-xs">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 bg-purple-500/20 text-purple-300 text-xs font-bold px-3 py-1 rounded-full border border-purple-400/20 mb-3">
                    <i class="fa-solid fa-shop"></i> Speler-tot-Speler Handelsplatform
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">De Kliniek Marktplaats</h1>
                <p class="text-purple-200/90 text-xs sm:text-sm mt-1 max-w-2xl leading-relaxed">
                    Verkoop en koop zeldzame goederen tussen patiënten onderling: ruil <strong>Sterren ⭐ voor Geld</strong>, <strong>Geld voor Sterren</strong>, verhandel <strong>Kluiscodes</strong> en ongeopende <strong>Smokkeltassen</strong>!
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <button @click="createModalOpen = true" class="px-5 py-3 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-black text-xs flex items-center gap-2 transition shadow-md cursor-pointer">
                    <i class="fa-solid fa-plus-circle text-sm text-amber-200"></i>
                    <span>Aanbod Plaatsen</span>
                </button>
            </div>
        </div>

        <div class="absolute -right-6 -bottom-6 text-white/5 text-9xl font-black select-none pointer-events-none">
            <i class="fa-solid fa-scale-balanced"></i>
        </div>
    </div>

    <!-- Inventory Quick Overview (What can you sell?) -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                <i class="fa-solid fa-star"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide block">Sterren</span>
                <span class="text-sm font-black text-amber-700">{{ $character->stars ?? 0 }} ⭐</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                <i class="fa-solid fa-bag-shopping"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide block">Smokkeltassen</span>
                <span class="text-sm font-black text-indigo-700">{{ $character->unopened_bags ?? 0 }} 🧳</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                <i class="fa-solid fa-key"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide block">Eigen Kluiscodes</span>
                <span class="text-sm font-black text-purple-700">{{ $myVaultCodes->count() }} 🔐</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                <i class="fa-solid fa-mask"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide block">Maskers</span>
                <span class="text-sm font-black text-rose-700">{{ $character->masks ?? 0 }} 🎭</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <i class="fa-solid fa-coins"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide block">Contant Geld</span>
                <span class="text-sm font-black text-emerald-700">{{ number_format($character->cash, 0, ',', '.') }} KC</span>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 custom-scrollbar">
        <a href="{{ route('marketplace.index', ['type' => 'all']) }}" 
            class="px-4 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap {{ $filterType === 'all' ? 'bg-slate-900 text-white font-bold shadow-xs' : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200' }}">
            Alle Aanbiedingen ({{ count($listings) }})
        </a>
        <a href="{{ route('marketplace.index', ['type' => 'stars']) }}" 
            class="px-4 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap {{ $filterType === 'stars' ? 'bg-slate-900 text-white font-bold shadow-xs' : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200' }}">
            ⭐ Sterren
        </a>
        <a href="{{ route('marketplace.index', ['type' => 'vault_codes']) }}" 
            class="px-4 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap {{ $filterType === 'vault_codes' ? 'bg-slate-900 text-white font-bold shadow-xs' : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200' }}">
            🔐 Kluiscodes
        </a>
        <a href="{{ route('marketplace.index', ['type' => 'packages']) }}" 
            class="px-4 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap {{ $filterType === 'packages' ? 'bg-slate-900 text-white font-bold shadow-xs' : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200' }}">
            🧳 Smokkelkoffers
        </a>
        <a href="{{ route('marketplace.index', ['type' => 'masks']) }}" 
            class="px-4 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap {{ $filterType === 'masks' ? 'bg-slate-900 text-white font-bold shadow-xs' : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200' }}">
            🎭 Maskers
        </a>
        <a href="{{ route('marketplace.index', ['type' => 'cash']) }}" 
            class="px-4 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap {{ $filterType === 'cash' ? 'bg-slate-900 text-white font-bold shadow-xs' : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200' }}">
            💰 Contant Geld
        </a>
    </div>

    <!-- Listings Table / Grid -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-extrabold text-slate-900">Actuele Aanbiedingen</h2>
            <span class="text-xs font-semibold text-slate-400">{{ count($listings) }} artikelen te koop</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-[10px] uppercase tracking-wider font-bold text-slate-400 border-b border-slate-100">
                        <th class="p-4">Aanbod & Categorie</th>
                        <th class="p-4">Verkoper</th>
                        <th class="p-4 text-center">Aantal</th>
                        <th class="p-4 text-right">Vraagprijs</th>
                        <th class="p-4 text-right">Actie</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($listings as $item)
                        @php
                            $isMine = $item->seller_id === $character->id;
                            $canAfford = $item->currency === 'cash' ? ($character->cash >= $item->price) : (($character->stars ?? 0) >= $item->price);
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors {{ $isMine ? 'bg-purple-50/30' : '' }}">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg shrink-0
                                        {{ $item->item_type === 'stars' ? 'bg-amber-100 text-amber-700' : ($item->item_type === 'vault_codes' ? 'bg-purple-100 text-purple-700' : ($item->item_type === 'packages' ? 'bg-indigo-100 text-indigo-700' : ($item->item_type === 'masks' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'))) }}">
                                        @if($item->item_type === 'stars')
                                            <i class="fa-solid fa-star"></i>
                                        @elseif($item->item_type === 'vault_codes')
                                            <i class="fa-solid fa-key"></i>
                                        @elseif($item->item_type === 'packages')
                                            <i class="fa-solid fa-briefcase"></i>
                                        @elseif($item->item_type === 'masks')
                                            <i class="fa-solid fa-mask"></i>
                                        @else
                                            <i class="fa-solid fa-coins"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="font-black text-slate-900 text-xs block leading-tight">{{ $item->title }}</span>
                                        <span class="text-[10px] font-semibold text-slate-400 capitalize">Type: {{ $item->item_type }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                @if($item->seller)
                                    <div class="flex items-center gap-2">
                                        <img src="{{ $item->seller->avatar_url }}" alt="{{ $item->seller->name }}" class="w-6 h-6 rounded-full object-cover">
                                        <a href="{{ route('profile.show', $item->seller->name) }}" class="font-bold text-slate-800 hover:text-indigo-600 text-xs">
                                            {{ $item->seller->name }}
                                        </a>
                                        @if($isMine)
                                            <span class="text-[9px] bg-purple-100 text-purple-800 font-extrabold px-1.5 py-0.2 rounded">Jij</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400">Onbekend</span>
                                @endif
                            </td>
                            <td class="p-4 text-center font-extrabold text-slate-700">
                                {{ $item->amount }}x
                            </td>
                            <td class="p-4 text-right">
                                <span class="font-black text-sm {{ $item->currency === 'stars' ? 'text-amber-600' : 'text-emerald-700' }}">
                                    @if($item->currency === 'stars')
                                        {{ $item->price }} ⭐
                                    @else
                                        {{ number_format($item->price, 0, ',', '.') }} KC
                                    @endif
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                @if($isMine)
                                    <form action="{{ route('marketplace.cancel', $item->id) }}" method="POST" class="inline"
                                          data-confirm="Weet je zeker dat je deze advertentie wilt intrekken?"
                                          data-confirm-title="Advertentie Annuleren"
                                          data-confirm-btn="Intrekken"
                                          data-confirm-type="warning">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs transition cursor-pointer">
                                            Annuleren
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('marketplace.buy', $item->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                {{ !$canAfford ? 'disabled' : '' }}
                                                class="px-4 py-1.5 rounded-lg font-black text-xs transition shadow-2xs cursor-pointer
                                                {{ $canAfford ? 'bg-slate-900 hover:bg-slate-800 text-white' : 'bg-slate-200 text-slate-400 cursor-not-allowed' }}">
                                            Kopen
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-12 text-center text-slate-400">
                                <i class="fa-solid fa-box-open text-3xl mb-2 block"></i>
                                Er zijn momenteel geen aanbiedingen in deze categorie. Plaats als eerste een aanbod!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- CREATE LISTING MODAL -->
    <div x-show="createModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="createModalOpen = false" class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-purple-600"></i>
                    <span>Nieuw Aanbod Plaatsen</span>
                </h3>
                <button @click="createModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('marketplace.store') }}" method="POST" class="space-y-4" x-data="{ selectedType: 'packages' }">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Wat wil je verkopen?</label>
                    <select name="item_type" x-model="selectedType" class="w-full text-xs p-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-purple-500 bg-white">
                        <option value="packages">🧳 Smokkelkoffers (beschikbaar: {{ $character->unopened_bags ?? 0 }})</option>
                        <option value="vault_codes">🔐 Geheime Kluiscodes (beschikbaar: {{ $myVaultCodes->count() }})</option>
                        <option value="masks">🎭 Maskers (beschikbaar: {{ $character->masks ?? 0 }})</option>
                        <option value="stars">⭐ Exclusieve Sterren (beschikbaar: {{ $character->stars ?? 0 }})</option>
                        <option value="cash">💰 Contant Geld (voor Sterren)</option>
                    </select>
                </div>

                <div x-show="selectedType === 'vault_codes'" class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700">Kies jouw kluiscode voucher</label>
                    @if($myVaultCodes->count() > 0)
                    <select name="vault_code_id" class="w-full text-xs p-2.5 rounded-xl border border-slate-200 bg-white">
                        @foreach($myVaultCodes as $code)
                            <option value="{{ $code->id }}">{{ $code->code }} (+€{{ number_format($code->reward_cash) }} bankgeld)</option>
                        @endforeach
                    </select>
                    @else
                    <div class="p-3 bg-amber-50 text-amber-800 border border-amber-200 rounded-xl text-xs">
                        Je hebt momenteel 0 ongekraakte kluiscodes in bezit. Onderschep koffers of kraak de Suikerkluis om kluiscodes te scoren!
                    </div>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Aantal te verkopen
                            <span x-show="selectedType === 'vault_codes'" class="text-[10px] text-purple-600 font-semibold">(1 per voucher)</span>
                        </label>
                        <input type="number" name="amount" min="1" value="1" :readonly="selectedType === 'vault_codes'" required class="w-full text-xs p-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-purple-500" :class="{ 'bg-slate-100 text-slate-500': selectedType === 'vault_codes' }">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Vraagprijs</label>
                        <input type="number" name="price" min="10" value="500" required class="w-full text-xs p-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Valuta waarin koper moet betalen</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50">
                            <input type="radio" name="currency" value="cash" checked class="text-purple-600 focus:ring-purple-500">
                            <span class="text-xs font-bold text-slate-800">Contant Geld (KC)</span>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50">
                            <input type="radio" name="currency" value="stars" class="text-purple-600 focus:ring-purple-500">
                            <span class="text-xs font-bold text-slate-800">Sterren ⭐</span>
                        </label>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button @click="createModalOpen = false" type="button" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Annuleren
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-black text-xs rounded-xl transition shadow-xs">
                        Plaats op Marktplaats
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
