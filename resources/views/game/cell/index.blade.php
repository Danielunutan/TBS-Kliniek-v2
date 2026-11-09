@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="rounded-2xl overflow-hidden border border-slate-200/90 dark:border-slate-800 shadow-xs bg-white dark:bg-slate-900 p-6 sm:p-8 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200/80 dark:border-indigo-800/70 text-indigo-700 dark:text-indigo-300 text-xs font-bold mb-1">
                    <i class="fa-solid fa-house-user text-indigo-600 dark:text-indigo-400"></i> Privéverblijf &amp; Prestige
                </span>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-2.5">
                    <i class="fa-solid fa-bed text-indigo-600 dark:text-indigo-400"></i>
                    <span>Mijn TBS Cel &amp; Upgrades</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 max-w-2xl leading-relaxed">
                    Investeer je miljoenen in je persoonlijke patiëntenverblijf. Van een luxe boxspring voor sneller energieherstel tot een gepantserde kluis en versterkte stalen celdeur voor maximale verdediging tegen overvallers.
                </p>
            </div>

            <!-- Total Invested Badge -->
            <div class="shrink-0 bg-slate-50 dark:bg-slate-800/80 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-700 text-right">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-0.5">Totale Celinvestering</span>
                <div class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono">
                    €{{ number_format($totalInvested, 0, ',', '.') }}
                </div>
                <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 block mt-0.5">
                    Contant op zak: <span class="text-slate-900 dark:text-white font-mono font-black">€{{ number_format($character->cash, 0, ',', '.') }}</span>
                </span>
            </div>
        </div>

        <!-- Cell Stat Bonuses Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
            <div class="bg-indigo-50/70 dark:bg-indigo-950/30 rounded-xl p-3 border border-indigo-200/60 dark:border-indigo-900/40">
                <div class="flex items-center gap-2 mb-1">
                    <i class="fa-solid fa-bolt text-indigo-600 dark:text-indigo-400 text-sm"></i>
                    <span class="text-[10px] font-extrabold uppercase tracking-wide text-slate-500 dark:text-slate-400">Energieherstel</span>
                </div>
                @php $bedLevel = $currentLevels['bed'] ?? 0; @endphp
                <span class="text-base font-black text-indigo-700 dark:text-indigo-300 font-mono">
                    @if($bedLevel > 0)
                        +{{ (int) round($character->getCellEnergyIntervalReduction() * 100) }}% Sneller
                    @else
                        Standaard
                    @endif
                </span>
            </div>

            <div class="bg-rose-50/70 dark:bg-rose-950/30 rounded-xl p-3 border border-rose-200/60 dark:border-rose-900/40">
                <div class="flex items-center gap-2 mb-1">
                    <i class="fa-solid fa-shield-halved text-rose-600 dark:text-rose-400 text-sm"></i>
                    <span class="text-[10px] font-extrabold uppercase tracking-wide text-slate-500 dark:text-slate-400">Celverdediging</span>
                </div>
                @php $doorLevel = $currentLevels['door'] ?? 0; @endphp
                <span class="text-base font-black text-rose-700 dark:text-rose-300 font-mono">
                    @if($doorLevel > 0)
                        +{{ number_format($character->getCellDefenseBonus(), 0, ',', '.') }} Def
                    @else
                        +0 Basis
                    @endif
                </span>
            </div>

            <div class="bg-emerald-50/70 dark:bg-emerald-950/30 rounded-xl p-3 border border-emerald-200/60 dark:border-emerald-900/40">
                <div class="flex items-center gap-2 mb-1">
                    <i class="fa-solid fa-vault text-emerald-600 dark:text-emerald-400 text-sm"></i>
                    <span class="text-[10px] font-extrabold uppercase tracking-wide text-slate-500 dark:text-slate-400">Zakgeld Kluis</span>
                </div>
                @php $safeLevel = $currentLevels['safe'] ?? 0; @endphp
                <span class="text-base font-black text-emerald-700 dark:text-emerald-300 font-mono">
                    @if($safeLevel > 0)
                        {{ $character->getCellCashProtectionPct() }}% Beschermd
                    @else
                        0% Beschermd
                    @endif
                </span>
            </div>

            <div class="bg-amber-50/70 dark:bg-amber-950/30 rounded-xl p-3 border border-amber-200/60 dark:border-amber-900/40">
                <div class="flex items-center gap-2 mb-1">
                    <i class="fa-solid fa-tv text-amber-600 dark:text-amber-400 text-sm"></i>
                    <span class="text-[10px] font-extrabold uppercase tracking-wide text-slate-500 dark:text-slate-400">Comfort &amp; XP</span>
                </div>
                @php $comfortLevel = $currentLevels['comfort'] ?? 0; @endphp
                <span class="text-base font-black text-amber-700 dark:text-amber-300 font-mono">
                    @if($comfortLevel > 0)
                        +{{ $character->getCellComfortBonusPct() }}% XP/Respect
                    @else
                        Standaard
                    @endif
                </span>
            </div>
        </div>
    </div>

    <!-- Upgrades Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @foreach($catalog as $key => $upgrade)
            @php
                $lvl = $currentLevels[$key] ?? 0;
                $maxLvl = (int) ($upgrade['max_level'] ?? 5);
                $isMax = $lvl >= $maxLvl;
                $nextTier = !$isMax ? \App\Support\CellUpgradeCatalog::getNextTier($key, $lvl) : null;
                $currentTier = $lvl > 0 ? ($upgrade['tiers'][$lvl] ?? null) : null;
                $canAfford = $nextTier && ((int)$character->cash >= (int)$nextTier['cost']);
            @endphp
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <!-- Title & Level Header -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 flex items-center justify-center text-lg text-slate-800 dark:text-slate-200 shrink-0">
                                <i class="fa-solid {{ $upgrade['icon'] }}"></i>
                            </div>
                            <div>
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">
                                    {{ $upgrade['category'] }}
                                </span>
                                <h3 class="text-base font-black text-slate-900 dark:text-white">
                                    {{ $upgrade['name'] }}
                                </h3>
                            </div>
                        </div>

                        <!-- Level Pill -->
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black {{ $isMax ? 'bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-700' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300' }}">
                            @if($isMax)
                                <i class="fa-solid fa-crown text-[10px]"></i> MAX
                            @else
                                Level {{ $lvl }}/{{ $maxLvl }}
                            @endif
                        </span>
                    </div>

                    <!-- Description -->
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        {{ $upgrade['description'] }}
                    </p>

                    <!-- Progress Bar (5 ticks) -->
                    <div class="grid grid-cols-5 gap-1 pt-1">
                        @for($i = 1; $i <= $maxLvl; $i++)
                            <div class="h-2 rounded-full transition-colors {{ $i <= $lvl ? 'bg-indigo-600 dark:bg-indigo-500 shadow-2xs' : 'bg-slate-200 dark:bg-slate-800' }}"></div>
                        @endfor
                    </div>

                    <!-- Current Active Perk -->
                    @if($currentTier)
                        <div class="p-2.5 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200/60 dark:border-emerald-900/40 flex items-center gap-2 text-xs">
                            <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                            <div class="text-emerald-900 dark:text-emerald-200">
                                <strong>Actief voordeel:</strong> {{ $currentTier['benefit'] }}
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Next Tier Preview & Upgrade Action -->
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                    @if(!$isMax && $nextTier)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="space-y-0.5">
                                <span class="text-[10px] font-extrabold uppercase tracking-wide text-slate-400 dark:text-slate-500 block">
                                    Volgend niveau (Lvl {{ $nextTier['level'] }}):
                                </span>
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">
                                    {{ $nextTier['benefit'] }}
                                </span>
                                <span class="text-xs font-mono font-black text-slate-900 dark:text-white block">
                                    Kosten: €{{ number_format($nextTier['cost'], 0, ',', '.') }}
                                </span>
                            </div>

                            <form action="{{ route('cell.upgrade', $key) }}" method="POST" class="shrink-0">
                                @csrf
                                <button type="submit" 
                                    @if(!$canAfford) disabled @endif
                                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-150 flex items-center justify-center gap-2 shadow-xs cursor-pointer {{ $canAfford ? 'bg-indigo-600 hover:bg-indigo-700 text-white active:scale-95' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600 cursor-not-allowed opacity-60' }}">
                                    <i class="fa-solid fa-arrow-up"></i>
                                    <span>{{ $canAfford ? 'Upgrade Installeren' : 'Te Weinig Geld' }}</span>
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="text-center py-2 text-xs font-black text-amber-700 dark:text-amber-400 flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-award"></i>
                            <span>Maximale Celuitbreiding Voltooid!</span>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

