@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-gradient-to-r from-slate-900 to-red-950 rounded-2xl p-6 sm:p-8 text-white relative overflow-hidden">
        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 bg-red-500/20 text-red-300 text-xs font-bold px-3 py-1 rounded-full border border-red-400/20 mb-3">
                <i class="fa-solid fa-crosshairs"></i> Onderwereld Opdrachten
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Liquidatie & Afrekeningen</h1>
            <p class="text-slate-300 text-xs sm:text-sm mt-1 max-w-xl">Huur een huurmoordenaar in of regel een gerichte aanval op rivaliserende patiënten in de kliniek.</p>
        </div>
        <div class="absolute -right-6 -bottom-6 text-white/5 text-9xl font-black select-none pointer-events-none">
            <i class="fa-solid fa-crosshairs"></i>
        </div>
    </div>

    <!-- Stats & Energy Bar -->
    <div class="flex items-center gap-3 bg-white border border-slate-200 rounded-xl px-5 py-3 shadow-xs">
        <i class="fa-solid fa-bolt text-emerald-500"></i>
        <span class="text-xs font-bold text-slate-700">Energie: <span class="text-emerald-700">{{ $character->energy }} / {{ $character->max_energy }}</span></span>
        <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-200">
            <div class="bg-emerald-500 h-full rounded-full" style="width: {{ min(100, round(($character->energy / max(1, $character->max_energy)) * 100)) }}%"></div>
        </div>
        <span class="text-xs font-bold text-slate-500">Cash: <span class="text-slate-800 font-extrabold">{{ number_format($character->cash, 0, ',', '.') }} KC</span></span>
    </div>

    <!-- Contract Order Form -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <h2 class="text-base font-extrabold text-slate-900 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-file-contract text-red-600"></i> Contract Uitzetten
        </h2>

        @if($targets->isEmpty())
        <div class="p-6 text-center text-slate-400 text-sm font-semibold">
            Geen beschikbare doelwitten gevonden die buiten de isoleercel zijn.
        </div>
        @else
        <form action="{{ route('hitman.contract') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Select Target (Searchable Input with Autocomplete List) -->
            <div x-data="{
                search: '',
                selectedId: '',
                selectedName: '',
                openDropdown: false,
                targets: [
                    @foreach($targets as $target)
                    {
                        id: '{{ $target->id }}',
                        name: '{{ addslashes($target->name ?? $target->user->name ?? 'Onbekend') }}',
                        level: {{ $target->level }},
                        respect: {{ $target->respect }},
                        health: {{ $target->health }},
                        avatar: '{{ $target->avatar_url }}'
                    },
                    @endforeach
                ],
                get filtered() {
                    if (!this.search.trim()) return this.targets;
                    return this.targets.filter(t => t.name.toLowerCase().includes(this.search.toLowerCase()));
                },
                select(target) {
                    this.selectedId = target.id;
                    this.selectedName = target.name;
                    this.search = target.name;
                    this.openDropdown = false;
                }
            }" class="relative">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Kies Doelwit (Zoek of Typ Naam)
                </label>
                <input type="hidden" name="target_id" :value="selectedId" required>
                <input type="hidden" name="target_name" :value="search">

                <div class="relative flex items-center">
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 flex items-center pointer-events-none text-red-500 text-sm z-10">
                        <i class="fa-solid fa-crosshairs"></i>
                    </div>
                    <input type="text"
                           x-model="search"
                           @focus="openDropdown = true"
                           @input="openDropdown = true; selectedId = ''"
                           @click.outside="openDropdown = false"
                           required
                           autocomplete="off"
                           placeholder="Typ naam of kies doelwit..."
                           class="w-full bg-slate-50 border border-slate-300 focus:bg-white focus:border-red-500 focus:ring-2 focus:ring-red-100 rounded-xl py-3 text-sm font-bold text-slate-900 focus:outline-none transition tbs-search-input shadow-xs"
                           style="padding-left: 3rem !important; padding-right: 2.5rem !important;">
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 flex items-center pointer-events-none text-slate-400 text-xs z-10">
                        <i class="fa-solid fa-chevron-down transition-transform duration-150" :class="openDropdown ? 'rotate-180' : ''"></i>
                    </div>
                </div>

                <!-- Supplemental Dropdown List -->
                <div x-show="openDropdown" 
                     x-transition
                     class="absolute z-50 left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white rounded-xl shadow-xl border border-slate-200 divide-y divide-slate-100">
                    <template x-for="t in filtered" :key="t.id">
                        <button type="button" 
                                @click="select(t)"
                                class="w-full text-left px-4 py-2.5 hover:bg-red-50 flex items-center justify-between text-xs transition cursor-pointer group">
                            <div class="flex items-center gap-3">
                                <img :src="t.avatar" class="w-8 h-8 rounded-full object-cover border border-slate-200 shadow-xs">
                                <div>
                                    <span class="font-extrabold text-slate-900 group-hover:text-red-700 block" x-text="t.name"></span>
                                    <span class="text-[10px] text-slate-400" x-text="'Lvl ' + t.level + ' • ' + t.respect + ' Respect • HP ' + t.health + '/100'"></span>
                                </div>
                            </div>
                            <span x-show="selectedId === t.id" class="text-red-600 font-bold text-sm">
                                <i class="fa-solid fa-check"></i>
                            </span>
                        </button>
                    </template>
                    <template x-if="filtered.length === 0">
                        <div class="p-3 text-xs text-slate-400 text-center">
                            Geen patiënt gevonden met '<span x-text="search"></span>'
                        </div>
                    </template>
                </div>
            </div>

            <!-- Select Contract Type -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Kies Type Contract</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach($contracts as $contract)
                    <label class="relative flex flex-col justify-between p-4 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-red-300 transition has-[:checked]:border-red-600 has-[:checked]:bg-red-50/20">
                        <input type="radio" name="contract_id" value="{{ $contract['id'] }}" class="peer sr-only" required {{ $loop->first ? 'checked' : '' }}>
                        <div>
                            <div class="text-sm font-extrabold text-slate-900">{{ $contract['name'] }}</div>
                            <div class="text-xs text-slate-500 mt-1">{{ $contract['desc'] }}</div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 text-xs space-y-1">
                            <div class="flex justify-between">
                                <span class="text-slate-400 font-semibold">Prijs:</span>
                                <span class="font-extrabold text-slate-800">{{ number_format($contract['cost'], 0, ',', '.') }} KC</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400 font-semibold">Energie:</span>
                                <span class="font-bold text-amber-600">{{ $contract['energy'] }} EP</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400 font-semibold">Kans:</span>
                                <span class="font-bold text-indigo-600">{{ $contract['rate'] }}%</span>
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            <button type="submit"
                    {{ $character->isImprisoned() ? 'disabled' : '' }}
                    class="w-full py-3.5 px-6 rounded-xl font-extrabold text-sm text-white transition shadow-sm {{ $character->isImprisoned() ? 'bg-slate-300 cursor-not-allowed' : 'bg-red-600 hover:bg-red-700 active:scale-[0.99]' }}">
                <i class="fa-solid fa-crosshairs mr-2"></i> Contract Bevestigen & Uitvoeren
            </button>
        </form>
        @endif
    </div>
</div>
@endsection
