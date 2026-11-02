@extends('layouts.app')

@section('title', '🖼️ Profielkaders Overzicht - TBS Kliniek')

@section('content')
<div class="space-y-6" x-data="{ sortMode: @js($sort ?? 'event'), filterMode: @js($filter ?? 'all') }">
    <!-- Hero Header Banner -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 p-6 sm:p-7 text-slate-800 dark:text-slate-100 shadow-xs border border-slate-200 dark:border-slate-800 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-amber-500/10 dark:bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-13 h-13 rounded-2xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800/60 flex items-center justify-center text-2xl text-amber-600 dark:text-amber-400 shadow-2xs shrink-0">
                    <i class="fa-solid fa-id-badge"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <span class="text-[10px] font-black uppercase tracking-widest text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/80 px-2.5 py-0.5 rounded-md border border-amber-200 dark:border-amber-800/60">
                            🖼️ Collectie &amp; Catalogus
                        </span>
                        <span class="text-[10px] font-bold text-emerald-800 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-0.5 rounded-md border border-emerald-200 dark:border-emerald-800/60">
                            ♾️ Geclaimde Kaders Blijven Voor Altijd Behouden
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        Alle Profielkaders van het Spel
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-2xl leading-relaxed">
                        Bekijk in één oogopslag alle profielkaders van TBS Kliniek. Elk kader is duidelijk gelabeld als
                        <strong class="text-emerald-600 dark:text-emerald-400">Haalbaar</strong> of
                        <strong class="text-rose-600 dark:text-rose-400">Niet haalbaar (Niet meer haalbaar)</strong>.
                        Geclaimde kaders en event-items zijn permanente verzamelobjecten die nooit worden verwijderd!
                    </p>
                </div>
            </div>

            <!-- Summary Counters -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 shrink-0">
                <div class="bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-center">
                    <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block">Totaal Kaders</span>
                    <span class="text-base font-black text-slate-900 dark:text-white font-mono">{{ count($allBorders) }}</span>
                </div>
                <div class="bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl px-3.5 py-2.5 text-center">
                    <span class="text-[9px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-400 block">Haalbaar</span>
                    <span class="text-base font-black text-emerald-700 dark:text-emerald-300 font-mono">{{ count($obtainableBorders) }}</span>
                </div>
                <div class="bg-rose-50/70 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-xl px-3.5 py-2.5 text-center">
                    <span class="text-[9px] font-black uppercase tracking-wider text-rose-700 dark:text-rose-400 block">Niet Haalbaar</span>
                    <span class="text-base font-black text-rose-700 dark:text-rose-300 font-mono">{{ count($retiredBorders) }}</span>
                </div>
                <div class="bg-amber-50/70 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-xl px-3.5 py-2.5 text-center">
                    <span class="text-[9px] font-black uppercase tracking-wider text-amber-700 dark:text-amber-400 block">In Jouw Bezit</span>
                    <span class="text-base font-black text-amber-700 dark:text-amber-300 font-mono">{{ count($unlockedBorders) }} / {{ count($allBorders) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Sub-Navigation Tabs Bar -->
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-3">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('halloween.quests') }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 shadow-2xs">
                <i class="fa-solid fa-leaf text-amber-500"></i>
                <span>Herfstoogst Quests</span>
            </a>
            <a href="{{ route('halloween.shop') }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 shadow-2xs">
                <i class="fa-solid fa-store text-amber-500"></i>
                <span>Herfstoogst Winkel</span>
            </a>
            <a href="{{ route('borders.index') }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-amber-600 text-white shadow-xs">
                <i class="fa-solid fa-id-badge"></i>
                <span>Profielkaders Overzicht</span>
            </a>
        </div>

        @if($character->equipped_border)
            <form action="{{ route('halloween.equip-border') }}" method="POST">
                @csrf
                <input type="hidden" name="border_key" value="none">
                <button type="submit" @disabled($character->isImprisoned() || $character->isComatose()) class="px-3.5 py-2 rounded-xl text-xs font-bold bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60 hover:bg-rose-100 transition cursor-pointer flex items-center gap-1.5 disabled:opacity-50">
                    <i class="fa-solid fa-xmark"></i>
                    <span>Actief Kader Uitschakelen</span>
                </button>
            </form>
        @endif
    </div>

    <!-- Sorting & Filtering Controls Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Sort Mode Selector: Per Event vs Per Status -->
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-[11px] font-black uppercase tracking-wider text-slate-400 mr-1 flex items-center gap-1.5">
                <i class="fa-solid fa-layer-group text-amber-500"></i> Sorteer Overzicht:
            </span>
            <button type="button"
                    @click="sortMode = 'event'"
                    :class="sortMode === 'event' ? 'bg-amber-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                    class="px-3.5 py-2 rounded-xl text-xs font-black transition cursor-pointer flex items-center gap-1.5">
                <i class="fa-solid fa-calendar-star"></i>
                <span>Sorteer per Event</span>
            </button>
            <button type="button"
                    @click="sortMode = 'status'"
                    :class="sortMode === 'status' ? 'bg-amber-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                    class="px-3.5 py-2 rounded-xl text-xs font-black transition cursor-pointer flex items-center gap-1.5">
                <i class="fa-solid fa-circle-check"></i>
                <span>Sorteer per Status (Haalbaar / Niet haalbaar)</span>
            </button>
        </div>

        <!-- Filter Buttons -->
        <div class="flex flex-wrap items-center gap-1.5">
            <span class="text-[11px] font-black uppercase tracking-wider text-slate-400 mr-1 flex items-center gap-1">
                <i class="fa-solid fa-filter text-slate-400"></i> Filter:
            </span>
            <button type="button"
                    @click="filterMode = 'all'"
                    :class="filterMode === 'all' ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
                    class="px-3 py-1.5 rounded-lg text-[11px] font-black transition cursor-pointer">
                Alle ({{ count($allBorders) }})
            </button>
            <button type="button"
                    @click="filterMode = 'obtainable'"
                    :class="filterMode === 'obtainable' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60'"
                    class="px-3 py-1.5 rounded-lg text-[11px] font-black transition cursor-pointer">
                ✅ Haalbaar ({{ count($obtainableBorders) }})
            </button>
            <button type="button"
                    @click="filterMode = 'unobtainable'"
                    :class="filterMode === 'unobtainable' ? 'bg-rose-600 text-white' : 'bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60'"
                    class="px-3 py-1.5 rounded-lg text-[11px] font-black transition cursor-pointer">
                🔒 Niet haalbaar ({{ count($retiredBorders) }})
            </button>
            <button type="button"
                    @click="filterMode = 'owned'"
                    :class="filterMode === 'owned' ? 'bg-amber-500 text-slate-950' : 'bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60'"
                    class="px-3 py-1.5 rounded-lg text-[11px] font-black transition cursor-pointer">
                ⭐ In Bezit ({{ count($unlockedBorders) }})
            </button>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- SECTION A: HERFSTOOGST EVENT 2026 / STATUS: HAALBAAR                -->
    <!-- =================================================================== -->
    <div x-show="filterMode !== 'unobtainable'" class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-base font-bold border border-emerald-200 dark:border-emerald-800/60">
                    <span x-show="sortMode === 'event'">🍂</span>
                    <i x-show="sortMode === 'status'" class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <h2 class="text-sm sm:text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <span x-text="sortMode === 'event' ? '🍂 Event: De Gouden Herfstoogst 2026' : '✅ Status: Haalbaar (Nu Verkrijgbaar)'">
                            🍂 Event: De Gouden Herfstoogst 2026
                        </span>
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                            Haalbaar
                        </span>
                    </h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        Deze profielkaders zijn momenteel actief te verdienen en vrij te spelen in het Herfstoogst-event.
                    </p>
                </div>
            </div>
            <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400">
                {{ count($obtainableBorders) }} Profielkaders Haalbaar
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($obtainableBorders as $border)
                @php
                    $isOwned = $character->hasBorder($border['key']);
                    $isEquipped = $character->equipped_border === $border['key'];
                    $canAfford = ($character->pumpkin_seeds ?? 0) >= $border['price_seeds'];
                @endphp
                <div x-show="filterMode !== 'owned' || {{ $isOwned ? 'true' : 'false' }}"
                     class="bg-white dark:bg-slate-900 rounded-2xl border {{ $isEquipped ? 'border-amber-500 ring-2 ring-amber-500/25' : 'border-slate-200/90 dark:border-slate-800' }} shadow-xs overflow-hidden flex flex-col justify-between hover:border-amber-400 dark:hover:border-amber-600/60 transition">
                    <!-- Card Header -->
                    <div class="p-3.5 bg-slate-50/90 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between gap-2">
                        <span class="text-xs font-black text-slate-900 dark:text-white truncate">{{ $border['emoji'] }} {{ $border['name'] }}</span>
                        <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700 shrink-0">
                            Haalbaar
                        </span>
                    </div>

                    <!-- Card Body -->
                    <div class="p-4 space-y-3 text-center grow flex flex-col justify-between">
                        <!-- Avatar Preview -->
                        <div class="py-2 flex justify-center">
                            <div class="relative">
                                <div class="w-18 h-18 rounded-2xl p-0.5 {{ $border['preview_class'] }} transition-all">
                                    <img src="{{ $character->avatar_url }}" alt="{{ $border['name'] }}" class="w-full h-full rounded-2xl object-cover bg-slate-900">
                                </div>
                                <span class="absolute -bottom-1 -right-1 text-base drop-shadow">{{ $border['emoji'] }}</span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/60">
                                    {{ $border['event_name'] }}
                                </span>
                                @if($isEquipped)
                                    <span class="px-2 py-0.5 rounded text-[9px] font-black bg-amber-500 text-slate-950">
                                        ★ Actief
                                    </span>
                                @elseif($isOwned)
                                    <span class="px-2 py-0.5 rounded text-[9px] font-black bg-emerald-600 text-white">
                                        ✓ In Bezit
                                    </span>
                                @endif
                            </div>

                            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                                {{ $border['description'] }}
                            </p>

                            <div class="p-2 rounded-xl bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-200/70 dark:border-emerald-800/50 text-[10px] text-emerald-800 dark:text-emerald-300 font-semibold">
                                {{ $border['how_to_get'] }}
                            </div>
                        </div>
                    </div>

                    <!-- Action Footer -->
                    <div class="p-3 bg-slate-50/70 dark:bg-slate-800/50 border-t border-slate-200/80 dark:border-slate-800">
                        @if($isEquipped)
                            <form action="{{ route('halloween.equip-border') }}" method="POST">
                                @csrf
                                <input type="hidden" name="border_key" value="none">
                                <button type="submit" class="w-full py-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 font-bold text-xs text-center flex items-center justify-center gap-1.5 cursor-pointer hover:bg-rose-50 hover:text-rose-600 transition">
                                    <i class="fa-solid fa-check"></i>
                                    <span>Actief op Profiel (Klik om uit te zetten)</span>
                                </button>
                            </form>
                        @elseif($isOwned)
                            <form action="{{ route('halloween.equip-border') }}" method="POST">
                                @csrf
                                <input type="hidden" name="border_key" value="{{ $border['key'] }}">
                                <button type="submit"
                                        @disabled($character->isImprisoned() || $character->isComatose())
                                        class="w-full py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-black text-xs shadow-xs transition cursor-pointer flex items-center justify-center gap-1.5 disabled:opacity-50">
                                    <i class="fa-solid fa-paintbrush text-xs"></i>
                                    <span>Activeer op Profiel</span>
                                </button>
                            </form>
                        @else
                            <form action="{{ route('halloween.buy') }}" method="POST">
                                @csrf
                                <input type="hidden" name="item_key" value="{{ $border['key'] }}">
                                <button type="submit"
                                        @disabled(!$canAfford || $character->isImprisoned() || $character->isComatose())
                                        class="w-full py-2 rounded-xl font-black text-xs shadow-xs transition flex items-center justify-center gap-1.5 {{ ($canAfford && !$character->isImprisoned() && !$character->isComatose()) ? 'bg-amber-600 hover:bg-amber-700 text-white cursor-pointer active:scale-95' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 border border-slate-200 dark:border-slate-700 cursor-not-allowed' }}">
                                    <span>Vrijschakelen voor</span>
                                    <span class="font-mono font-black">{{ $border['price_seeds'] }}</span>
                                    <span>🍂</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- SECTION B: HALLOWEEN EVENT 2026 / STATUS: NIET (MEER) HAALBAAR      -->
    <!-- =================================================================== -->
    <div x-show="filterMode !== 'obtainable'" class="space-y-4 pt-2">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center text-base font-bold border border-rose-200 dark:border-rose-800/60">
                    <span x-show="sortMode === 'event'">🎃</span>
                    <i x-show="sortMode === 'status'" class="fa-solid fa-lock"></i>
                </div>
                <div>
                    <h2 class="text-sm sm:text-base font-black text-slate-900 dark:text-white flex items-center gap-2 flex-wrap">
                        <span x-text="sortMode === 'event' ? '🎃 Event: Halloween Event 2026 (Verzamelobjecten Archief)' : '🔒 Status: Niet haalbaar / Niet meer haalbaar'">
                            🎃 Event: Halloween Event 2026 (Verzamelobjecten Archief)
                        </span>
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-rose-100 dark:bg-rose-950/80 text-rose-800 dark:text-rose-300 border border-rose-300 dark:border-rose-700">
                            Niet haalbaar &bull; Niet meer haalbaar
                        </span>
                    </h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        Exclusieve profielkaders uit het afgelopen Halloween-event. Spelers die deze kaders hebben geclaimd behouden ze voor altijd, maar ze kunnen niet meer verkregen worden door nieuwe spelers.
                    </p>
                </div>
            </div>
            <span class="text-xs font-bold text-rose-600 dark:text-rose-400">
                {{ count($retiredBorders) }} Exclusieve Verzamelobjecten
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($retiredBorders as $border)
                @php
                    $isOwned = $character->hasBorder($border['key']);
                    $isEquipped = $character->equipped_border === $border['key'];
                @endphp
                <div x-show="filterMode !== 'owned' || {{ $isOwned ? 'true' : 'false' }}"
                     class="bg-white dark:bg-slate-900 rounded-2xl border {{ $isEquipped ? 'border-orange-500 ring-2 ring-orange-500/25' : ($isOwned ? 'border-amber-400/60 dark:border-amber-600/50' : 'border-slate-200/90 dark:border-slate-800 opacity-85') }} shadow-xs overflow-hidden flex flex-col justify-between transition">
                    <!-- Card Header -->
                    <div class="p-3.5 bg-slate-50/90 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between gap-2">
                        <span class="text-xs font-black text-slate-900 dark:text-white truncate">{{ $border['emoji'] }} {{ $border['name'] }}</span>
                        <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider bg-rose-100 dark:bg-rose-950/80 text-rose-800 dark:text-rose-300 border border-rose-300 dark:border-rose-700 shrink-0" title="Niet haalbaar voor nieuwe spelers">
                            Niet meer haalbaar
                        </span>
                    </div>

                    <!-- Card Body -->
                    <div class="p-4 space-y-3 text-center grow flex flex-col justify-between">
                        <!-- Avatar Preview -->
                        <div class="py-2 flex justify-center">
                            <div class="relative">
                                <div class="w-18 h-18 rounded-2xl p-0.5 {{ $border['preview_class'] }} transition-all">
                                    <img src="{{ $character->avatar_url }}" alt="{{ $border['name'] }}" class="w-full h-full rounded-2xl object-cover bg-slate-900">
                                </div>
                                <span class="absolute -bottom-1 -right-1 text-base drop-shadow">{{ $border['emoji'] }}</span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-orange-50 dark:bg-orange-950/60 text-orange-800 dark:text-orange-300 border border-orange-200/80 dark:border-orange-800/60">
                                    {{ $border['event_name'] }}
                                </span>
                                <span class="px-2 py-0.5 rounded text-[9px] font-black bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/60">
                                    Niet haalbaar
                                </span>
                                @if($isEquipped)
                                    <span class="px-2 py-0.5 rounded text-[9px] font-black bg-orange-500 text-white">
                                        ★ Actief
                                    </span>
                                @elseif($isOwned)
                                    <span class="px-2 py-0.5 rounded text-[9px] font-black bg-emerald-600 text-white">
                                        ✓ Geclaimd Verzamelobject
                                    </span>
                                @endif
                            </div>

                            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                                {{ $border['description'] }}
                            </p>

                            <div class="p-2 rounded-xl bg-rose-50/60 dark:bg-rose-950/30 border border-rose-200/70 dark:border-rose-800/50 text-[10px] text-rose-800 dark:text-rose-300 font-semibold">
                                {{ $border['how_to_get'] }}
                            </div>
                        </div>
                    </div>

                    <!-- Action Footer -->
                    <div class="p-3 bg-slate-50/70 dark:bg-slate-800/50 border-t border-slate-200/80 dark:border-slate-800">
                        @if($isEquipped)
                            <form action="{{ route('halloween.equip-border') }}" method="POST">
                                @csrf
                                <input type="hidden" name="border_key" value="none">
                                <button type="submit" class="w-full py-2 rounded-xl bg-orange-50 dark:bg-orange-950/40 text-orange-700 dark:text-orange-300 border border-orange-200 dark:border-orange-800/60 font-bold text-xs text-center flex items-center justify-center gap-1.5 cursor-pointer hover:bg-rose-50 hover:text-rose-600 transition">
                                    <i class="fa-solid fa-check"></i>
                                    <span>Actief op Profiel (Niet meer haalbaar)</span>
                                </button>
                            </form>
                        @elseif($isOwned)
                            <form action="{{ route('halloween.equip-border') }}" method="POST">
                                @csrf
                                <input type="hidden" name="border_key" value="{{ $border['key'] }}">
                                <button type="submit"
                                        @disabled($character->isImprisoned() || $character->isComatose())
                                        class="w-full py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-black text-xs shadow-xs transition cursor-pointer flex items-center justify-center gap-1.5 disabled:opacity-50">
                                    <i class="fa-solid fa-crown text-xs"></i>
                                    <span>Activeer Verzamelobject</span>
                                </button>
                            </form>
                        @else
                            <div class="w-full py-2 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 font-bold text-xs text-center flex items-center justify-center gap-1.5 select-none">
                                <i class="fa-solid fa-lock text-[10px]"></i>
                                <span>Niet meer haalbaar</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
