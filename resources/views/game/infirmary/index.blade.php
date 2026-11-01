@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Top Medical Post Header Banner (Clean Solid Styling) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 sm:p-7 border border-slate-200/90 dark:border-slate-800 shadow-xs relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-800/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-2xl shrink-0 shadow-2xs mt-0.5">
                    <i class="fa-solid fa-hospital-user"></i>
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70">
                            <i class="fa-solid fa-stethoscope"></i> Medische Dienst &bull; Afdeling C
                        </span>
                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">
                            Zuster Ingrid op zaal
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        Ziekenboeg & Medicijnpost
                    </h1>
                    <p class="text-xs text-slate-600 dark:text-slate-300 max-w-2xl leading-relaxed font-medium">
                        Meld je bij het medische loket om gratis te herstellen van opgelopen klappen. Ben je behendig genoeg? Probeer dan een zware pil onder je tong te houden (palmen) om deze later te gebruiken of te verhandelen op de zwarte markt!
                    </p>
                </div>
            </div>

            <!-- Health Status & Palmed Stats Summary -->
            <div class="flex items-center gap-3 shrink-0 self-start md:self-center">
                <div class="p-3 sm:p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-center min-w-[100px]">
                    <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-0.5">Gezondheid</span>
                    <span class="text-lg font-black {{ $character->health < 30 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }} leading-none font-mono">
                        {{ $character->health }}/{{ $character->max_health }}
                    </span>
                </div>
                <div class="p-3 sm:p-3.5 rounded-xl bg-purple-50/70 dark:bg-purple-950/40 border border-purple-200/80 dark:border-purple-800/70 text-center min-w-[100px]">
                    <span class="text-[9px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-300 block mb-0.5">Gepalmd</span>
                    <span class="text-lg font-black text-purple-700 dark:text-purple-300 leading-none font-mono">
                        {{ $character->palmed_pills_count ?? 0 }}x
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN TWO OPTIONS: 1. NETJES INNEMEN  VS  2. PILLEN PALMEN -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Option 1: Netjes Innemen (Health Restore) -->
        <div class="lg:col-span-6 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/50 flex items-center justify-center font-bold text-lg">
                        <i class="fa-solid fa-glass-water"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-extrabold uppercase tracking-wide text-slate-900 dark:text-white">1. Netjes Innemen</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Officiële medische verzorging</p>
                    </div>
                </div>
                <span class="text-[11px] font-black px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/70">
                    0% Risico
                </span>
            </div>

            <div class="space-y-3">
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-medium">
                    Neem een slokje water uit het bekertje en slik de kalmeringstablet netjes door. Zuster Ingrid noteert het in je behandeloverzicht en verzorgt je wonden.
                </p>

                <div class="p-4 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400 font-bold">Herstel Effect:</span>
                        <span class="font-extrabold text-emerald-600 dark:text-emerald-400 font-mono">+45 Gezondheid</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400 font-bold">Kosten:</span>
                        <span class="font-bold text-slate-700 dark:text-slate-200">Gratis (Ziekenfonds)</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400 font-bold">Wachttijd:</span>
                        <span class="font-bold text-slate-700 dark:text-slate-200">4 minuten cooldown</span>
                    </div>
                </div>

                @if($takeCooldown > 0)
                <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/60 flex flex-wrap items-center justify-between gap-3 text-xs">
                    <span class="font-bold text-amber-900 dark:text-amber-200 flex items-center gap-2">
                        <i class="fa-solid fa-clock text-amber-500"></i> Doseerschema actief: wacht nog {{ $takeCooldown }}s
                    </span>
                    <div class="flex flex-wrap items-center gap-2">
                        @if(\App\Http\Controllers\SponsorAdController::areAdsEnabled())
                        <button type="button"
                                @disabled($character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum() || $character->getRemainingAdsToday() <= 0)
                                onclick="window.tbsOpenSponsorAd('cooldown:infirmary_take', () => { window.location.reload(); })"
                                class="px-3 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-[11px] transition shrink-0 cursor-pointer shadow-xs inline-flex items-center gap-1 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fa-solid fa-play text-[10px]"></i>
                            <span>Gratis via Ad</span>
                        </button>
                        @endif
                        <form method="POST" action="{{ route('cooldown.skip') }}" class="inline">
                            @csrf
                            <input type="hidden" name="action" value="infirmary_take">
                            <button type="submit" @disabled($character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum() || ($character->stars ?? 0) < 10) class="btn-star-gold px-3 py-1.5 rounded-xl text-slate-900 dark:text-white font-black text-[11px] transition shrink-0 cursor-pointer shadow-xs inline-flex items-center gap-1 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fa-solid fa-star text-[10px]"></i>
                                <span>Omzeilen (10 ⭐)</span>
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            </div>

            <form action="{{ route('infirmary.take') }}" method="POST">
                @csrf
                <button type="submit" 
                        {{ $takeCooldown > 0 || $character->health >= $character->max_health || $character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum() ? 'disabled' : '' }}
                        class="w-full py-3 px-4 rounded-xl font-black text-xs uppercase tracking-wider transition-all duration-150 text-center shadow-xs flex items-center justify-center gap-2 {{ $takeCooldown > 0 || $character->health >= $character->max_health || $character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum() ? 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 cursor-not-allowed border border-slate-200/80 dark:border-slate-700/60' : 'bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white cursor-pointer active:scale-95' }}">
                    <i class="fa-solid fa-pills"></i>
                    <span>{{ $character->health >= $character->max_health ? 'Gezondheid al Maximaal' : 'Neem Medicijn In (+45 HP)' }}</span>
                </button>
            </form>
        </div>

        <!-- Option 2: Pil Stiekem Palmen (Smuggle Under Tongue) -->
        <div class="lg:col-span-6 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200/60 dark:border-purple-800/50 flex items-center justify-center font-bold text-lg">
                        <i class="fa-solid fa-mask"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-extrabold uppercase tracking-wide text-slate-900 dark:text-white">2. Pil Stiekem Palmen</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Onder je tong bewaren & smokkelen</p>
                    </div>
                </div>
                <span class="text-[11px] font-black px-2.5 py-1 rounded-full bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70">
                    Slaagkans: {{ $palmSuccessRate }}%
                </span>
            </div>

            <div class="space-y-3">
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-medium">
                    Wacht tot Ingrid haar stempel pakt en klem de pil behendig achter je kies. Slaag je? Dan verdwijnt de strip in je broekzak. Word je betrapt? Dan vlieg je 90 seconden in de isoleercel!
                </p>

                <!-- Palm Odds & Stats -->
                <div class="p-4 rounded-xl bg-purple-50/50 dark:bg-slate-800/60 border border-purple-200/60 dark:border-slate-700/70 space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400 font-bold">Mogelijke Buit:</span>
                        <span class="font-extrabold text-purple-700 dark:text-purple-300">Oxazepam, Ritalin of Haloperidol</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400 font-bold">Energie Vereist:</span>
                        <span class="font-black text-amber-600 dark:text-amber-400 font-mono">10 Energie</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400 font-bold">Bonus Invloed:</span>
                        <span class="font-bold text-slate-700 dark:text-slate-200">Discipline & Charisma verhogen kans</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400 font-bold">Cooldown:</span>
                        <span class="font-bold text-amber-600 dark:text-amber-400"><i class="fa-solid fa-clock text-[10px] mr-1"></i>{{ gmdate('i:s', $palmCooldownSeconds ?? 180) }}</span>
                    </div>
                </div>

                @if($palmCooldown > 0)
                <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/60 flex flex-wrap items-center justify-between gap-3 text-xs">
                    <span class="font-bold text-amber-900 dark:text-amber-200 flex items-center gap-2">
                        <i class="fa-solid fa-eye text-amber-500"></i> Ingrid let scherp op: wacht nog {{ $palmCooldown }}s
                    </span>
                    <div class="flex flex-wrap items-center gap-2">
                        @if(\App\Http\Controllers\SponsorAdController::areAdsEnabled())
                        <button type="button"
                                @disabled($character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum() || $character->getRemainingAdsToday() <= 0)
                                onclick="window.tbsOpenSponsorAd('cooldown:infirmary_palm', () => { window.location.reload(); })"
                                class="px-3 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs transition shrink-0 cursor-pointer shadow-xs inline-flex items-center gap-1 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fa-solid fa-play text-[10px]"></i>
                            <span>Gratis via Ad</span>
                        </button>
                        @endif
                        <form method="POST" action="{{ route('cooldown.skip') }}" class="inline">
                            @csrf
                            <input type="hidden" name="action" value="infirmary_palm">
                            <button type="submit" @disabled($character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum() || ($character->stars ?? 0) < 10) class="btn-star-gold px-3 py-1.5 rounded-xl text-slate-900 dark:text-white font-black text-xs transition shrink-0 cursor-pointer shadow-xs inline-flex items-center gap-1 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fa-solid fa-star text-[10px]"></i>
                                <span>Omzeilen (10 ⭐)</span>
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            </div>

            <form action="{{ route('infirmary.palm') }}" method="POST">
                @csrf
                <button type="submit" 
                        {{ $palmCooldown > 0 || $character->energy < 10 || $character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum() ? 'disabled' : '' }}
                        class="w-full py-3 px-4 rounded-xl font-black text-xs uppercase tracking-wider transition-all duration-150 text-center shadow-xs flex items-center justify-center gap-2 {{ $palmCooldown > 0 || $character->energy < 10 || $character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum() ? 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 cursor-not-allowed border border-slate-200/80 dark:border-slate-700/60' : 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white cursor-pointer active:scale-95' }}">
                    <i class="fa-solid fa-hand-holding-hand"></i>
                    <span>Pil Stiekem Palmen (-10 Energie)</span>
                </button>
            </form>
        </div>

    </div>

    <!-- TRAY OVERZICHT: WAT KUN JE PALMEN & JOUW VOORRAAD -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Tray overzicht -->
        <div class="lg:col-span-7 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
            <div class="flex items-center gap-2.5 border-b border-slate-100 dark:border-slate-800 pb-3">
                <i class="fa-solid fa-tablets text-purple-600 dark:text-purple-400"></i>
                <h3 class="text-sm font-extrabold uppercase tracking-wide text-slate-900 dark:text-white">
                    Medicijnen op de Tray van Ingrid
                </h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                @foreach($pills as $pill)
                <div class="p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-700/70 bg-slate-50/90 dark:bg-slate-800/60 space-y-2">
                    <div class="flex items-center justify-between">
                        <i class="fa-solid {{ $pill->icon }} text-purple-600 dark:text-purple-400 text-lg"></i>
                        <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-md border {{ $pill->rarity === 'epic' ? 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200/80 dark:border-purple-800/70' : ($pill->rarity === 'rare' ? 'bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border-sky-200/80 dark:border-sky-800/70' : 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200/80 dark:border-emerald-800/70') }}">
                            {{ $pill->rarity }}
                        </span>
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-slate-900 dark:text-white">{{ $pill->name }}</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-snug mt-1">{{ $pill->description }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Jouw voorraad pillen -->
        <div class="lg:col-span-5 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <i class="fa-solid fa-box-archive text-emerald-600 dark:text-emerald-400"></i>
                    <h3 class="text-sm font-extrabold uppercase tracking-wide text-slate-900 dark:text-white">
                        Jouw Medicatie Voorraad
                    </h3>
                </div>
                <a href="{{ route('shop.index', ['tab' => 'inventaris']) }}" class="text-xs font-bold text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 hover:underline">
                    Inventaris &raquo;
                </a>
            </div>

            <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                @forelse($ownedPills as $owned)
                <div class="flex items-center justify-between p-3 rounded-xl border border-slate-200/80 dark:border-slate-700/70 bg-slate-50/90 dark:bg-slate-800/60 text-xs">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <i class="fa-solid {{ $owned->item->icon }} text-purple-600 dark:text-purple-400"></i>
                        <span class="font-bold text-slate-900 dark:text-white truncate">{{ $owned->item->name }}</span>
                    </div>
                    <span class="font-mono font-black px-2 py-0.5 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70 text-xs shrink-0">
                        {{ $owned->quantity }}x in bezit
                    </span>
                </div>
                @empty
                <div class="text-center py-6 text-slate-400 dark:text-slate-500 text-xs">
                    <i class="fa-solid fa-prescription-bottle text-2xl mb-2 opacity-40"></i>
                    <p>Je hebt momenteel geen gepalmde pillen in je zakken.</p>
                </div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
