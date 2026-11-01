@extends('layouts.app')

@section('title', '🍬 Halloween Shop - TBS Kliniek Event')

@section('content')
<div class="space-y-6">
    <!-- TBS Kliniek Title Banner -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 p-6 sm:p-7 text-slate-800 dark:text-slate-100 shadow-xs border border-slate-200 dark:border-slate-800 relative overflow-hidden">
        <!-- Subtle theme ambient glow -->
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-purple-500/5 dark:bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-13 h-13 rounded-2xl bg-purple-50 dark:bg-purple-950/50 border border-purple-200 dark:border-purple-800/60 flex items-center justify-center text-2xl text-purple-600 dark:text-purple-400 shadow-2xs shrink-0">
                    <i class="fa-solid fa-store"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <span class="text-[10px] font-black uppercase tracking-widest text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/80 px-2.5 py-0.5 rounded-md border border-purple-200 dark:border-purple-800/60">
                            🎃 Halloween Spook-Winkel
                        </span>
                        <span class="text-[10px] font-bold text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 px-2.5 py-0.5 rounded-md border border-amber-200 dark:border-amber-800/60">
                            <i class="fa-regular fa-clock mr-1 text-amber-600 dark:text-amber-400"></i> Resterend: {{ $timeRemaining['formatted'] }} (t/m 5 Nov)
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        Halloween Shop & Beloningen
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 max-w-xl leading-relaxed">
                        Besteed je zuurverdiende <strong class="text-amber-600 dark:text-amber-400">Pompoenpitten 🎃</strong> aan exclusieve avatar profielranden, sfeervolle cel-collectibles en krachtige tijdelijke boosts voor de overlevingsstrijd in de kliniek.
                    </p>
                </div>
            </div>

            <!-- Seeds Wallet Chip & Quests Link -->
            <div class="flex items-center gap-3 shrink-0">
                <div class="bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 flex items-center gap-3 shadow-2xs">
                    <div class="w-9 h-9 rounded-lg bg-amber-100 dark:bg-amber-950/80 border border-amber-200 dark:border-amber-800/60 flex items-center justify-center text-lg shrink-0">
                        🎃
                    </div>
                    <div>
                        <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-400 block">Jouw Pompoenpitten</span>
                        <div class="text-lg font-black text-slate-900 dark:text-white font-mono leading-none">
                            {{ number_format($character->pumpkin_seeds ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <a href="{{ route('halloween.quests') }}" class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold py-2.5 px-4 rounded-xl text-xs transition-all duration-150 active:scale-95 cursor-pointer flex items-center gap-2 shrink-0 shadow-2xs">
                    <i class="fa-solid fa-scroll text-purple-600 dark:text-purple-400 text-xs"></i>
                    <span>Naar Quests &raquo;</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Sub-Navigation Tabs Bar -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
        <a href="{{ route('halloween.quests') }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 shadow-2xs">
            <i class="fa-solid fa-scroll"></i>
            <span>Halloween Quests</span>
        </a>
        <a href="{{ route('halloween.shop') }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-purple-600 text-white shadow-xs">
            <i class="fa-solid fa-store"></i>
            <span>Halloween Shop & Beloningen</span>
        </a>
    </div>

    <!-- Flash Notifications -->
    

    <!-- CATEGORY 1: EXCLUSIEVE HALLOWEEN PROFIELRANDEN -->
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm font-bold border border-purple-200/80 dark:border-purple-800/60">
                    <i class="fa-solid fa-palette"></i>
                </div>
                <div>
                    <h2 class="text-sm sm:text-base font-black text-slate-900 dark:text-white">Halloween Profielranden (Avatar Borders)</h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Pimp je avatar met een griezelgloed. Zichtbaar op je profiel, de ranglijst en in de chat!</p>
                </div>
            </div>

            @if($character->equipped_border)
                <form action="{{ route('halloween.equip-border') }}" method="POST">
                    @csrf
                    <input type="hidden" name="border_key" value="none">
                    <button type="submit" @disabled($character->isImprisoned() || $character->isComatose()) class="text-xs font-bold text-rose-600 dark:text-rose-400 hover:text-rose-700 hover:underline cursor-pointer flex items-center gap-1 self-start sm:self-auto disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-xmark text-[11px]"></i>
                        <span>Rand Uitschakelen</span>
                    </button>
                </form>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4.5">
            @foreach($borders as $border)
                @php
                    $isOwned = $character->hasBorder($border['key']);
                    $isEquipped = $character->equipped_border === $border['key'];
                    $canAfford = ($character->pumpkin_seeds ?? 0) >= $border['price_seeds'];
                @endphp
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden flex flex-col justify-between hover:border-purple-300 dark:hover:border-purple-600/50 transition {{ $isEquipped ? 'border-purple-400 dark:border-purple-500 ring-2 ring-purple-500/20' : '' }}">
                    <!-- Card Header -->
                    <div class="p-3.5 bg-slate-50/90 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                        <span class="text-xs font-black text-slate-900 dark:text-white">{{ $border['name'] }}</span>
                        @if($isEquipped)
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider bg-purple-600 text-white shadow-2xs">
                                ★ Actief
                            </span>
                        @elseif($isOwned)
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                ✓ In Bezit
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700">
                                {{ $border['badge'] }}
                            </span>
                        @endif
                    </div>

                    <!-- Card Body -->
                    <div class="p-4 space-y-3 text-center grow flex flex-col justify-between">
                        <!-- Live Avatar Preview with Border -->
                        <div class="py-2 flex justify-center">
                            <div class="relative">
                                <div class="w-18 h-18 rounded-2xl p-0.5 {{ $border['preview_class'] }} transition-all">
                                    <img src="{{ $character->avatar_url }}" alt="{{ $character->name }}" class="w-full h-full rounded-2xl object-cover bg-slate-900">
                                </div>
                                @if($border['key'] === 'pumpkin_glow')
                                    <span class="absolute -bottom-1 -right-1 text-sm">🎃</span>
                                @elseif($border['key'] === 'vampire_blood')
                                    <span class="absolute -bottom-1 -right-1 text-sm">🩸</span>
                                @elseif($border['key'] === 'phantom_mist')
                                    <span class="absolute -bottom-1 -right-1 text-sm">👻</span>
                                @elseif($border['key'] === 'gothic_king')
                                    <span class="absolute -top-2 -right-1 text-sm">👑</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ $border['description'] }}</p>
                        </div>
                    </div>

                    <!-- Buy / Equip Footer -->
                    <div class="p-3 bg-slate-50/70 dark:bg-slate-800/50 border-t border-slate-200/80 dark:border-slate-800">
                        @if($isEquipped)
                            <div class="w-full py-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60 font-bold text-xs text-center flex items-center justify-center gap-1.5 cursor-default">
                                <i class="fa-solid fa-check"></i>
                                <span>Momenteel Actief</span>
                            </div>
                        @elseif($isOwned)
                            <form action="{{ route('halloween.equip-border') }}" method="POST">
                                @csrf
                                <input type="hidden" name="border_key" value="{{ $border['key'] }}">
                                <button type="submit" 
                                        @if($character->isImprisoned() || $character->isComatose()) disabled @endif
                                        class="w-full py-2 rounded-xl {{ ($character->isImprisoned() || $character->isComatose()) ? 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 cursor-not-allowed' : 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white cursor-pointer active:scale-95' }} font-black text-xs shadow-xs transition-all duration-150 flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-paintbrush text-xs"></i>
                                    <span>Instellen op Profiel</span>
                                </button>
                            </form>
                        @else
                            <form action="{{ route('halloween.buy') }}" method="POST">
                                @csrf
                                <input type="hidden" name="item_key" value="{{ $border['key'] }}">
                                <button type="submit" 
                                        @if(!$canAfford || $character->isImprisoned() || $character->isComatose()) disabled @endif
                                        class="w-full py-2 rounded-xl font-black text-xs shadow-xs transition-all duration-150 flex items-center justify-center gap-1.5 {{ ($canAfford && !$character->isImprisoned() && !$character->isComatose()) ? 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white cursor-pointer active:scale-95' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed' }}">
                                    <span>Koop voor</span>
                                    <span class="font-mono font-black text-xs {{ $canAfford ? 'text-amber-300' : 'text-slate-400 dark:text-slate-500' }}">{{ $border['price_seeds'] }}</span>
                                    <span>🎃</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- CATEGORY 2: HEBBEDINGETJES & COLLECTIBLES -->
    <div class="space-y-4 pt-2">
        <div class="flex items-center gap-2.5 pb-2 border-b border-slate-200 dark:border-slate-800">
            <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm font-bold border border-purple-200/80 dark:border-purple-800/60">
                <i class="fa-solid fa-box-archive"></i>
            </div>
            <div>
                <h2 class="text-sm sm:text-base font-black text-slate-900 dark:text-white">Halloween Hebbedingetjes & Collectibles</h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Unieke items om te verzamelen in je cel en depot</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4.5">
            @foreach($collectibles as $item)
                @php
                    $dbItem = \App\Models\Item::where('slug', 'halloween_'.$item['key'])->first();
                    $ownedCount = $dbItem ? \App\Models\CharacterItem::where('character_id', $character->id)->where('item_id', $dbItem->id)->value('quantity') ?? 0 : 0;
                    $canAfford = ($character->pumpkin_seeds ?? 0) >= $item['price_seeds'];
                @endphp
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden flex flex-col justify-between hover:border-purple-300 dark:hover:border-purple-600/50 transition">
                    <!-- Card Header -->
                    <div class="p-4 bg-slate-50/90 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-800/60 flex items-center justify-center text-xl shadow-2xs shrink-0">
                                <i class="fa-solid {{ $item['icon'] }}"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-black text-slate-900 dark:text-white">{{ $item['name'] }}</h3>
                                <span class="text-[10px] font-black text-purple-600 dark:text-purple-400 uppercase tracking-wider block mt-0.5">
                                    {{ $item['badge'] }}
                                </span>
                            </div>
                        </div>

                        @if($ownedCount > 0)
                            <span class="px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 text-[10px] font-black border border-emerald-300 dark:border-emerald-800 shrink-0">
                                {{ $ownedCount }}x In cel
                            </span>
                        @endif
                    </div>

                    <!-- Card Body -->
                    <div class="p-4 space-y-3 grow flex flex-col justify-between">
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ $item['description'] }}</p>
                    </div>

                    <!-- Card Footer -->
                    <div class="p-3 bg-slate-50/70 dark:bg-slate-800/50 border-t border-slate-200/80 dark:border-slate-800">
                        <form action="{{ route('halloween.buy') }}" method="POST">
                            @csrf
                            <input type="hidden" name="item_key" value="{{ $item['key'] }}">
                            <button type="submit" 
                                    @if(!$canAfford || $character->isImprisoned() || $character->isComatose()) disabled @endif
                                    class="w-full py-2.5 rounded-xl font-black text-xs shadow-xs transition-all duration-150 flex items-center justify-center gap-1.5 {{ ($canAfford && !$character->isImprisoned() && !$character->isComatose()) ? 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white cursor-pointer active:scale-95' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed' }}">
                                <span>Koop Item voor</span>
                                <span class="font-mono font-black text-xs {{ $canAfford ? 'text-amber-300' : 'text-slate-400 dark:text-slate-500' }}">{{ $item['price_seeds'] }}</span>
                                <span>🎃</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- CATEGORY 3: SPECIALE HALLOWEEN BOOSTS -->
    <div class="space-y-4 pt-2">
        <div class="flex items-center gap-2.5 pb-2 border-b border-slate-200 dark:border-slate-800">
            <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm font-bold border border-purple-200/80 dark:border-purple-800/60">
                <i class="fa-solid fa-flask-vial"></i>
            </div>
            <div>
                <h2 class="text-sm sm:text-base font-black text-slate-900 dark:text-white">Speciale Halloween Boosts & Elixers</h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Krachtige drankjes en duistere zegeningen die direct geactiveerd worden</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4.5">
            @foreach($boosts as $boost)
                @php
                    $isPerkActive = $boost['key'] === 'grim_blessing' && $character->hasPerk('magere_hein_zegening');
                    $remainingSecs = $isPerkActive ? $character->getPerkRemainingSeconds('magere_hein_zegening') : 0;
                    $remainingHours = ceil($remainingSecs / 3600);
                    $canAfford = ($character->pumpkin_seeds ?? 0) >= $boost['price_seeds'];
                @endphp
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden flex flex-col justify-between hover:border-purple-300 dark:hover:border-purple-600/50 transition {{ $isPerkActive ? 'border-amber-400 dark:border-amber-500 ring-2 ring-amber-500/20' : '' }}">
                    <!-- Card Header -->
                    <div class="p-4 bg-slate-50/90 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-950/40 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-800/60 flex items-center justify-center text-xl shadow-2xs shrink-0">
                                <i class="fa-solid {{ $boost['icon'] }}"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-black text-slate-900 dark:text-white">{{ $boost['name'] }}</h3>
                                <span class="text-[10px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-wider block mt-0.5">
                                    {{ $boost['badge'] }}
                                </span>
                            </div>
                        </div>

                        @if($isPerkActive)
                            <span class="px-2 py-0.5 rounded-md bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 text-[10px] font-black border border-amber-300 dark:border-amber-700 shrink-0 animate-pulse">
                                Actief ({{ $remainingHours }}u)
                            </span>
                        @endif
                    </div>

                    <!-- Card Body -->
                    <div class="p-4 space-y-3 grow flex flex-col justify-between">
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ $boost['description'] }}</p>
                    </div>

                    <!-- Card Footer -->
                    <div class="p-3 bg-slate-50/70 dark:bg-slate-800/50 border-t border-slate-200/80 dark:border-slate-800">
                        <form action="{{ route('halloween.buy') }}" method="POST">
                            @csrf
                            <input type="hidden" name="item_key" value="{{ $boost['key'] }}">
                            <button type="submit" 
                                    @if(!$canAfford || $character->isImprisoned() || $character->isComatose()) disabled @endif
                                    class="w-full py-2.5 rounded-xl font-black text-xs shadow-xs transition-all duration-150 flex items-center justify-center gap-1.5 {{ ($canAfford && !$character->isImprisoned() && !$character->isComatose()) ? 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white cursor-pointer active:scale-95' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed' }}">
                                <span>Koop Boost voor</span>
                                <span class="font-mono font-black text-xs {{ $canAfford ? 'text-amber-300' : 'text-slate-400 dark:text-slate-500' }}">{{ $boost['price_seeds'] }}</span>
                                <span>🎃</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
