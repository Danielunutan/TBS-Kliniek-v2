@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Top Hero Banner -->
    <div class="rounded-2xl shadow-xs p-6 sm:p-8 text-slate-800 relative overflow-hidden bg-white border border-slate-200">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 bg-purple-50 text-purple-700 text-xs font-bold px-3 py-1 rounded-full border border-purple-200 mb-2">
                    <i class="fa-solid fa-box-open text-purple-600"></i> Zwarte Markt & Productie
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Smokkel & Handel</h1>
                <p class="text-slate-500 text-xs sm:text-sm mt-1 max-w-xl leading-relaxed">
                    Draai peuken, druk medicatie of smokkel telefoons de kliniek binnen. Verkoop je voorraad aan mede-patiënten voor contant geld, respect en 5% rank XP per deal!
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-emerald-700 flex items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-bolt text-emerald-600"></i> {{ $character->energy }} / {{ $character->max_energy }} Energie
                </span>
                <span class="px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-amber-700 flex items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-star text-amber-500"></i> {{ $character->stars ?? 0 }} Sterren
                </span>
            </div>
        </div>
    </div>

    <!-- Inventory Stock Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white border border-slate-200 rounded-xl p-5 flex items-center gap-4 shadow-xs">
            <span class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl border border-amber-200 shrink-0">
                <i class="fa-solid fa-smoking"></i>
            </span>
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Voorraad Sigaretten</span>
                <span class="text-xl font-extrabold text-slate-900">{{ $cigarettes }} stuks</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-5 flex items-center gap-4 shadow-xs">
            <span class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl border border-purple-200 shrink-0">
                <i class="fa-solid fa-pills"></i>
            </span>
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Voorraad Ritalin / Medicatie</span>
                <span class="text-xl font-extrabold text-slate-900">{{ $ritalin }} strips</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-5 flex items-center gap-4 shadow-xs">
            <span class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl border border-indigo-200 shrink-0">
                <i class="fa-solid fa-mobile-screen"></i>
            </span>
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Voorraad Telefoons</span>
                <span class="text-xl font-extrabold text-slate-900">{{ $phones }} toestellen</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left Column: Produceren (Productiekelder) -->
        <div class="lg:col-span-6 bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden flex flex-col justify-between">
            <div>
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-industry text-purple-600 text-sm"></i>
                        <h2 class="text-slate-900 font-extrabold text-sm uppercase tracking-wide">Productiekelder</h2>
                    </div>
                    <span class="text-xs font-bold text-slate-500">
                        Energie: <strong class="text-emerald-700">{{ $character->energy }} / {{ $character->max_energy }}</strong>
                    </span>
                </div>
                
                <div class="p-5 space-y-3.5">
                    <!-- 1. Sigaretten (5 min cooldown) -->
                    @php
                        $sigCooldown = $character->cooldownRemaining('produce_sigaretten');
                    @endphp
                    <div class="p-4 border border-slate-200 rounded-xl bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-sm shrink-0 font-bold">
                                <i class="fa-solid fa-smoking"></i>
                            </span>
                            <div>
                                <span class="text-[11px] text-slate-500 font-medium inline-flex items-center gap-1.5 flex-wrap">
                                    <span>Kost 5 Energie</span> &bull; <span>+25 XP</span> &bull; <span><i class="fa-solid fa-stopwatch text-amber-500"></i> 5 min cooldown</span>
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @if($sigCooldown > 0)
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[11px] font-black text-amber-700 bg-amber-100 px-2 py-1 rounded-lg">
                                        <i class="fa-regular fa-clock mr-1"></i>{{ gmdate('i:s', $sigCooldown) }}
                                    </span>
                                    <button type="button" onclick="window.tbsOpenSponsorAd('cooldown')" title="Kijk Ad (Reset Alle Cooldowns)" class="px-2.5 py-1 bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-black rounded-lg text-xs shadow-xs transition cursor-pointer inline-flex items-center gap-1">
                                        <i class="fa-solid fa-tv text-[10px]"></i>
                                        <span>Ad</span>
                                    </button>
                                    <form action="{{ route('smuggle.skip-cooldown') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="action" value="produce_sigaretten">
                                        <button type="submit" 
                                                title="Sla cooldown over voor 1 Ster" 
                                                class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white font-black rounded-lg text-xs shadow-xs transition cursor-pointer inline-flex items-center gap-1">
                                            <span>1</span> <i class="fa-solid fa-star text-[10px]"></i>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <form action="{{ route('smuggle.produce') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="type" value="sigaretten">
                                    <button type="submit" 
                                            @if($character->energy < 5) disabled @endif
                                            class="bg-slate-900 hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-bold px-4 py-2 rounded-lg transition shadow-xs cursor-pointer">
                                        Produceren
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <!-- 2. Ritalin (15 min cooldown) -->
                    @php
                        $ritCooldown = $character->cooldownRemaining('produce_ritalin');
                    @endphp
                    <div class="p-4 border border-slate-200 rounded-xl bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-sm shrink-0 font-bold">
                                <i class="fa-solid fa-pills"></i>
                            </span>
                            <div>
                                <h4 class="font-extrabold text-slate-900 text-xs">3x Ritalin Strips Onderscheppen</h4>
                                <span class="text-[11px] text-slate-500 font-medium inline-flex items-center gap-1.5 flex-wrap">
                                    <span>Kost 10 Energie</span> &bull; <span>+50 XP</span> &bull; <span><i class="fa-solid fa-stopwatch text-amber-500"></i> 15 min cooldown</span>
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @if($ritCooldown > 0)
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[11px] font-black text-amber-700 bg-amber-100 px-2 py-1 rounded-lg">
                                        <i class="fa-regular fa-clock mr-1"></i>{{ gmdate('i:s', $ritCooldown) }}
                                    </span>
                                    <button type="button" onclick="window.tbsOpenSponsorAd('cooldown')" title="Kijk Ad (Reset Alle Cooldowns)" class="px-2.5 py-1 bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-black rounded-lg text-xs shadow-xs transition cursor-pointer inline-flex items-center gap-1">
                                        <i class="fa-solid fa-tv text-[10px]"></i>
                                        <span>Ad</span>
                                    </button>
                                    <form action="{{ route('smuggle.skip-cooldown') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="action" value="produce_ritalin">
                                        <button type="submit" 
                                                title="Sla cooldown over voor 2 Sterren" 
                                                class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white font-black rounded-lg text-xs shadow-xs transition cursor-pointer inline-flex items-center gap-1">
                                            <span>2</span> <i class="fa-solid fa-star text-[10px]"></i>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <form action="{{ route('smuggle.produce') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="type" value="ritalin">
                                    <button type="submit" 
                                            @if($character->energy < 10) disabled @endif
                                            class="bg-slate-900 hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-bold px-4 py-2 rounded-lg transition shadow-xs cursor-pointer">
                                        Produceren
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <!-- 3. Telefoon (30 min cooldown) -->
                    @php
                        $phoneCooldown = max($character->cooldownRemaining('produce_telefoon'), $character->cooldownRemaining('produce_phone'));
                    @endphp
                    <div class="p-4 border border-slate-200 rounded-xl bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm shrink-0 font-bold">
                                <i class="fa-solid fa-mobile-screen"></i>
                            </span>
                            <div>
                                <h4 class="font-extrabold text-slate-900 text-xs">1x Mini Telefoon Binnensmokkelen</h4>
                                <span class="text-[11px] text-slate-500 font-medium inline-flex items-center gap-1.5 flex-wrap">
                                    <span>Kost 20 Energie</span> &bull; <span>+120 XP</span> &bull; <span><i class="fa-solid fa-stopwatch text-amber-500"></i> 30 min cooldown</span>
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @if($phoneCooldown > 0)
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[11px] font-black text-amber-700 bg-amber-100 px-2 py-1 rounded-lg">
                                        <i class="fa-regular fa-clock mr-1"></i>{{ gmdate('i:s', $phoneCooldown) }}
                                    </span>
                                    <button type="button" onclick="window.tbsOpenSponsorAd('cooldown')" title="Kijk Ad (Reset Alle Cooldowns)" class="px-2.5 py-1 bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-black rounded-lg text-xs shadow-xs transition cursor-pointer inline-flex items-center gap-1">
                                        <i class="fa-solid fa-tv text-[10px]"></i>
                                        <span>Ad</span>
                                    </button>
                                    <form action="{{ route('smuggle.skip-cooldown') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="action" value="produce_telefoon">
                                        <button type="submit" 
                                                title="Sla cooldown over voor 3 Sterren" 
                                                class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white font-black rounded-lg text-xs shadow-xs transition cursor-pointer inline-flex items-center gap-1">
                                            <span>3</span> <i class="fa-solid fa-star text-[10px]"></i>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <form action="{{ route('smuggle.produce') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="type" value="telefoon">
                                    <button type="submit" 
                                            @if($character->energy < 20) disabled @endif
                                            class="bg-slate-900 hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-bold px-4 py-2 rounded-lg transition shadow-xs cursor-pointer">
                                        Produceren
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-100 text-xs text-slate-500 text-center font-medium">
                Cooldowns verlagen de belasting op de kliniek. Verwijder de cooldown direct met Sterren!
            </div>
        </div>

        <!-- Right Column: Verkopen Card -->
        <div class="lg:col-span-6 bg-white border border-slate-200 rounded-xl p-6 shadow-xs flex flex-col justify-between space-y-6">
            <div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2 text-sm font-extrabold text-slate-900 uppercase tracking-wide">
                        <i class="fa-solid fa-certificate text-purple-600"></i>
                        <span>Verkopen aan Patiënten</span>
                    </div>
                    <span class="text-xs font-bold text-purple-700 bg-purple-50 border border-purple-200 px-2.5 py-1 rounded-full">
                        +5% Rank XP per deal
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-3 leading-relaxed">
                    Voor elke verkoop pak je <strong>5% Rank XP</strong>. Hoe meer deals je sluit met andere patiënten, hoe sneller je rank stijgt!
                </p>

                <!-- Deals List -->
                <div class="mt-5 space-y-3.5">
                    @foreach($orders as $order)
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-4 transition hover:border-purple-300">
                        <!-- Buyer avatar and deal details -->
                        <div class="flex items-center gap-3.5 min-w-0">
                            <img src="{{ $order['avatar'] }}" alt="{{ $order['buyer'] }}" 
                                 onerror="this.onerror=null; this.src='{{ asset('assets/characters/henkie.jpg') }}';"
                                 class="w-12 h-12 rounded-full object-cover border-2 border-purple-200 shrink-0 shadow-xs ring-2 ring-purple-50">
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-slate-900 truncate">
                                    {{ $order['amount'] }} {{ strtolower($order['item_name']) }} voor <span class="text-emerald-700 font-extrabold">€{{ number_format($order['reward_cash'], 0, ',', '.') }}</span>
                                </div>
                                <div class="text-xs text-slate-500 font-medium flex items-center gap-2 mt-0.5">
                                    <span>Koper: <strong class="text-slate-700">{{ $order['buyer'] }}</strong></span>
                                    <span>&bull;</span>
                                    <span class="text-purple-700 font-bold">+{{ $order['reward_respect'] }} Respect</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action buttons: Verkopen & Weigeren -->
                        <div class="flex flex-col gap-1.5 shrink-0 w-28">
                            <!-- Verkopen -->
                            <form action="{{ route('smuggle.sell') }}" method="POST">
                                @csrf
                                <input type="hidden" name="order_id" value="{{ $order['id'] }}">
                                <button type="submit" 
                                        @if(!$order['can_fulfill']) disabled @endif
                                        class="w-full py-2 px-3 rounded-lg bg-purple-600 hover:bg-purple-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-xs uppercase tracking-wider transition shadow-xs cursor-pointer flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-box-archive text-[10px]"></i>
                                    <span>Verkopen</span>
                                </button>
                            </form>

                            <!-- Weigeren -->
                            <form action="{{ route('smuggle.reject') }}" method="POST"
                                  data-confirm="Weet je zeker dat je deze smokkelorder wilt weigeren?"
                                  data-confirm-title="Order Weigeren"
                                  data-confirm-btn="Weigeren"
                                  data-confirm-type="warning">
                                @csrf
                                <input type="hidden" name="order_id" value="{{ $order['id'] }}">
                                <button type="submit" 
                                        class="w-full py-1 px-3 rounded-lg bg-white hover:bg-slate-100 border border-slate-300 text-slate-600 hover:text-slate-900 font-semibold text-xs transition cursor-pointer flex items-center justify-center gap-1">
                                    <i class="fa-solid fa-xmark text-slate-400 text-[10px]"></i>
                                    <span>Weigeren</span>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 text-xs text-slate-400 flex items-center justify-between">
                <span>Direct na elke deal meldt zich automatisch een nieuwe koper!</span>
                <span class="text-purple-700 font-bold">+5% Rank XP</span>
            </div>
        </div>

    </div>

</div>
@endsection
