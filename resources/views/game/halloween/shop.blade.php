@extends('layouts.app')

@section('title', '🍬 Halloween Shop - TBS Kliniek Event')

@section('content')
<div class="space-y-6">
    <!-- EVENT HERO BANNER -->
    <div class="relative overflow-hidden rounded-3xl bg-linear-to-br from-slate-950 via-[#180928] to-[#2a0e05] border-2 border-orange-500/40 p-6 sm:p-8 shadow-2xl text-white">
        <!-- Spooky background elements -->
        <div class="absolute -right-6 -bottom-6 text-9xl opacity-15 pointer-events-none select-none text-orange-500">
            🍬
        </div>
        <div class="absolute right-36 top-4 text-4xl opacity-20 pointer-events-none select-none animate-bounce">
            🎃
        </div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2 max-w-2xl">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-orange-500/20 text-orange-400 border border-orange-500/40 shadow-xs flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-orange-500 animate-ping"></span>
                        🎃 Halloween Spook-Winkel
                    </span>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-900/60 text-purple-300 border border-purple-700/50">
                        ⏳ Exclusief tot 5 November (Nog {{ $timeRemaining['formatted'] }})
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight text-white flex items-center gap-2.5">
                    <span>De Halloween Shop</span>
                    <span class="text-orange-500">🍬</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                    Besteed je zuurverdiende <strong class="text-orange-400">Pompoenpitten 🎃</strong> aan exclusieve profielranden om je avatar mee te pimpen, griezelige hebbedingetjes voor je cel, en krachtige tijdelijke boosts voor de overlevingsstrijd in de kliniek!
                </p>
            </div>

            <!-- PLAYER PUMPKIN SEEDS WALLET & QUESTS SHORTCUT -->
            <div class="shrink-0 flex flex-col sm:flex-row md:flex-col gap-3 justify-center">
                <div class="bg-black/40 backdrop-blur-md rounded-2xl border border-orange-500/30 p-4 text-center space-y-1">
                    <span class="text-[10px] font-black uppercase tracking-wider text-orange-400/80 block">Jouw Pompoenpitten</span>
                    <div class="text-3xl font-black text-orange-400 flex items-center justify-center gap-2 font-mono">
                        <span>🎃</span>
                        <span>{{ number_format($character->pumpkin_seeds ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <span class="text-[10px] text-slate-400 block">Verdien meer pitten via quests</span>
                </div>

                <a href="{{ route('halloween.quests') }}" class="px-5 py-3 rounded-2xl bg-purple-900/80 hover:bg-purple-800 text-white font-black text-xs text-center border border-purple-600/50 shadow-lg transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-scroll text-orange-400"></i>
                    <span>Bekijk Halloween Quests &raquo;</span>
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold flex items-center gap-2.5">
            <i class="fa-solid fa-circle-check text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-bold flex items-center gap-2.5">
            <i class="fa-solid fa-triangle-exclamation text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="p-4 rounded-2xl bg-purple-500/10 border border-purple-500/30 text-purple-300 text-xs font-bold flex items-center gap-2.5">
            <i class="fa-solid fa-circle-info text-base"></i>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    <!-- CATEGORY 1: EXCLUSIEVE HALLOWEEN PROFIELRANDEN -->
    <div class="space-y-4">
        <div class="flex items-center justify-between pb-1 border-b border-slate-200 dark:border-purple-900/40">
            <div class="flex items-center gap-2">
                <span class="text-xl">🎃</span>
                <div>
                    <h2 class="text-base font-black text-slate-900 dark:text-white">Halloween Profielranden (Borders)</h2>
                    <p class="text-[11px] text-slate-500 dark:text-purple-300/80">Pimp je avatar met een permanente griezelgloed. Zichtbaar op je profiel, de ranglijst en in de chat!</p>
                </div>
            </div>

            @if($character->equipped_border)
                <form action="{{ route('halloween.equip-border') }}" method="POST">
                    @csrf
                    <input type="hidden" name="border_key" value="none">
                    <button type="submit" class="text-[11px] font-bold text-rose-500 hover:text-rose-400 hover:underline cursor-pointer">
                        Rand Uitschakelen
                    </button>
                </form>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($borders as $border)
                @php
                    $isOwned = $character->hasBorder($border['key']);
                    $isEquipped = $character->equipped_border === $border['key'];
                @endphp
                <div class="bg-white dark:bg-[#150a2b] rounded-2xl border p-5 flex flex-col justify-between gap-4 transition shadow-xs hover:border-orange-500/60 {{ $isEquipped ? 'border-orange-500 ring-2 ring-orange-500/40 shadow-orange-500/15' : 'border-slate-200 dark:border-purple-900/40' }}">
                    <div class="space-y-3 text-center">
                        <!-- Border Badge -->
                        <div class="flex justify-center">
                            <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider {{ $isEquipped ? 'bg-orange-500 text-white' : ($isOwned ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-purple-900/40 text-purple-300 border border-purple-800/40') }}">
                                {{ $isEquipped ? '★ ACTIEF UITGERUST' : ($isOwned ? '✓ IN BEZIT' : $border['badge']) }}
                            </span>
                        </div>

                        <!-- Live Avatar Preview with Border -->
                        <div class="py-2 flex justify-center">
                            <div class="relative">
                                <div class="w-20 h-20 rounded-2xl p-0.5 {{ $border['preview_class'] }} transition-all">
                                    <img src="{{ $character->avatar_url }}" alt="{{ $character->name }}" class="w-full h-full rounded-2xl object-cover bg-slate-900">
                                </div>
                                @if($border['key'] === 'pumpkin_glow')
                                    <span class="absolute -bottom-1 -right-1 text-base">🎃</span>
                                @elseif($border['key'] === 'vampire_blood')
                                    <span class="absolute -bottom-1 -right-1 text-base">🩸</span>
                                @elseif($border['key'] === 'phantom_mist')
                                    <span class="absolute -bottom-1 -right-1 text-base">👻</span>
                                @elseif($border['key'] === 'gothic_king')
                                    <span class="absolute -top-2 -right-1 text-base">👑</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white">{{ $border['name'] }}</h3>
                            <p class="text-xs text-slate-500 dark:text-purple-300/80 mt-1 leading-relaxed">{{ $border['description'] }}</p>
                        </div>
                    </div>

                    <!-- Buy / Equip Form -->
                    <div class="pt-3 border-t border-slate-100 dark:border-purple-900/40 space-y-2">
                        @if($isEquipped)
                            <button type="button" disabled class="w-full py-2.5 rounded-xl bg-emerald-500/20 text-emerald-400 font-black text-xs border border-emerald-500/30 text-center cursor-default flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-check"></i>
                                <span>Momenteel Actief</span>
                            </button>
                        @elseif($isOwned)
                            <form action="{{ route('halloween.equip-border') }}" method="POST">
                                @csrf
                                <input type="hidden" name="border_key" value="{{ $border['key'] }}">
                                <button type="submit" class="w-full py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs shadow-md transition cursor-pointer flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-paintbrush"></i>
                                    <span>Instellen op Profiel</span>
                                </button>
                            </form>
                        @else
                            <form action="{{ route('halloween.buy') }}" method="POST">
                                @csrf
                                <input type="hidden" name="item_key" value="{{ $border['key'] }}">
                                <button type="submit" 
                                        @if(($character->pumpkin_seeds ?? 0) < $border['price_seeds']) disabled @endif
                                        class="w-full py-2.5 rounded-xl font-black text-xs shadow-md transition flex items-center justify-center gap-2 cursor-pointer {{ ($character->pumpkin_seeds ?? 0) >= $border['price_seeds'] ? 'bg-linear-to-r from-orange-600 to-amber-600 hover:from-orange-500 hover:to-amber-500 text-white shadow-orange-500/20' : 'bg-slate-200 dark:bg-purple-950/60 text-slate-400 dark:text-purple-400/50 cursor-not-allowed opacity-60' }}">
                                    <span>Koop voor</span>
                                    <span class="font-mono text-sm">{{ $border['price_seeds'] }}</span>
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
    <div class="space-y-4 pt-4">
        <div class="flex items-center gap-2 pb-1 border-b border-slate-200 dark:border-purple-900/40">
            <span class="text-xl">🦇</span>
            <div>
                <h2 class="text-base font-black text-slate-900 dark:text-white">Halloween Hebbedingetjes & Collectibles</h2>
                <p class="text-[11px] text-slate-500 dark:text-purple-300/80">Unieke collector's items om te verzamelen in je cel en inventaris. Sommige hebben speciale voordelen!</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($collectibles as $item)
                @php
                    $dbItem = \App\Models\Item::where('slug', 'halloween_'.$item['key'])->first();
                    $ownedCount = $dbItem ? \App\Models\CharacterItem::where('character_id', $character->id)->where('item_id', $dbItem->id)->value('quantity') ?? 0 : 0;
                @endphp
                <div class="bg-white dark:bg-[#150a2b] rounded-2xl border border-slate-200 dark:border-purple-900/40 p-5 flex flex-col justify-between gap-4 transition shadow-xs hover:border-orange-500/60">
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl bg-orange-500/10 text-orange-500 border border-orange-500/20 flex items-center justify-center text-2xl shadow-inner">
                                    <i class="fa-solid {{ $item['icon'] }}"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-slate-900 dark:text-white">{{ $item['name'] }}</h3>
                                    <span class="text-[10px] font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wider">{{ $item['badge'] }}</span>
                                </div>
                            </div>

                            @if($ownedCount > 0)
                                <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-black border border-emerald-500/30 shrink-0">
                                    {{ $ownedCount }}x In cel
                                </span>
                            @endif
                        </div>

                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">{{ $item['description'] }}</p>
                    </div>

                    <div class="pt-3 border-t border-slate-100 dark:border-purple-900/40">
                        <form action="{{ route('halloween.buy') }}" method="POST">
                            @csrf
                            <input type="hidden" name="item_key" value="{{ $item['key'] }}">
                            <button type="submit" 
                                    @if(($character->pumpkin_seeds ?? 0) < $item['price_seeds']) disabled @endif
                                    class="w-full py-2.5 rounded-xl font-black text-xs shadow-md transition flex items-center justify-center gap-2 cursor-pointer {{ ($character->pumpkin_seeds ?? 0) >= $item['price_seeds'] ? 'bg-linear-to-r from-orange-600 to-amber-600 hover:from-orange-500 hover:to-amber-500 text-white shadow-orange-500/20' : 'bg-slate-200 dark:bg-purple-950/60 text-slate-400 dark:text-purple-400/50 cursor-not-allowed opacity-60' }}">
                                <span>Koop Item voor</span>
                                <span class="font-mono text-sm">{{ $item['price_seeds'] }}</span>
                                <span>🎃</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- CATEGORY 3: SPECIALE HALLOWEEN BOOSTS -->
    <div class="space-y-4 pt-4">
        <div class="flex items-center gap-2 pb-1 border-b border-slate-200 dark:border-purple-900/40">
            <span class="text-xl">🧪</span>
            <div>
                <h2 class="text-base font-black text-slate-900 dark:text-white">Speciale Halloween Boosts & Elixers</h2>
                <p class="text-[11px] text-slate-500 dark:text-purple-300/80">Krachtige drankjes en duistere zegeningen die direct geactiveerd worden om je overval- en vechtkansen te vergroten.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($boosts as $boost)
                @php
                    $isPerkActive = $boost['key'] === 'grim_blessing' && $character->hasPerk('magere_hein_zegening');
                    $remainingSecs = $isPerkActive ? $character->getPerkRemainingSeconds('magere_hein_zegening') : 0;
                    $remainingHours = ceil($remainingSecs / 3600);
                @endphp
                <div class="bg-white dark:bg-[#150a2b] rounded-2xl border border-slate-200 dark:border-purple-900/40 p-5 flex flex-col justify-between gap-4 transition shadow-xs hover:border-orange-500/60 {{ $isPerkActive ? 'border-amber-500/60 ring-2 ring-amber-500/30' : '' }}">
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-400 border border-purple-500/20 flex items-center justify-center text-2xl shadow-inner">
                                    <i class="fa-solid {{ $boost['icon'] }}"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-slate-900 dark:text-white">{{ $boost['name'] }}</h3>
                                    <span class="text-[10px] font-bold text-orange-500 uppercase tracking-wider">{{ $boost['badge'] }}</span>
                                </div>
                            </div>

                            @if($isPerkActive)
                                <span class="px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-400 text-[10px] font-black border border-amber-500/30 shrink-0 animate-pulse">
                                    Actief ({{ $remainingHours }}u)
                                </span>
                            @endif
                        </div>

                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">{{ $boost['description'] }}</p>
                    </div>

                    <div class="pt-3 border-t border-slate-100 dark:border-purple-900/40">
                        <form action="{{ route('halloween.buy') }}" method="POST">
                            @csrf
                            <input type="hidden" name="item_key" value="{{ $boost['key'] }}">
                            <button type="submit" 
                                    @if(($character->pumpkin_seeds ?? 0) < $boost['price_seeds']) disabled @endif
                                    class="w-full py-2.5 rounded-xl font-black text-xs shadow-md transition flex items-center justify-center gap-2 cursor-pointer {{ ($character->pumpkin_seeds ?? 0) >= $boost['price_seeds'] ? 'bg-linear-to-r from-orange-600 to-amber-600 hover:from-orange-500 hover:to-amber-500 text-white shadow-orange-500/20' : 'bg-slate-200 dark:bg-purple-950/60 text-slate-400 dark:text-purple-400/50 cursor-not-allowed opacity-60' }}">
                                <span>Koop Boost voor</span>
                                <span class="font-mono text-sm">{{ $boost['price_seeds'] }}</span>
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
