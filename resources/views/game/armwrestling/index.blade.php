@extends('layouts.app')

@section('title', 'Illegale Armpjedruk Arena - TBS Kliniek')

@section('content')
<div class="space-y-6" x-data="tbsArmWrestleArena()">

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2 shadow-2xs">
        <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center gap-2 shadow-2xs">
        <i class="fa-solid fa-triangle-exclamation text-rose-600 text-sm"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Clean TBS Kliniek Title Banner -->
    <div class="rounded-2xl bg-white p-6 sm:p-8 text-slate-800 shadow-xs border border-slate-200 relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-50 border border-purple-100 flex items-center justify-center text-2xl text-purple-600 shadow-2xs shrink-0">
                    <i class="fa-solid fa-hand-back-fist"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-purple-700 bg-purple-50 px-2.5 py-0.5 rounded-md border border-purple-200">
                            Krachthonk & Minigames
                        </span>
                        <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200">
                            1-op-1 Showdown
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-1">
                        De Illegale Armpjedruk Arena
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5 max-w-2xl leading-relaxed">
                        Test je spierkracht, uithoudingsvermogen en reflexen aan de zware houten tafel in het krachthonk. Daag de beruchtste afdelingsbazen uit voor direct prijzengeld of leg jouw inzet op tafel tegen medepatiënten!
                    </p>
                </div>
            </div>

            <!-- Stats Pill Counters -->
            <div class="flex flex-wrap items-center gap-2 text-xs font-bold shrink-0">
                <span class="bg-purple-50 text-purple-700 px-3.5 py-2 rounded-xl border border-purple-200 shadow-2xs flex items-center gap-1.5">
                    <i class="fa-solid fa-trophy text-purple-600"></i>
                    <span>{{ $stat->wins }} Gewonnen</span>
                </span>
                <span class="bg-slate-100 text-slate-700 px-3.5 py-2 rounded-xl border border-slate-200 shadow-2xs flex items-center gap-1.5">
                    <i class="fa-solid fa-skull text-slate-500"></i>
                    <span>{{ $stat->losses }} Verloren</span>
                </span>
                <span class="bg-amber-50 text-amber-800 px-3.5 py-2 rounded-xl border border-amber-200 shadow-2xs flex items-center gap-1.5">
                    <i class="fa-solid fa-fire text-amber-500"></i>
                    <span>Reeks: {{ $stat->current_streak }} (Beste: {{ $stat->best_streak }})</span>
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
                    <i class="fa-solid fa-dumbbell text-purple-600"></i>
                    <h2 class="text-sm font-black uppercase tracking-wider text-slate-900">Afdelingskampioenen Uitdagen</h2>
                </div>
                <span class="text-xs text-slate-500 font-bold">Kosten: 5 Energie per duel</span>
            </div>

            <!-- NPC Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($npcs as $npcKey => $npc)
                @php
                    $canAfford = $character->cash >= $npc['bet'];
                    $meetsStrength = $character->strength >= $npc['min_strength'];
                    $hasEnergy = $character->energy >= 5;
                    $canPlay = $canAfford && $meetsStrength && $hasEnergy;
                @endphp
                <div class="bg-white rounded-xl border border-slate-200 shadow-xs hover:border-purple-300 transition flex flex-col justify-between p-5 space-y-4">
                    
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl border flex items-center justify-center text-xl shrink-0 shadow-2xs {{ $npc['icon_style'] }}">
                                    <i class="fa-solid {{ $npc['icon'] }}"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-1.5 mb-0.5">
                                        <span class="px-2 py-0.2 rounded text-[10px] font-black uppercase tracking-wider border {{ $npc['difficulty_color'] }}">
                                            {{ $npc['difficulty'] }}
                                        </span>
                                    </div>
                                    <h3 class="text-sm font-black text-slate-900 leading-snug">{{ $npc['name'] }}</h3>
                                    <p class="text-[11px] font-bold text-purple-700">{{ $npc['role'] }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Quote Box -->
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100 text-[11px] text-slate-600 italic">
                            &ldquo;{{ $npc['quote'] }}&rdquo;
                        </div>

                        <!-- Requirements & Rewards Pill Grid -->
                        <div class="grid grid-cols-2 gap-2 text-[11px]">
                            <div class="p-2 rounded-lg bg-slate-50 border border-slate-100 space-y-0.5">
                                <span class="text-[9px] font-bold uppercase text-slate-400 block">Inzet / Prijzenpot</span>
                                <span class="font-extrabold text-slate-900">€ {{ number_format($npc['bet'], 0, ',', '.') }}</span>
                                <span class="text-[10px] text-emerald-600 font-bold block">Winst: € {{ number_format($npc['win_payout'], 0, ',', '.') }}</span>
                            </div>
                            <div class="p-2 rounded-lg bg-slate-50 border border-slate-100 space-y-0.5">
                                <span class="text-[9px] font-bold uppercase text-slate-400 block">Beloning & Eis</span>
                                <span class="font-extrabold text-purple-700">+{{ $npc['xp'] }} XP &bull; +{{ $npc['respect'] }} Respect</span>
                                <span class="text-[10px] font-bold block {{ $meetsStrength ? 'text-slate-500' : 'text-rose-600' }}">
                                    Min. {{ $npc['min_strength'] }} Kracht (Jij: {{ $character->strength }})
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="pt-2 border-t border-slate-100">
                        @if(!$meetsStrength)
                            <button type="button" disabled class="w-full py-2.5 rounded-xl bg-slate-100 text-slate-400 text-xs font-bold cursor-not-allowed">
                                Onvoldoende Kracht (min. {{ $npc['min_strength'] }})
                            </button>
                        @elseif(!$canAfford)
                            <button type="button" disabled class="w-full py-2.5 rounded-xl bg-slate-100 text-slate-400 text-xs font-bold cursor-not-allowed">
                                Onvoldoende Contanten (€ {{ number_format($npc['bet'], 0, ',', '.') }})
                            </button>
                        @elseif(!$hasEnergy)
                            <button type="button" disabled class="w-full py-2.5 rounded-xl bg-slate-100 text-slate-400 text-xs font-bold cursor-not-allowed">
                                Onvoldoende Energie (5 vereist)
                            </button>
                        @else
                            <button type="button" @click="startMatch('{{ $npcKey }}')" 
                                    class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 active:scale-95 text-white text-xs font-black transition cursor-pointer shadow-xs flex items-center justify-center gap-2">
                                <i class="fa-solid fa-hand-back-fist text-amber-400"></i>
                                <span>Tafelduel Aangaan (€ {{ number_format($npc['bet'], 0, ',', '.') }})</span>
                            </button>
                        @endif
                    </div>

                </div>
                @endforeach
            </div>

            <!-- PVP OPEN TABLE CHALLENGES -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-sm border border-purple-200">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900">De Open Tafel (Spelers Uitdagingen)</h3>
                            <p class="text-xs text-slate-500">Daag een willekeurige medepatiënt uit voor een rechtstreekse armpjedrukpot.</p>
                        </div>
                    </div>
                </div>

                <!-- Own Open Challenge Banner (if any) -->
                @if($myOpenChallenge)
                <div class="p-4 rounded-xl bg-purple-50/70 border border-purple-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-dot text-purple-600 text-base animate-pulse"></i>
                        <div>
                            <h4 class="text-xs font-black text-purple-950">Jouw Uitdaging Ligt Open op Tafel</h4>
                            <p class="text-[11px] text-purple-800">Inzet: <strong>€ {{ number_format($myOpenChallenge->bet_amount, 0, ',', '.') }}</strong> (Gereserveerd in de pot)</p>
                        </div>
                    </div>
                    <form action="{{ route('armwrestling.challenge.cancel', $myOpenChallenge->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer shadow-2xs">
                            Annuleren & Geld Terugvragen
                        </button>
                    </form>
                </div>
                @else
                <!-- Create Challenge Form -->
                <form action="{{ route('armwrestling.challenge.create') }}" method="POST" class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    @csrf
                    <div>
                        <h4 class="text-xs font-black text-slate-900">Zet Jouw Geld op de Tafel</h4>
                        <p class="text-[11px] text-slate-500">Kies een inzetbedrag en wacht tot een tegenstander de handschoen oppakt.</p>
                    </div>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <select name="bet_amount" class="text-xs font-bold bg-white border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:ring-1 focus:ring-purple-500">
                            <option value="2500">€ 2.500</option>
                            <option value="5000">€ 5.000</option>
                            <option value="10000" selected>€ 10.000</option>
                            <option value="25000">€ 25.000</option>
                            <option value="50000">€ 50.000</option>
                            <option value="100000">€ 100.000</option>
                        </select>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-black transition cursor-pointer shadow-2xs shrink-0">
                            Plaats Uitdaging
                        </button>
                    </div>
                </form>
                @endif

                <!-- Open Challenges List -->
                <div class="space-y-2">
                    <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider block">Beschikbare Uitdagingen op Tafel:</span>
                    @forelse($openChallenges as $open)
                    <div class="p-3 rounded-xl bg-white border border-slate-200 shadow-2xs flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $open->challenger->avatar_url }}" alt="Avatar" class="w-9 h-9 rounded-xl object-cover border border-slate-200 shrink-0">
                            <div>
                                <h5 class="text-xs font-black text-slate-900">{{ $open->challenger->name }}</h5>
                                <p class="text-[10px] text-slate-500">Lvl {{ $open->challenger->level }} &bull; Respect: {{ number_format($open->challenger->respect, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-black text-purple-700">Pot: € {{ number_format($open->bet_amount * 2, 0, ',', '.') }}</span>
                            <form action="{{ route('armwrestling.challenge.accept', $open->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition cursor-pointer shadow-2xs flex items-center gap-1.5">
                                    <i class="fa-solid fa-hand-fist text-amber-300 text-[10px]"></i>
                                    <span>Aannemen (€ {{ number_format($open->bet_amount, 0, ',', '.') }})</span>
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-center text-xs text-slate-500 font-bold">
                        Er liggen momenteel geen open uitdagingen van andere patiënten op tafel. Plaats er zelf een!
                    </div>
                    @endforelse
                </div>

            </div>

        </div>

        <!-- RIGHT COLUMN: LEADERBOARD & RECENT MATCHES (4 COLS) -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Top Tafel Kampioenen -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                    <i class="fa-solid fa-trophy text-amber-500"></i>
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-900">Top 5 Tafel Kampioenen</h3>
                </div>

                <div class="space-y-2.5">
                    @forelse($topChampions as $idx => $champ)
                    <div class="flex items-center justify-between text-xs p-2 rounded-xl {{ $idx === 0 ? 'bg-amber-50/60 border border-amber-200' : 'bg-slate-50' }}">
                        <div class="flex items-center gap-2.5">
                            <span class="w-5 h-5 rounded-full flex items-center justify-center font-black text-[10px] {{ $idx === 0 ? 'bg-amber-400 text-slate-950' : 'bg-slate-200 text-slate-700' }}">
                                {{ $idx + 1 }}
                            </span>
                            <span class="font-extrabold text-slate-900">{{ $champ->character->name ?? 'Onbekend' }}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-black text-purple-700 block">{{ $champ->wins }} winst</span>
                            <span class="text-[10px] text-slate-400 block">Reeks: {{ $champ->best_streak }}</span>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 italic text-center py-2">Nog geen overwinningen geregistreerd.</p>
                    @endforelse
                </div>
            </div>

            <!-- Recente Tafel Duels -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                    <i class="fa-solid fa-clock-rotate-left text-slate-500"></i>
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-900">Recente Tafeluitslagen</h3>
                </div>

                <div class="space-y-2">
                    @forelse($recentMatches as $m)
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-[11px] space-y-1">
                        <div class="flex items-center justify-between font-bold text-slate-800">
                            <span>{{ $m->challenger->name ?? 'Patiënt' }}</span>
                            <span class="text-slate-400">vs</span>
                            <span>{{ $m->type === 'npc' ? ($npcs[$m->npc_key]['name'] ?? 'NPC') : ($m->opponent->name ?? 'Tegenstander') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-slate-500">
                            <span>Winnaar: <strong class="text-emerald-700">{{ $m->winner->name ?? 'Niemand' }}</strong></span>
                            <span>Inzet: € {{ number_format($m->bet_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 italic text-center py-2">Geen recente wedstrijden.</p>
                    @endforelse
                </div>
            </div>

            <!-- Fitness Tips & Krachthonk Banner -->
            <div class="rounded-2xl p-5 bg-gradient-to-br from-purple-50 to-indigo-50 border border-purple-200 space-y-3 text-slate-800">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-lightbulb text-purple-600"></i>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-900">Kliniek Kracht Tip</h4>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Jouw <strong>Kracht</strong> bepaalt hoeveel druk jij per slag uitoefent op de arm van je tegenstander. Jouw <strong>Snelheid</strong> vergroot de sweet-spot van je timingbalk en je <strong>Stamina</strong> voorkomt uitputting.
                </p>
                <a href="{{ route('combat-training.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-purple-700 hover:text-purple-800 transition">
                    <span>Naar het Krachthonk om te trainen</span> &raquo;
                </a>
            </div>

        </div>

    </div>

    <!-- INTERACTIVE SHOWDOWN MODAL (ALPINE.JS DRIVEN) -->
    <div x-show="isMatchActive" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs select-none">
        
        <div class="bg-white border-2 border-slate-300 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-6 text-slate-800 relative overflow-hidden"
             @keydown.space.window.prevent="pushArm()">
            
            <!-- Top Match Status Header -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-hand-back-fist text-purple-600 text-lg"></i>
                    <h3 class="text-base font-black text-slate-900">Tafelduel Showdown</h3>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-black text-rose-600 bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-200" x-text="timeLeft + 's resterend'"></span>
                </div>
            </div>

            <!-- Face-off Banner -->
            <div class="flex items-center justify-between gap-4 py-2">
                <!-- Player Side -->
                <div class="text-center space-y-1">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-purple-100 border-2 border-purple-300 flex items-center justify-center text-xl text-purple-700 shadow-sm font-black">
                        Jij
                    </div>
                    <span class="text-xs font-black text-slate-900 block truncate max-w-[100px]">{{ $character->name }}</span>
                    <span class="text-[10px] font-bold text-purple-700 block">Kracht: {{ $character->strength }}</span>
                </div>

                <!-- VS Badge -->
                <div class="text-center">
                    <span class="w-9 h-9 rounded-full bg-slate-900 text-amber-300 flex items-center justify-center text-xs font-black shadow-xs mx-auto">
                        VS
                    </span>
                    <span class="text-[10px] font-bold text-slate-400 mt-1 block">Tafelkraker</span>
                </div>

                <!-- Opponent Side -->
                <div class="text-center space-y-1">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 border-2 border-slate-300 flex items-center justify-center text-xl text-slate-700 shadow-sm font-black">
                        <i class="fa-solid" :class="activeNpc ? activeNpc.icon : 'fa-skull'"></i>
                    </div>
                    <span class="text-xs font-black text-slate-900 block truncate max-w-[100px]" x-text="activeNpc ? activeNpc.name : 'Tegenstander'"></span>
                    <span class="text-[10px] font-bold text-slate-500 block" x-text="'Kracht: ' + (activeNpc ? activeNpc.npc_strength : '??')"></span>
                </div>
            </div>

            <!-- Live Arm Wrestling Progress Bar -->
            <div class="space-y-2">
                <div class="flex items-center justify-between text-[11px] font-extrabold">
                    <span class="text-rose-600 flex items-center gap-1">
                        <i class="fa-solid fa-angle-left"></i> Tegenstander Pression
                    </span>
                    <span class="text-purple-700" x-text="Math.round(armPosition) + '% Dominantie'"></span>
                    <span class="text-emerald-600 flex items-center gap-1">
                        Jouw Tafelwinst <i class="fa-solid fa-angle-right"></i>
                    </span>
                </div>

                <!-- The Heavy Wooden Table Meter -->
                <div class="h-6 w-full bg-slate-200 rounded-full p-1 border border-slate-300 relative overflow-hidden shadow-inner">
                    <!-- Progress Bar -->
                    <div class="h-full rounded-full transition-all duration-75"
                         :class="armPosition > 50 ? 'bg-gradient-to-r from-amber-400 to-emerald-500' : 'bg-gradient-to-r from-rose-500 to-amber-400'"
                         :style="'width: ' + armPosition + '%'">
                    </div>

                    <!-- Center 50% Marker -->
                    <div class="absolute inset-y-0 left-1/2 w-0.5 bg-slate-600 -translate-x-1/2"></div>
                </div>
            </div>

            <!-- Sweet-Spot Timing Oscillating Bar -->
            <div class="space-y-1.5 p-3 rounded-2xl bg-slate-50 border border-slate-200 text-center">
                <span class="text-[10px] font-black uppercase text-slate-500 tracking-wider block">Timing Sweet-Spot (Kans op 2.5x Critical Slam):</span>
                <div class="h-3 w-full bg-slate-200 rounded-full relative overflow-hidden">
                    <!-- Green Sweet Spot in Center -->
                    <div class="absolute inset-y-0 left-[42%] right-[42%] bg-emerald-400 rounded"></div>
                    <!-- Moving Needle -->
                    <div class="absolute inset-y-0 w-2.5 bg-purple-700 rounded-full shadow-xs transition-none"
                         :style="'left: ' + sweetSpotPos + '%'"></div>
                </div>
            </div>

            <!-- Big Slam Power Button -->
            <div class="space-y-2">
                <button type="button" @click="pushArm()"
                        class="w-full py-4 rounded-2xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-black text-base transition cursor-pointer shadow-lg flex items-center justify-center gap-2">
                    <i class="fa-solid fa-hand-fist text-xl text-amber-300"></i>
                    <span>SLAM DE ARM! (OF SPATIEBALK)</span>
                </button>
                <p class="text-[10px] text-slate-400 text-center">Tip: Klik snel achter elkaar én raak de groene zone voor maximale doordruk!</p>
            </div>

            <!-- Floating Result Screen (Victory / Defeat) -->
            <template x-if="matchResult">
                <div class="absolute inset-0 bg-white/95 backdrop-blur-xs flex flex-col items-center justify-center p-6 text-center space-y-4 animate-in fade-in zoom-in-95 duration-200 z-10">
                    <div class="w-16 h-16 rounded-3xl flex items-center justify-center text-3xl shadow-md"
                         :class="matchResult.won ? 'bg-emerald-100 text-emerald-600 border border-emerald-200' : 'bg-rose-100 text-rose-600 border border-rose-200'">
                        <i class="fa-solid" :class="matchResult.won ? 'fa-trophy' : 'fa-skull'"></i>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-xl font-black text-slate-900" x-text="matchResult.title"></h4>
                        <p class="text-xs text-slate-600 leading-relaxed max-w-sm" x-text="matchResult.message"></p>
                    </div>
                    <button type="button" @click="closeMatch()"
                            class="py-2.5 px-6 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-black text-xs transition cursor-pointer shadow-xs">
                        Sluiten & Verder Spelen
                    </button>
                </div>
            </template>

        </div>
    </div>

</div>

<script>
function tbsArmWrestleArena() {
    return {
        isMatchActive: false,
        activeNpcKey: null,
        activeNpc: null,
        armPosition: 50, // 0 = opponent wins, 100 = player wins
        sweetSpotPos: 50,
        sweetSpotDir: 1,
        timeLeft: 12,
        clicks: 0,
        matchStartTime: 0,
        matchResult: null,
        intervalId: null,
        sweetSpotInterval: null,
        npcs: @json($npcs),
        playerStrength: {{ $character->strength }},
        playerSpeed: {{ $character->speed }},
        playerStamina: {{ $character->stamina }},

        startMatch(npcKey) {
            this.activeNpcKey = npcKey;
            this.activeNpc = this.npcs[npcKey] || null;
            this.armPosition = 50;
            this.timeLeft = 12;
            this.clicks = 0;
            this.matchStartTime = Date.now();
            this.matchResult = null;
            this.isMatchActive = true;

            // Opponent constant pushback interval
            this.intervalId = setInterval(() => {
                if (this.matchResult) return;

                // Time countdown
                this.timeLeft--;
                if (this.timeLeft <= 0) {
                    this.finishDuel(this.armPosition >= 51);
                    return;
                }

                // Opponent push rate based on their strength vs your defense
                const pushPower = (this.activeNpc.npc_strength / 14) + (Math.random() * 2);
                this.armPosition = Math.max(0, this.armPosition - pushPower);

                if (this.armPosition <= 0) {
                    this.finishDuel(false);
                }
            }, 500);

            // Sweet spot needle oscillation
            this.sweetSpotInterval = setInterval(() => {
                if (this.matchResult) return;
                this.sweetSpotPos += (4 * this.sweetSpotDir);
                if (this.sweetSpotPos >= 92) {
                    this.sweetSpotPos = 92;
                    this.sweetSpotDir = -1;
                } else if (this.sweetSpotPos <= 8) {
                    this.sweetSpotPos = 8;
                    this.sweetSpotDir = 1;
                }
            }, 30);
        },

        pushArm() {
            if (!this.isMatchActive || this.matchResult) return;

            this.clicks++;

            // Check if needle is in sweet-spot (40% to 60%)
            const isCrit = (this.sweetSpotPos >= 40 && this.sweetSpotPos <= 60);

            // Calculate push force based on strength
            let force = 4.5 + (this.playerStrength * 0.12);
            if (isCrit) {
                force *= 2.2; // Critical slam!
            }

            this.armPosition = Math.min(100, this.armPosition + force);

            if (this.armPosition >= 100) {
                this.finishDuel(true);
            }
        },

        async finishDuel(didWin) {
            clearInterval(this.intervalId);
            clearInterval(this.sweetSpotInterval);

            const duration = Math.max(1000, Date.now() - this.matchStartTime);

            try {
                const response = await fetch('{{ route('armwrestling.npc.finish') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        npc_key: this.activeNpcKey,
                        won: didWin,
                        clicks: this.clicks,
                        duration_ms: duration
                    })
                });

                const data = await response.json();
                if (data.success) {
                    this.matchResult = data;
                } else {
                    this.matchResult = {
                        won: false,
                        title: 'Foutmelding',
                        message: data.message || 'Er ging iets mis tijdens het duels.'
                    };
                }
            } catch(e) {
                this.matchResult = {
                    won: didWin,
                    title: didWin ? 'Tafelwinst!' : 'Verloren!',
                    message: didWin ? 'Gefeliciteerd met je overwinning!' : 'Volgende keer beter!'
                };
            }
        },

        closeMatch() {
            this.isMatchActive = false;
            window.location.reload();
        }
    };
}
</script>
@endsection
