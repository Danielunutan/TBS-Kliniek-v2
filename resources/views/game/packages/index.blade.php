@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Top Hero Banner (Clean TBS White Style) -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
            <div>
                <div class="inline-flex items-center gap-2 bg-purple-50 text-purple-700 text-xs font-bold px-3 py-1 rounded-full border border-purple-200 mb-3">
                    <i class="fa-solid fa-briefcase"></i> Smokkelkoffers & Pakketten
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Koffers & Pakketjes</h1>
                <p class="text-slate-500 text-xs sm:text-sm mt-1 max-w-xl leading-relaxed">
                    Onderschep pakketjes in de wandelgangen of scoor smokkelkoffers vol wit bankgeld, maskers, sterren en geheime kluiscodes!
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="px-3.5 py-2 rounded-xl bg-amber-50 border border-amber-200 text-xs font-bold text-amber-800 flex items-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-briefcase text-amber-600"></i> {{ $character->unopened_bags ?? 0 }} Koffers
                </span>
                <span class="px-3.5 py-2 rounded-xl bg-purple-50 border border-purple-200 text-xs font-bold text-purple-700 flex items-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-crown text-purple-600"></i> {{ $character->xxl_cases ?? 0 }} XXL Koffers
                </span>
            </div>
        </div>
    </div>

    <!-- MAIN TWO COLUMN SECTION -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Card: Koffers & XXL Koffers -->
        <div class="lg:col-span-5 bg-white border border-slate-200 rounded-xl p-6 shadow-xs flex flex-col justify-between space-y-5">
            <div>
                <div class="flex items-center gap-2 text-base font-extrabold text-slate-900 mb-1">
                    <i class="fa-solid fa-briefcase text-purple-600"></i>
                    <span>Smokkelkoffers Openen</span>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed mb-4">
                    Open je verzamelde koffers voor direct wit bankgeld, maskers en kluiscodes.
                </p>

                <!-- Current Bag Inventory Status -->
                <div class="pt-4 border-t border-slate-100">
                    @if(($character->unopened_bags ?? 0) <= 0)
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-center">
                            <p class="text-slate-500 font-bold text-xs">
                                Je hebt momenteel geen standaard koffers. Onderschep er een hieronder!
                            </p>
                        </div>
                    @else
                        <div class="space-y-3">
                            <div class="flex items-center justify-between bg-purple-50/70 border border-purple-200 rounded-xl p-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-xl shrink-0 font-black">
                                        <i class="fa-solid fa-briefcase"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs font-black text-slate-900">Je bezit {{ $character->unopened_bags }} koffer(s)</div>
                                        <div class="text-[10px] text-purple-600 font-bold">Kies: kraken voor buit óf verkopen voor gegarandeerd bankgeld!</div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <form action="{{ route('packages.open-bag') }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full py-2.5 px-3 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-[0.99] text-white font-extrabold text-xs uppercase tracking-wider transition shadow-xs cursor-pointer flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-unlock"></i> Open Koffer
                                    </button>
                                </form>
                                <form action="{{ route('packages.sell-bag') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="amount" value="1">
                                    <button type="submit" 
                                            class="w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white font-extrabold text-xs uppercase tracking-wider transition shadow-xs cursor-pointer flex items-center justify-center gap-1.5"
                                            title="Verkoop 1 koffer direct voor €2.500 wit bankgeld">
                                        <i class="fa-solid fa-sack-dollar"></i> Verkoop (€2.500)
                                    </button>
                                </form>
                            </div>

                            @if(($character->unopened_bags ?? 0) > 1)
                            <form action="{{ route('packages.sell-bag') }}" method="POST">
                                @csrf
                                <input type="hidden" name="amount" value="{{ $character->unopened_bags }}">
                                <button type="submit" 
                                        class="w-full py-2 px-3 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-[11px] font-extrabold transition flex items-center justify-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-money-bill-transfer"></i> Verkoop Alle {{ $character->unopened_bags }} Koffers (€{{ number_format($character->unopened_bags * 2500, 0, ',', '.') }} bankgeld)
                                </button>
                            </form>
                            @endif

                            <div class="pt-1 text-center">
                                <a href="{{ route('marketplace.index') }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-500 hover:text-purple-600 transition">
                                    <i class="fa-solid fa-scale-balanced"></i> Of verkoop aan spelers op Marktplaats &rarr;
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- XXL KOFFERS KAART (Grotere Prijzen!) -->
            <div class="pt-4 border-t border-slate-100 bg-amber-50/50 p-4 rounded-xl border border-amber-200">
                <div class="flex items-center justify-between gap-3 mb-1.5">
                    <span class="text-xs font-black text-amber-900 flex items-center gap-1.5">
                        <i class="fa-solid fa-crown text-amber-600"></i> XXL Koffers
                    </span>
                    <span class="text-[11px] font-extrabold text-amber-800 bg-amber-100 border border-amber-300 px-2.5 py-0.5 rounded-full">
                        {{ $character->xxl_cases ?? 0 }} in bezit
                    </span>
                </div>
                <p class="text-[11px] text-slate-600 mb-3 leading-relaxed">
                    Grotere prijzen: tot wel <strong>€75.000 cash</strong> op je bank, <strong>25 Sterren</strong>, <strong>12 Maskers</strong> en geheime kluiscodes!
                </p>
                <div class="space-y-2">
                    @if(($character->xxl_cases ?? 0) > 0)
                    <form action="{{ route('packages.open-xxl') }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full py-2.5 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 active:scale-95 text-white font-black text-xs uppercase tracking-wider transition shadow-sm cursor-pointer flex items-center justify-center gap-2">
                            <i class="fa-solid fa-key"></i> Kraak XXL Koffer
                        </button>
                    </form>
                    @endif
                    <form action="{{ route('packages.buy-xxl') }}" method="POST">
                        @csrf
                        <button type="submit" 
                                @if(($character->stars ?? 0) < 100) disabled @endif
                                class="w-full py-2 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed text-white font-extrabold text-xs transition cursor-pointer flex items-center justify-center gap-2 shadow-xs">
                            <i class="fa-solid fa-star text-amber-400"></i> Koop XXL Koffer (100 Sterren)
                        </button>
                    </form>
                </div>
            </div>

            <!-- Buy Pack for 150 Stars -->
            <div class="pt-4 border-t border-slate-100 bg-slate-50 p-4 rounded-xl border border-slate-200">
                <div class="flex items-center justify-between gap-3 mb-1.5">
                    <span class="text-xs font-black text-slate-900">Exclusief Master Pakket</span>
                    <span class="text-[11px] font-extrabold text-amber-700 bg-amber-100 border border-amber-300 px-2.5 py-0.5 rounded-full inline-flex items-center gap-1">
                        <span>150 Sterren</span>
                        <i class="fa-solid fa-star text-amber-600 text-[10px]"></i>
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 mb-3">
                    Geen zin om te wachten? Koop direct een exclusief pakket vol wit bankgeld, maskers en zeldzame kluiscodes.
                </p>
                <form action="{{ route('shop.buy-star-item') }}" method="POST">
                    @csrf
                    <input type="hidden" name="item_type" value="package_150">
                    <button type="submit" 
                            @if(($character->stars ?? 0) < 150) disabled @endif
                            class="w-full py-2.5 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-xs transition cursor-pointer flex items-center justify-center gap-2 shadow-xs">
                        <i class="fa-solid fa-star text-amber-300"></i>
                        <span>Koop Pakket (150 Sterren)</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Card: Koffer logs Table -->
        <div class="lg:col-span-7 bg-white border border-slate-200 rounded-xl p-6 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2 text-base font-extrabold text-slate-900 mb-1">
                    <i class="fa-solid fa-clock-rotate-left text-purple-600"></i>
                    <span>Koffer logs</span>
                </div>
                <p class="text-xs text-slate-500 mb-4">
                    Hieronder zie je al je recent geopende koffers en beloningen.
                </p>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2">
                                <th class="py-2.5 px-2">ID</th>
                                <th class="py-2.5 px-2">INHOUD</th>
                                <th class="py-2.5 px-2 text-right">DATUM</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-medium">
                            @forelse($kofferLogs ?? [] as $log)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3 px-2 font-mono font-bold text-slate-400">#{{ 150000 + $log->id }}</td>
                                    <td class="py-3 px-2 text-slate-800 flex items-center gap-2">
                                        <i class="fa-solid fa-briefcase text-purple-600 text-xs"></i>
                                        <span class="font-bold">{{ $log->loot_summary }}</span>
                                    </td>
                                    <td class="py-3 px-2 text-right font-mono text-[11px] text-slate-400 whitespace-nowrap">
                                        {{ $log->created_at->format('d-m-Y H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-slate-400 text-xs italic">
                                        Nog geen koffers geopend. Open je eerste koffer hiernaast!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Intercept Corridor Missions Grid (Site Activity to drop koffers & kluizen) -->
    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-truck-moving text-purple-600"></i>
                    <span>Pakketjes & Koffers Onderscheppen in de Wandelgangen</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Door acties op de site te doen ontvang je af en toe een random koffer of kluiscode!
                </p>
            </div>
            <div class="flex items-center gap-3 text-xs font-bold text-slate-600">
                <span>Energie: <strong class="text-emerald-700">{{ $character->energy }} / {{ $character->max_energy }}</strong></span>
            </div>
        </div>

        @php
            $pkgCooldown = $character->cooldownRemaining('package');
        @endphp

        @if($pkgCooldown > 0)
            <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 flex flex-wrap items-center justify-between gap-3">
                <span class="text-xs font-bold text-amber-900">
                    <i class="fa-solid fa-clock mr-1 text-amber-600"></i> Cooldown actief: nog <strong>{{ gmdate('i:s', $pkgCooldown) }}</strong>
                </span>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="window.tbsOpenSponsorAd('cooldown')" class="bg-purple-600 hover:bg-purple-700 active:scale-95 text-white text-[11px] font-black px-3 py-1.5 rounded-lg transition shadow-xs cursor-pointer inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-tv text-white text-[10px]"></i>
                        <span>Kijk Ad (Reset)</span>
                    </button>
                    <form action="{{ route('cooldown.skip') }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="package">
                        <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white text-[11px] font-black px-3 py-1.5 rounded-lg transition shadow-xs cursor-pointer inline-flex items-center gap-1">
                            <i class="fa-solid fa-star text-white text-[10px]"></i>
                            <span>{{ $packageSkipCost }} Sterren ⭐</span>
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($packages as $pkg)
            @php
                $hasTokenCost = ($pkg['token_cost'] ?? 0) > 0;
                $canAffordTokens = !$hasTokenCost || ($character->vault_tokens >= $pkg['token_cost']);
                $canAffordEnergy = $character->energy >= $pkg['energy'];
                $isDisabled = $character->isImprisoned() || !$canAffordEnergy || !$canAffordTokens || $pkgCooldown > 0;
            @endphp
            <div class="bg-slate-50 hover:bg-white border border-slate-200 hover:border-purple-300 rounded-xl p-4 flex flex-col justify-between gap-3 transition shadow-xs">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center shrink-0">
                        <i class="fa-solid {{ $pkg['icon'] }}"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-extrabold text-slate-900">{{ $pkg['name'] }}</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ $pkg['desc'] }}</p>
                    </div>
                </div>

                <div class="text-[11px] space-y-1 pt-2 border-t border-slate-200/80 text-slate-600">
                    <div class="flex justify-between">
                        <span>Buit:</span>
                        <span class="font-bold text-emerald-700">€{{ number_format($pkg['min'], 0, ',', '.') }}–€{{ number_format($pkg['max'], 0, ',', '.') }} contant</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Kans op slagen:</span>
                        <span class="font-bold text-slate-800">{{ $pkg['rate'] }}%</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Cooldown:</span>
                        <span class="font-bold text-amber-600"><i class="fa-solid fa-clock text-[10px] mr-1"></i>{{ gmdate('i:s', $pkg['cooldown'] ?? 180) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Koffer Drop:</span>
                        <span class="font-bold text-purple-600">25% kans</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Kluiscode Drop:</span>
                        <span class="font-bold text-indigo-600">Via Bericht</span>
                    </div>
                </div>

                <form action="{{ route('packages.intercept') }}" method="POST">
                    @csrf
                    <input type="hidden" name="package_id" value="{{ $pkg['id'] }}">
                    <button type="submit" 
                            {{ $isDisabled ? 'disabled' : '' }}
                            class="w-full py-2 px-3 rounded-lg text-xs font-bold transition cursor-pointer {{ $isDisabled ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-slate-900 hover:bg-slate-800 text-white shadow-xs' }}">
                        Onderscheppen ({{ $pkg['energy'] }} EP)
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
