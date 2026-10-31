@extends('layouts.app')

@section('title', 'Illegale Armpjedruk Arena - TBS Kliniek')

@section('content')
<div class="space-y-6"
     x-data="tbsArmWrestleArena({{ (int) ($cooldownRemaining ?? 0) }})"
     @stats-updated.window="if ($event.detail) { if ($event.detail.cash !== undefined) playerCash = $event.detail.cash; if ($event.detail.energy !== undefined) playerEnergy = $event.detail.energy; }">

    

    <!-- Clean TBS Kliniek Title Banner -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 p-6 sm:p-8 text-slate-800 dark:text-slate-100 shadow-xs border border-slate-200/90 dark:border-slate-800 relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-2xl text-purple-600 dark:text-purple-400 shadow-2xs shrink-0">
                    <i class="fa-solid fa-hand-back-fist"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 px-2.5 py-0.5 rounded-full border border-purple-200/80 dark:border-purple-800/70">
                            Krachthonk & Minigames
                        </span>
                        <span class="text-[10px] font-bold text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 px-2 py-0.5 rounded-full border border-amber-200/80 dark:border-amber-800/70">
                            1-op-1 Showdown
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">
                        De Illegale Armpjedruk Arena
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 max-w-2xl leading-relaxed">
                        Test je spierkracht, uithoudingsvermogen en reflexen aan de zware houten tafel in het krachthonk. Daag de beruchtste afdelingsbazen uit voor direct prijzengeld of leg jouw inzet op tafel tegen medepatiënten!
                    </p>
                </div>
            </div>

            <!-- Stats Pill Counters -->
            <div class="flex flex-wrap items-center gap-2 text-xs font-bold shrink-0">
                <span class="bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 px-3.5 py-2 rounded-xl border border-purple-200/80 dark:border-purple-800/70 shadow-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-trophy text-purple-600 dark:text-purple-400"></i>
                    <span>{{ $stat?->wins ?? 0 }} Gewonnen</span>
                </span>
                <span class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-3.5 py-2 rounded-xl border border-slate-200/80 dark:border-slate-700 shadow-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-skull text-slate-500"></i>
                    <span>{{ $stat?->losses ?? 0 }} Verloren</span>
                </span>
                <span class="bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 px-3.5 py-2 rounded-xl border border-amber-200/80 dark:border-amber-800/70 shadow-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-fire text-amber-500"></i>
                    <span>Reeks: {{ $stat?->current_streak ?? 0 }} (Beste: {{ $stat?->best_streak ?? 0 }})</span>
                </span>
            </div>
        </div>
    </div>

    <!-- MAIN GRID: NPC OPPONENTS & PVP TABLE -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- LEFT COLUMN: NPC OPPONENTS (8 COLS) -->
        <div class="lg:col-span-8 space-y-6">

            <!-- Section Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-dumbbell text-purple-600 dark:text-purple-400"></i>
                    <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 dark:text-white">Afdelingskampioenen Uitdagen</h2>
                </div>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-bold">Kosten: 5 Energie &bull; Inzet via contant geld</span>
            </div>

            <!-- NPC Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($npcs as $npcKey => $npc)
                @php
                    $canAfford = (int) ($character->cash ?? 0) >= $npc['bet'];
                    $effectiveStrength = $character->getEffectiveStrength();
                    $meetsStrength = $effectiveStrength >= $npc['min_strength'];
                    $hasEnergy = (int) ($character->energy ?? 0) >= 5;
                    $canPlay = $canAfford && $meetsStrength && $hasEnergy;
                @endphp
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200 flex flex-col justify-between p-5 space-y-4">
                    
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl border flex items-center justify-center text-xl shrink-0 shadow-2xs {{ $npc['icon_style'] }}">
                                    <i class="fa-solid {{ $npc['icon'] }}"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-1.5 mb-0.5">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider border {{ $npc['difficulty_color'] }}">
                                            {{ $npc['difficulty'] }}
                                        </span>
                                    </div>
                                    <h3 class="text-sm font-black text-slate-900 dark:text-white leading-snug">{{ $npc['name'] }}</h3>
                                    <p class="text-[11px] font-bold text-purple-700 dark:text-purple-400">{{ $npc['role'] }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Quote Box -->
                        <div class="p-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-[11px] text-slate-600 dark:text-slate-300 italic">
                            &ldquo;{{ $npc['quote'] }}&rdquo;
                        </div>

                        <!-- Requirements & Rewards Pill Grid -->
                        <div class="grid grid-cols-2 gap-2 text-[11px]">
                            <div class="p-2 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 space-y-0.5">
                                <span class="text-[9px] font-bold uppercase text-slate-400 dark:text-slate-500 block">Inzet / Prijzenpot</span>
                                <span class="font-extrabold text-slate-900 dark:text-white">€ {{ number_format($npc['bet'], 0, ',', '.') }}</span>
                                <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold block">Winst: € {{ number_format($npc['win_payout'], 0, ',', '.') }}</span>
                            </div>
                            <div class="p-2 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 space-y-0.5">
                                <span class="text-[9px] font-bold uppercase text-slate-400 dark:text-slate-500 block">Beloning & Eis</span>
                                <span class="font-extrabold text-purple-700 dark:text-purple-400">+{{ $npc['xp'] }} XP &bull; +{{ $npc['respect'] }} Respect</span>
                                <span class="text-[10px] font-bold block {{ $meetsStrength ? 'text-slate-500 dark:text-slate-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    Min. {{ $npc['min_strength'] }} Kracht (Jij: <span x-text="playerStrength">{{ $effectiveStrength }}</span>)
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                        @if($character->isComatose())
                            <button type="button" disabled class="w-full py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-rose-500 border border-slate-200/80 dark:border-slate-700/60 text-xs font-bold cursor-not-allowed">
                                Code Blauw (Geen Armworstelen)
                            </button>
                        @elseif($character->isImprisoned())
                            <button type="button" disabled class="w-full py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-amber-500 border border-slate-200/80 dark:border-slate-700/60 text-xs font-bold cursor-not-allowed">
                                Isoleercel (Geen Toegang)
                            </button>
                        @elseif(!$meetsStrength)
                            <button type="button" disabled class="w-full py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 text-xs font-bold cursor-not-allowed">
                                Onvoldoende Kracht (min. {{ $npc['min_strength'] }})
                            </button>
                        @elseif(!$canAfford)
                            <button type="button" disabled class="w-full py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 text-xs font-bold cursor-not-allowed">
                                Onvoldoende Contant Geld (€ {{ number_format($npc['bet'], 0, ',', '.') }})
                            </button>
                        @elseif(!$hasEnergy)
                            <button type="button" disabled class="w-full py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 text-xs font-bold cursor-not-allowed">
                                Onvoldoende Energie (5 vereist)
                            </button>
                        @else
                            <button type="button" 
                                    x-show="cooldownSeconds <= 0 && playerCash >= {{ $npc['bet'] }} && playerEnergy >= 5 && playerStrength >= {{ $npc['min_strength'] }}"
                                    @click="startMatch('{{ $npcKey }}')" 
                                    class="w-full py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white text-xs font-black transition-all duration-150 cursor-pointer shadow-xs flex items-center justify-center gap-2">
                                <i class="fa-solid fa-hand-back-fist text-amber-300"></i>
                                <span>Tafelduel Aangaan (€ {{ number_format($npc['bet'], 0, ',', '.') }})</span>
                            </button>
                            <button type="button"
                                    x-show="cooldownSeconds <= 0 && playerCash < {{ $npc['bet'] }}"
                                    x-cloak
                                    disabled
                                    class="w-full py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 text-xs font-bold cursor-not-allowed">
                                Onvoldoende Contant Geld (€ {{ number_format($npc['bet'], 0, ',', '.') }})
                            </button>
                            <button type="button"
                                    x-show="cooldownSeconds <= 0 && playerCash >= {{ $npc['bet'] }} && playerEnergy < 5"
                                    x-cloak
                                    disabled
                                    class="w-full py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 text-xs font-bold cursor-not-allowed">
                                Onvoldoende Energie (5 vereist)
                            </button>
                            <button type="button" 
                                    x-show="cooldownSeconds > 0"
                                    x-cloak
                                    disabled 
                                    class="w-full py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/60 text-slate-400 dark:text-slate-500 text-xs font-bold flex items-center justify-center gap-2 cursor-not-allowed">
                                <i class="fa-solid fa-hourglass-half text-amber-500"></i>
                                <span>Spieren Herstellen (<span x-text="cooldownSeconds + 's'"></span>)</span>
                            </button>
                        @endif
                    </div>

                </div>
                @endforeach
            </div>

            <!-- PVP OPEN TABLE CHALLENGES -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-6 space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm border border-purple-200/80 dark:border-purple-800/70 shadow-2xs">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white">De Open Tafel (Spelers Uitdagingen)</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Daag een willekeurige medepatiënt uit voor een rechtstreekse armpjedrukpot.</p>
                        </div>
                    </div>
                </div>

                <!-- Own Open Challenge Banner (if any) -->
                @if($myOpenChallenge)
                <div class="p-4 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xs">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-dot text-purple-600 dark:text-purple-400 text-base animate-pulse"></i>
                        <div>
                            <h4 class="text-xs font-black text-purple-950 dark:text-purple-200">Jouw Uitdaging Ligt Open op Tafel</h4>
                            <p class="text-[11px] text-purple-800 dark:text-purple-300">Inzet: <strong>€ {{ number_format($myOpenChallenge->bet_amount, 0, ',', '.') }}</strong> (Gereserveerd in de pot)</p>
                        </div>
                    </div>
                    <form action="{{ route('armwrestling.challenge.cancel', $myOpenChallenge->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer shadow-xs">
                            Annuleren & Geld Terugvragen
                        </button>
                    </form>
                </div>
                @else
                <!-- Create Challenge Form -->
                @php
                    $cashOnHand = (int) ($character->cash ?? 0);
                    $canCreateAnyChallenge = $cashOnHand >= 2500 && !$character->isImprisoned() && !$character->isComatose();
                @endphp
                <form action="{{ route('armwrestling.challenge.create') }}" method="POST" class="p-4 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xs">
                    @csrf
                    <div>
                        <h4 class="text-xs font-black text-slate-900 dark:text-white">Zet Jouw Geld op de Tafel</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Kies een inzetbedrag (betaald met contant geld op zak) en wacht tot een tegenstander de handschoen oppakt.</p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                        <select name="bet_amount" {{ !$canCreateAnyChallenge ? 'disabled' : '' }} class="w-full sm:w-auto text-xs font-bold bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-slate-100 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 disabled:opacity-50 disabled:cursor-not-allowed transition">
                            @foreach([2500, 5000, 10000, 25000, 50000, 100000] as $betOption)
                                <option value="{{ $betOption }}" {{ $cashOnHand < $betOption ? 'disabled' : '' }} {{ ($betOption === 10000 && $cashOnHand >= 10000) || ($betOption === 2500 && $cashOnHand < 10000) ? 'selected' : '' }}>
                                    € {{ number_format($betOption, 0, ',', '.') }}{{ $cashOnHand < $betOption ? ' (Te weinig contant)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" {{ !$canCreateAnyChallenge ? 'disabled' : '' }} class="w-full sm:w-auto px-4 py-2 rounded-xl {{ $canCreateAnyChallenge ? 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white cursor-pointer' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed' }} text-xs font-black transition-all duration-150 shadow-xs shrink-0">
                            {{ $canCreateAnyChallenge ? 'Plaats Uitdaging' : 'Onvoldoende Contant' }}
                        </button>
                    </div>
                </form>
                @endif

                <!-- Open Challenges List -->
                <div class="space-y-2">
                    <span class="text-[10px] font-black uppercase text-slate-400 dark:text-slate-500 tracking-wider block">Beschikbare Uitdagingen op Tafel:</span>
                    @forelse($openChallenges as $open)
                    @php
                        $canAffordChallenge = (int) ($character?->cash ?? 0) >= $open->bet_amount;
                        $hasEnergyForChallenge = (int) ($character?->energy ?? 0) >= 5;
                        $canAcceptChallenge = $canAffordChallenge && $hasEnergyForChallenge && !($character?->isImprisoned() ?? false) && !($character?->isComatose() ?? false);
                    @endphp
                    <div class="p-3 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 shadow-xs flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $open->challenger?->avatar_url ?? asset('assets/characters/avatar_male_1.jpg') }}" alt="Avatar" class="w-9 h-9 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shrink-0">
                            <div>
                                <h5 class="text-xs font-black text-slate-900 dark:text-white">{{ $open->challenger?->name ?? 'Patiënt' }}</h5>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400">Lvl {{ $open->challenger?->level ?? 1 }} &bull; Respect: {{ number_format($open->challenger?->respect ?? 0, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-black text-purple-700 dark:text-purple-400">Pot: € {{ number_format($open->bet_amount * 2, 0, ',', '.') }}</span>
                            <form action="{{ route('armwrestling.challenge.accept', $open->id) }}" method="POST">
                                @csrf
                                <button type="submit" {{ !$canAcceptChallenge ? 'disabled' : '' }} class="px-3.5 py-1.5 rounded-xl {{ $canAcceptChallenge ? 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white cursor-pointer' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed' }} text-xs font-black transition-all duration-150 shadow-xs flex items-center gap-1.5">
                                    <i class="fa-solid fa-hand-fist {{ $canAcceptChallenge ? 'text-amber-300' : 'text-slate-400' }} text-[10px]"></i>
                                    <span>
                                        @if(!$canAffordChallenge)
                                             Onvoldoende Contant (€ {{ number_format($open->bet_amount, 0, ',', '.') }})
                                        @elseif(!$hasEnergyForChallenge)
                                            Onvoldoende Energie (5 EP)
                                        @else
                                            Aannemen (€ {{ number_format($open->bet_amount, 0, ',', '.') }})
                                        @endif
                                    </span>
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="p-4 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-center text-xs text-slate-500 dark:text-slate-400 font-bold">
                        Er liggen momenteel geen open uitdagingen van andere patiënten op tafel. Plaats er zelf een!
                    </div>
                    @endforelse
                </div>

            </div>

        </div>

        <!-- RIGHT COLUMN: LEADERBOARD & RECENT MATCHES (4 COLS) -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Top Tafel Kampioenen -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-5 space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <i class="fa-solid fa-trophy text-amber-500"></i>
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white">Top 5 Tafel Kampioenen</h3>
                </div>

                <div class="space-y-2.5">
                    @forelse($topChampions as $idx => $champ)
                    <div class="flex items-center justify-between text-xs p-2.5 rounded-xl {{ $idx === 0 ? 'bg-amber-50/60 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/60' : 'bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70' }}">
                        <div class="flex items-center gap-2.5">
                            <span class="w-5 h-5 rounded-full flex items-center justify-center font-black text-[10px] {{ $idx === 0 ? 'bg-amber-400 text-slate-950' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                                {{ $idx + 1 }}
                            </span>
                            <span class="font-extrabold text-slate-900 dark:text-white">{{ $champ->character->name ?? 'Onbekend' }}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-black text-purple-700 dark:text-purple-400 block">{{ $champ->wins }} winst</span>
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 block">Reeks: {{ $champ->best_streak }}</span>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 dark:text-slate-500 italic text-center py-2">Nog geen overwinningen geregistreerd.</p>
                    @endforelse
                </div>
            </div>

            <!-- Recente Tafel Duels -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-5 space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <i class="fa-solid fa-clock-rotate-left text-slate-500"></i>
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white">Recente Tafeluitslagen</h3>
                </div>

                <div class="space-y-2">
                    @forelse($recentMatches as $m)
                    <div class="p-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-[11px] space-y-1">
                        <div class="flex items-center justify-between font-bold text-slate-800 dark:text-slate-200">
                            <span>{{ $m->challenger?->name ?? 'Patiënt' }}</span>
                            <span class="text-slate-400">vs</span>
                            <span>{{ $m->type === 'npc' ? ($npcs[$m->npc_key]['name'] ?? 'NPC') : ($m->opponent?->name ?? 'Tegenstander') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-slate-500 dark:text-slate-400">
                            <span>Winnaar: <strong class="text-emerald-700 dark:text-emerald-400">{{ $m->winner?->name ?? ($m->type === 'npc' ? ($npcs[$m->npc_key]['name'] ?? 'NPC') : 'Niemand') }}</strong></span>
                            <span>Inzet: € {{ number_format($m->bet_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 dark:text-slate-500 italic text-center py-2">Geen recente wedstrijden.</p>
                    @endforelse
                </div>
            </div>

            <!-- Fitness Tips & Krachthonk Banner -->
            <div class="rounded-2xl p-5 bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-950/40 dark:to-indigo-950/40 border border-purple-200/80 dark:border-purple-800/70 space-y-3 text-slate-800 dark:text-slate-200">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-lightbulb text-purple-600 dark:text-purple-400"></i>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white">Kliniek Kracht Tip</h4>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                    Jouw <strong>Kracht</strong> bepaalt hoeveel druk jij per slag uitoefent op de arm van je tegenstander. Jouw <strong>Discipline</strong> houdt je timing scherp op de sweet-spot en je <strong>Conditie</strong> voorkomt uitputting.
                </p>
                <a href="{{ route('combat_training.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-purple-700 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 transition">
                    <span>Naar de Vechtoefening om te trainen</span> &raquo;
                </a>
            </div>

        </div>

    </div>

    <!-- INTERACTIVE SHOWDOWN MODAL (ALPINE.JS DRIVEN) -->
    <div x-show="isMatchActive || isSubmitting || matchResult !== null" x-cloak
         @keydown.window="handleKeydown($event)"
         @keyup.window="handleKeyup($event)"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 pb-24 lg:pb-4 bg-slate-950/80 backdrop-blur-xs select-none">
        
        <div class="bg-white dark:bg-slate-900 border-2 border-slate-200/90 dark:border-slate-800 rounded-3xl max-w-lg w-full max-h-[85dvh] overflow-y-auto p-6 sm:p-8 shadow-2xl space-y-6 text-slate-800 dark:text-slate-100 relative overflow-hidden">
            
            <!-- Top Match Status Header -->
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-hand-back-fist text-purple-600 dark:text-purple-400 text-lg"></i>
                    <h3 class="text-base font-black text-slate-900 dark:text-white">Tafelduel Showdown</h3>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-black text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/60 px-2.5 py-1 rounded-xl border border-rose-200/80 dark:border-rose-800/70" x-text="timeLeft + 's resterend'"></span>
                    <button type="button" tabindex="-1" x-show="isMatchActive && !isSubmitting && !matchResult" @click="cancelMatch()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-sm p-1 ml-1 cursor-pointer" title="Sluiten / Opgeven">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <!-- Face-off Banner -->
            <div class="flex items-center justify-between gap-4 py-2">
                <!-- Player Side -->
                <div class="text-center space-y-1">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-purple-100 dark:bg-purple-950/70 border-2 border-purple-300 dark:border-purple-700 flex items-center justify-center text-xl text-purple-700 dark:text-purple-300 shadow-sm font-black">
                        Jij
                    </div>
                    <span class="text-xs font-black text-slate-900 dark:text-white block truncate max-w-[100px]">{{ $character->name }}</span>
                    <span class="text-[10px] font-bold text-purple-700 dark:text-purple-400 block">Kracht: {{ $character->getEffectiveStrength() }}</span>
                </div>

                <!-- VS Badge -->
                <div class="text-center">
                    <span class="w-9 h-9 rounded-full bg-slate-900 dark:bg-purple-600 text-amber-300 flex items-center justify-center text-xs font-black shadow-xs mx-auto">
                        VS
                    </span>
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 mt-1 block">Tafelkraker</span>
                </div>

                <!-- Opponent Side -->
                <div class="text-center space-y-1">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-700 flex items-center justify-center text-xl text-slate-700 dark:text-slate-300 shadow-sm font-black">
                        <i class="fa-solid" :class="activeNpc ? activeNpc.icon : 'fa-skull'"></i>
                    </div>
                    <span class="text-xs font-black text-slate-900 dark:text-white block truncate max-w-[100px]" x-text="activeNpc ? activeNpc.name : 'Tegenstander'"></span>
                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 block" x-text="'Kracht: ' + (activeNpc ? activeNpc.npc_strength : '??')"></span>
                </div>
            </div>

            <!-- Live Arm Wrestling Progress Bar -->
            <div class="space-y-2">
                <div class="flex items-center justify-between text-[11px] font-extrabold">
                    <span class="text-rose-600 dark:text-rose-400 flex items-center gap-1">
                        <i class="fa-solid fa-angle-left"></i> Tegenstander Druk
                    </span>
                    <span class="text-purple-700 dark:text-purple-400" x-text="Math.round(armPosition) + '% Dominantie'"></span>
                    <span class="text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                        Jouw Tafelwinst <i class="fa-solid fa-angle-right"></i>
                    </span>
                </div>

                <!-- The Heavy Wooden Table Meter -->
                <div class="h-6 w-full bg-slate-200 dark:bg-slate-800 rounded-full p-1 border border-slate-300 dark:border-slate-700 relative overflow-hidden shadow-inner">
                    <!-- Progress Bar -->
                    <div class="h-full rounded-full transition-all duration-75"
                         :class="armPosition > 50 ? 'bg-gradient-to-r from-amber-400 to-emerald-500' : 'bg-gradient-to-r from-rose-500 to-amber-400'"
                         :style="'width: ' + armPosition + '%'">
                    </div>

                    <!-- Center 50% Marker -->
                    <div class="absolute inset-y-0 left-1/2 w-0.5 bg-slate-600 dark:bg-slate-400 -translate-x-1/2"></div>
                </div>
            </div>

            <!-- Sweet-Spot Timing Oscillating Bar -->
            <div class="space-y-1.5 p-3 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-center">
                <span class="text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 tracking-wider block">Timing Sweet-Spot (Groene Zone = 1.45x Krachtboost):</span>
                <div class="h-3 w-full bg-slate-200 dark:bg-slate-700 rounded-full relative overflow-hidden">
                    <!-- Green Sweet Spot in Center -->
                    <div class="absolute inset-y-0 left-[35%] right-[35%] bg-emerald-400 rounded"></div>
                    <!-- Moving Needle -->
                    <div class="absolute inset-y-0 w-2.5 bg-purple-700 dark:bg-purple-400 rounded-full shadow-xs transition-none"
                         :style="'left: ' + sweetSpotPos + '%'"></div>
                </div>
            </div>

            <!-- Big Slam Power Button -->
            <div class="space-y-2">
                <button type="button" tabindex="-1" @click="pushArm(false)"
                        class="w-full py-4 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white font-black text-base transition-all duration-150 cursor-pointer shadow-lg flex items-center justify-center gap-2 touch-manipulation select-none">
                    <i class="fa-solid fa-hand-fist text-xl text-amber-300"></i>
                    <span>SLAM DE ARM! (OF SPATIEBALK)</span>
                </button>
                <p class="text-[10px] text-slate-400 dark:text-slate-500 text-center">Tip: Druk op de spatiebalk of klik op de knop om je tegenstander naar 100% te drukken!</p>
            </div>

            <!-- Loading Indicator while submitting -->
            <template x-if="isSubmitting && !matchResult">
                <div class="absolute inset-0 bg-white/95 dark:bg-slate-900/95 backdrop-blur-xs flex flex-col items-center justify-center p-6 text-center space-y-3 z-20 animate-in fade-in duration-150">
                    <div class="w-14 h-14 rounded-2xl bg-purple-50 dark:bg-purple-950/70 border border-purple-200 dark:border-purple-800 flex items-center justify-center text-2xl text-purple-600 dark:text-purple-400 shadow-sm animate-pulse">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-base font-black text-slate-900 dark:text-white">Uitslag Verwerken...</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">De tafel scheidsrechter controleert de slag.</p>
                    </div>
                </div>
            </template>

            <!-- Floating Result Screen (Victory / Defeat) -->
            <template x-if="matchResult">
                <div class="absolute inset-0 bg-white/95 dark:bg-slate-900/95 backdrop-blur-xs flex flex-col items-center justify-center p-6 text-center space-y-4 animate-in fade-in zoom-in-95 duration-200 z-10">
                    <div class="w-16 h-16 rounded-3xl flex items-center justify-center text-3xl shadow-md"
                         :class="matchResult.won ? 'bg-emerald-100 dark:bg-emerald-950/80 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' : 'bg-rose-100 dark:bg-rose-950/80 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800'">
                        <i class="fa-solid" :class="matchResult.won ? 'fa-trophy' : 'fa-skull'"></i>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-xl font-black text-slate-900 dark:text-white" x-text="matchResult.title"></h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed max-w-sm" x-text="matchResult.message"></p>
                    </div>
                    <button type="button" @click="closeMatch()"
                            class="py-2.5 px-6 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white font-black text-xs transition-all duration-150 cursor-pointer shadow-xs">
                        Sluiten & Verder Spelen
                    </button>
                </div>
            </template>

        </div>
    </div>

</div>

<script>
function tbsArmWrestleArena(initialCooldown = 0) {
    return {
        cooldownSeconds: initialCooldown,
        cdInterval: null,
        isMatchActive: false,
        isSubmitting: false,
        activeNpcKey: null,
        activeNpc: null,
        armPosition: 50, // 0 = opponent wins, 100 = player wins
        sweetSpotPos: 50,
        sweetSpotDir: 1,
        timeLeft: 12,
        clicks: 0,
        lastPushTime: 0,
        matchStartTime: 0,
        matchResult: null,
        npcsData: @js($npcs),
        playerCash: {{ (int) ($character->cash ?? 0) }},
        playerEnergy: {{ (int) ($character->energy ?? 0) }},
        playerStrength: {{ $character->getEffectiveStrength() }},
        playerSpeed: {{ (int) ($character->speed ?? 10) }},
        playerStamina: {{ $character->getEffectiveStamina() }},
        gameLoopInterval: null,
        sweetSpotInterval: null,
        timerInterval: null,

        init() {
            if (this.cooldownSeconds > 0) {
                this.startCooldownTimer();
            }
        },

        handleKeydown(e) {
            if (!this.isMatchActive && !this.isSubmitting && !this.matchResult) return;
            if (e.target && ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) return;

            const isSpace = e.code === 'Space' || e.key === ' ' || e.key === 'Spacebar' || e.keyCode === 32;
            const isEnter = e.code === 'Enter' || e.key === 'Enter' || e.keyCode === 13;

            if (isSpace || isEnter) {
                e.preventDefault();
                e.stopPropagation();
                if (document.activeElement && typeof document.activeElement.blur === 'function') {
                    document.activeElement.blur();
                }
                if (this.isMatchActive && !this.isSubmitting && !this.matchResult) {
                    this.pushArm(Boolean(e.repeat));
                }
            }
        },

        handleKeyup(e) {
            if (!this.isMatchActive && !this.isSubmitting && !this.matchResult) return;
            if (e.target && ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) return;

            const isSpace = e.code === 'Space' || e.key === ' ' || e.key === 'Spacebar' || e.keyCode === 32;
            const isEnter = e.code === 'Enter' || e.key === 'Enter' || e.keyCode === 13;

            if (isSpace || isEnter) {
                e.preventDefault();
                e.stopPropagation();
            }
        },

        startCooldownTimer() {
            if (this.cdInterval) clearInterval(this.cdInterval);
            this.cdInterval = setInterval(() => {
                this.cooldownSeconds--;
                if (this.cooldownSeconds <= 0) {
                    clearInterval(this.cdInterval);
                    this.cooldownSeconds = 0;
                }
            }, 1000);
        },

        clearGameIntervals() {
            if (this.gameLoopInterval) {
                clearInterval(this.gameLoopInterval);
                this.gameLoopInterval = null;
            }
            if (this.sweetSpotInterval) {
                clearInterval(this.sweetSpotInterval);
                this.sweetSpotInterval = null;
            }
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
                this.timerInterval = null;
            }
        },

        startMatch(npcKey) {
            if (this.isMatchActive || this.cooldownSeconds > 0 || this.isSubmitting) {
                return;
            }

            const npc = this.npcsData[npcKey];
            if (!npc) {
                return;
            }

            if (this.playerStrength < npc.min_strength || this.playerCash < npc.bet || this.playerEnergy < 5) {
                return;
            }

            if (document.activeElement && typeof document.activeElement.blur === 'function') {
                document.activeElement.blur();
            }

            this.clearGameIntervals();
            this.activeNpcKey = npcKey;
            this.activeNpc = npc;
            this.armPosition = 50;
            this.sweetSpotPos = 20;
            this.sweetSpotDir = 1;
            this.timeLeft = 12;
            this.clicks = 0;
            this.lastPushTime = 0;
            this.matchResult = null;
            this.isSubmitting = false;
            this.isMatchActive = true;
            this.matchStartTime = Date.now();

            this.startGameLoops();
        },

        cancelMatch() {
            if (this.isSubmitting || this.matchResult !== null) return;
            this.clearGameIntervals();
            this.isMatchActive = false;
            this.isSubmitting = false;
            this.matchResult = null;
        },

        startGameLoops() {
            // Main Arm Resistance Physics Loop (runs every 60ms)
            const opponentStrength = this.activeNpc ? this.activeNpc.npc_strength : 10;
            const strengthRatio = Math.min(1.35, Math.max(0.45, opponentStrength / Math.max(10, this.playerStrength)));
            const decay = 0.24 + (strengthRatio * 0.28);
            
            this.gameLoopInterval = setInterval(() => {
                if (!this.isMatchActive || this.matchResult || this.isSubmitting) return;

                // Wait until player makes their first push (up to 2500ms ready window) before opponent drains below 50%
                if (this.clicks === 0 && (Date.now() - this.matchStartTime) < 2500) {
                    return;
                }

                // Opponent pushes back towards 0
                this.armPosition = Math.max(0, this.armPosition - decay);

                if (this.armPosition <= 0) {
                    this.endMatch(false, 'Helaas! Je arm is tegen de tafel gedrukt.');
                }
            }, 60);

            // Sweet Spot Needle Animation Loop
            this.sweetSpotInterval = setInterval(() => {
                if (!this.isMatchActive || this.matchResult || this.isSubmitting) return;
                
                this.sweetSpotPos += (this.sweetSpotDir * 2.5);
                if (this.sweetSpotPos >= 95) {
                    this.sweetSpotPos = 95;
                    this.sweetSpotDir = -1;
                } else if (this.sweetSpotPos <= 5) {
                    this.sweetSpotPos = 5;
                    this.sweetSpotDir = 1;
                }
            }, 25);

            // Countdown Timer Loop
            this.timerInterval = setInterval(() => {
                if (!this.isMatchActive || this.matchResult || this.isSubmitting) return;
                if (this.clicks === 0 && (Date.now() - this.matchStartTime) < 1500) return;

                this.timeLeft--;
                if (this.timeLeft <= 0) {
                    // Time ran out! The player with position > 50 wins on dominance
                    if (this.armPosition > 50) {
                        this.endMatch(true, 'Tijd om! Jij had de overhand en wint het duel op punten!');
                    } else {
                        this.endMatch(false, 'Tijd om! Je tegenstander hield stand en claimt de pot.');
                    }
                }
            }, 1000);
        },

        pushArm(isRepeat = false) {
            if (!this.isMatchActive || this.matchResult || this.isSubmitting) return;

            const now = Date.now();
            const minInterval = isRepeat ? 140 : 40;
            if (this.lastPushTime && (now - this.lastPushTime < minInterval)) {
                return;
            }
            this.lastPushTime = now;

            this.clicks++;

            // Calculate balanced push power relative to opponent strength
            const opponentStrength = this.activeNpc ? this.activeNpc.npc_strength : 10;
            const playerAdvantage = Math.min(2.0, Math.max(0.75, this.playerStrength / Math.max(10, opponentStrength)));
            let basePush = 3.2 + (playerAdvantage * 1.6);

            // Check if needle is inside the sweet spot (35% - 65%)
            const inSweetSpot = this.sweetSpotPos >= 35 && this.sweetSpotPos <= 65;
            if (inSweetSpot) {
                basePush *= 1.45; // Critical Hit!
            }

            basePush = Math.min(9.0, basePush);
            this.armPosition = Math.min(100, this.armPosition + basePush);

            if (this.armPosition >= 100) {
                this.endMatch(true, 'KABOEM! Je hebt de arm van ' + this.activeNpc.name + ' met brute kracht tegen het hout geslamd!');
            }
        },

        endMatch(won, reason) {
            if (this.isSubmitting || this.matchResult) return;
            this.isSubmitting = true;

            this.clearGameIntervals();

            const durationMs = Math.max(600, Date.now() - this.matchStartTime);
            const durationSec = Math.max(1, Math.round(durationMs / 1000));

            // Call Backend to register match and claim rewards
            fetch('{{ route('armwrestling.npc.finish') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    npc_key: this.activeNpcKey,
                    won: won,
                    clicks: this.clicks,
                    duration_seconds: durationSec,
                    duration_ms: durationMs,
                })
            })
            .then(res => {
                return res.json().catch(() => ({
                    success: false,
                    message: 'Er ging iets mis met de serververbinding (HTTP ' + res.status + ').'
                }));
            })
            .then(data => {
                this.isSubmitting = false;
                if (data.cash !== undefined) {
                    this.playerCash = data.cash;
                }
                if (data.energy !== undefined) {
                    this.playerEnergy = data.energy;
                }
                if (data.strength !== undefined) {
                    this.playerStrength = data.strength;
                }
                if (data.cooldown) {
                    this.cooldownSeconds = data.cooldown;
                    this.startCooldownTimer();
                }
                if (data.success) {
                    if (window.dispatchEvent) {
                        window.dispatchEvent(new CustomEvent('stats-updated', {
                            detail: { cash: data.cash, energy: data.energy }
                        }));
                    }
                    this.matchResult = {
                        won: Boolean(data.won),
                        title: data.title || (data.won ? 'GEWELDIGE OVERWINNING!' : 'DUEL VERLOREN'),
                        message: data.message
                    };
                } else {
                    this.matchResult = {
                        won: false,
                        title: 'DUEL NIET VERWERKT',
                        message: data.message || 'Er is een fout opgetreden bij het verwerken van de uitslag.'
                    };
                }
            })
            .catch(() => {
                this.isSubmitting = false;
                this.matchResult = {
                    won: false,
                    title: 'VERBINDINGSFOUT',
                    message: 'Er ging iets mis met de verbinding. Probeer het opnieuw.'
                };
            });
        },

        closeMatch() {
            this.isMatchActive = false;
            this.isSubmitting = false;
            this.matchResult = null;
            // Reload page to update cash, energy and stats
            window.location.reload();
        }
    };
}
</script>
@endsection
