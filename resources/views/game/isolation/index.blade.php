@extends('layouts.app')

@section('content')
<div class="space-y-6">
    
    <!-- Title Card (Clean & Modern) -->
    <div class="rounded-2xl shadow-xs p-6 sm:p-8 text-slate-800 relative overflow-hidden bg-white border border-slate-200">
        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 bg-indigo-50 text-indigo-700 text-xs font-bold px-3 py-1 rounded-full border border-indigo-200 mb-3">
                <i class="fa-solid fa-ghost text-indigo-600"></i> Persoonlijke Bescherming
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Vrijwillige Isolatie</h1>
            <p class="text-slate-500 text-xs sm:text-sm mt-1 max-w-xl leading-relaxed">
                Heb je ruzie met gevaarlijke patiënten op de luchtplaats? Trek je tijdelijk terug in je cel achter slot en grendel zodat niemand je kan aanvallen.
            </p>
        </div>
    </div>

    <!-- Pricing Summary Banner -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4 text-slate-700 shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600 text-lg shrink-0">
                <i class="fa-solid fa-tags"></i>
            </div>
            <div>
                <span class="text-xs font-black uppercase tracking-wider text-purple-700">Vast Tarief per uur</span>
                <p class="text-xs text-slate-500 font-medium">
                    Bescherming kost <span class="font-extrabold text-slate-900">€50.000</span> óf <span class="font-extrabold text-amber-600">25 Sterren</span> per uur.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <span class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-emerald-700 flex items-center gap-1.5">
                <i class="fa-solid fa-wallet text-emerald-600"></i> €{{ number_format($character->cash + $character->bank, 0, ',', '.') }}
            </span>
            <span class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-amber-700 flex items-center gap-1.5">
                <i class="fa-solid fa-star text-amber-500"></i> {{ $character->stars ?? 0 }} Sterren
            </span>
        </div>
    </div>

    <!-- Status Card -->
    @if($isProtected)
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-5 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-lg shrink-0">
                <i class="fa-solid fa-shield-check"></i>
            </div>
            <div>
                <h3 class="text-sm font-extrabold text-emerald-950">Isolatiebescherming Actief!</h3>
                <p class="text-xs text-emerald-800">
                    Je cel is vergrendeld tot <span class="font-bold">{{ $character->protection_until->format('d-m H:i') }}</span> (nog ~{{ $remainingHours }} uur / {{ $remainingMinutes }} min). Andere patiënten kunnen je niet aanvallen op de luchtplaats. Je kunt de bescherming hieronder verlengen!
                </p>
            </div>
        </div>
        <span class="bg-emerald-200/80 text-emerald-900 text-xs font-bold px-3 py-1 rounded-lg shrink-0">
            Veilig
        </span>
    </div>
    @else
    <div class="bg-white border border-slate-200 rounded-xl p-5 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center font-bold text-lg shrink-0">
                <i class="fa-solid fa-door-open"></i>
            </div>
            <div>
                <h3 class="text-sm font-extrabold text-slate-800">Geen Actieve Isolatie</h3>
                <p class="text-xs text-slate-500">
                    Je loopt momenteel vrij rond op de afdeling en kunt worden aangevallen op de luchtplaats.
                </p>
            </div>
        </div>
        <span class="bg-slate-100 text-slate-600 text-xs font-bold px-3 py-1 rounded-lg shrink-0">
            Kwetsbaar
        </span>
    </div>
    @endif

    <!-- Package Selection -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-lock text-indigo-600"></i> Kies de duur van jouw isolatie
            </h2>
            <span class="text-xs text-slate-400 font-medium">Betaal per uur met Geld of Sterren</span>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pt-2">
            @foreach($packages as $pkg)
            @php
                $canAffordCash = ($character->cash + $character->bank) >= $pkg['cost_cash'];
                $canAffordStars = ($character->stars ?? 0) >= $pkg['cost_stars'];
            @endphp
            <div class="p-5 rounded-2xl border border-slate-200 hover:border-indigo-400 bg-slate-50/50 hover:bg-white flex flex-col justify-between gap-4 transition shadow-xs group">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-base font-extrabold text-slate-900 group-hover:text-indigo-600 transition">{{ $pkg['label'] }}</span>
                        <p class="text-[11px] text-slate-500 mt-0.5">Volledige immuniteit op de luchtplaats</p>
                    </div>
                    <span class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold shadow-xs">
                        <i class="fa-solid fa-clock"></i>
                    </span>
                </div>

                <div class="p-3 rounded-xl bg-white border border-slate-100 text-xs space-y-1">
                    <div class="flex items-center justify-between font-bold text-slate-700">
                        <span>Kosten in geld:</span>
                        <span class="text-slate-900 font-black">€{{ number_format($pkg['cost_cash'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between font-bold text-slate-700">
                        <span>Kosten in sterren:</span>
                        <span class="text-amber-500 font-black flex items-center gap-1">
                            <i class="fa-solid fa-star text-[10px]"></i> {{ $pkg['cost_stars'] }} Sterren
                        </span>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-200/80 grid grid-cols-2 gap-2">
                    <!-- Pay with cash -->
                    <form action="{{ route('isolation.activate') }}" method="POST">
                        @csrf
                        <input type="hidden" name="hours" value="{{ $pkg['hours'] }}">
                        <input type="hidden" name="currency" value="cash">
                        <button type="submit" 
                                {{ ! $canAffordCash ? 'disabled' : '' }}
                                title="Betaal met contant/bankgeld (€{{ number_format($pkg['cost_cash'], 0, ',', '.') }})"
                                class="w-full py-2.5 px-3 rounded-xl text-xs font-black transition flex items-center justify-center gap-1.5 {{ $canAffordCash ? 'bg-slate-900 hover:bg-slate-800 text-white cursor-pointer shadow-xs active:scale-[0.98]' : 'bg-slate-200 text-slate-400 cursor-not-allowed' }}">
                            <i class="fa-solid fa-money-bill-wave"></i>
                            <span>€{{ $pkg['hours'] * 50 }}k</span>
                        </button>
                    </form>

                    <!-- Pay with stars -->
                    <form action="{{ route('isolation.activate') }}" method="POST">
                        @csrf
                        <input type="hidden" name="hours" value="{{ $pkg['hours'] }}">
                        <input type="hidden" name="currency" value="stars">
                        <button type="submit" 
                                {{ ! $canAffordStars ? 'disabled' : '' }}
                                title="Betaal met sterren ({{ $pkg['cost_stars'] }} Sterren)"
                                class="w-full py-2.5 px-3 rounded-xl text-xs font-black transition flex items-center justify-center gap-1.5 {{ $canAffordStars ? 'bg-amber-500 hover:bg-amber-600 text-white cursor-pointer shadow-xs active:scale-[0.98]' : 'bg-slate-200 dark:bg-slate-800 text-slate-400 cursor-not-allowed' }}">
                            <i class="fa-solid fa-star text-amber-200"></i>
                            <span>{{ $pkg['cost_stars'] }} Sterren</span>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Custom Hours Card -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-6 space-y-4" x-data="{ hours: 1 }">
        <div class="flex items-center gap-2 text-sm font-extrabold text-slate-900 uppercase tracking-wider">
            <i class="fa-solid fa-sliders text-indigo-600"></i>
            <span>Of kies zelf het aantal uren isolatie</span>
        </div>
        <p class="text-xs text-slate-500">
            Kies precies hoeveel uur je achter slot en grendel wilt blijven (1 tot 24 uur). Het tarief is €50.000 of 25 Sterren per uur.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center bg-slate-50 p-4 rounded-xl border border-slate-200">
            <div class="md:col-span-4 space-y-1">
                <label class="block text-xs font-bold text-slate-700">Aantal Uren (1 - 24)</label>
                <input type="number" min="1" max="24" x-model.number="hours" 
                       class="w-full p-2.5 bg-white border border-slate-300 rounded-xl text-sm font-extrabold text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="md:col-span-4 space-y-1">
                <span class="block text-xs font-bold text-slate-700">Totaal Prijs</span>
                <div class="text-xs space-y-0.5">
                    <div class="font-extrabold text-slate-900 flex items-center gap-1.5">
                        <i class="fa-solid fa-coins text-emerald-600"></i>
                        <span>€<span x-text="(hours * 50000).toLocaleString('nl-NL')"></span></span>
                    </div>
                    <div class="font-extrabold text-amber-600 flex items-center gap-1.5">
                        <i class="fa-solid fa-star text-amber-500"></i>
                        <span><span x-text="hours * 25"></span> Sterren</span>
                    </div>
                </div>
            </div>

            <div class="md:col-span-4 flex items-center gap-2">
                <form action="{{ route('isolation.activate') }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="hours" :value="hours">
                    <input type="hidden" name="currency" value="cash">
                    <button type="submit" class="w-full py-2.5 px-3 bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs rounded-xl transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-money-bill-wave"></i>
                        <span>Betaal Geld</span>
                    </button>
                </form>

                <form action="{{ route('isolation.activate') }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="hours" :value="hours">
                    <input type="hidden" name="currency" value="stars">
                    <button type="submit" class="w-full py-2.5 px-3 bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs rounded-xl transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-star"></i>
                        <span>Betaal Sterren</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
