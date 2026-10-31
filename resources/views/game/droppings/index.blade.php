@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Top Header Banner (TBS Kliniek Website Styling) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 sm:p-7 border border-slate-200/90 dark:border-slate-800 shadow-xs relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/70 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl shrink-0 shadow-2xs mt-0.5">
                    <i class="fa-solid fa-plane-arrival"></i>
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/70">
                            <i class="fa-solid fa-boxes-packing"></i> Externe Bevoorrading
                        </span>
                        <span class="text-[10px] font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 rounded-full border border-slate-200/80 dark:border-slate-700">
                            Luchtplaats &bull; Dakdroppings
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        Tennisballen & Nachtelijke Drones
                    </h1>
                    <p class="text-xs text-slate-600 dark:text-slate-300 max-w-2xl leading-relaxed font-medium">
                        Handlangers van buitenaf smokkelen goederen de inrichting in! Vang opengesneden tennisballen over het hek van de luchtplaats of betaal een dronepiloot voor een geruisloze middernacht-dropping op het dak van jouw afdeling.
                    </p>
                </div>
            </div>

            <!-- Stats Summary Badges -->
            <div class="flex items-center gap-3 shrink-0 self-start md:self-center">
                <div class="p-3 sm:p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-center min-w-[100px]">
                    <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block mb-0.5">Onderschept</span>
                    <span class="text-lg font-black text-slate-900 dark:text-white leading-none font-mono">
                        {{ $totalIntercepted }}x
                    </span>
                </div>
                <div class="p-3 sm:p-3.5 rounded-xl bg-emerald-50/90 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/70 text-center min-w-[120px]">
                    <span class="text-[9px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-400 block mb-0.5">Buit Opbrengst</span>
                    <span class="text-lg font-black text-emerald-600 dark:text-emerald-400 leading-none font-mono">
                        €{{ number_format($totalCashMade, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2 Dropping Operations Cards (Clean TBS Kliniek Layout) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- 1. TENNISBAL OVER HET HEK (Dagoperatie) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs flex flex-col justify-between space-y-5 hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="w-12 h-12 rounded-xl bg-lime-50 dark:bg-lime-950/60 text-lime-600 dark:text-lime-400 border border-lime-200/80 dark:border-lime-800/70 flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid fa-baseball"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full bg-lime-50 dark:bg-lime-950/60 text-lime-800 dark:text-lime-300 border border-lime-200/80 dark:border-lime-800/70">
                        Overdag &bull; Luchtplaats
                    </span>
                </div>

                <div>
                    <h2 class="text-base font-black text-slate-900 dark:text-white">Opengesneden Tennisbal Onderscheppen</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                        Een maatje fietst langs het buitenhek en keilt met hoge boog een verzwaarde tennisbal over de betonnen muur. Duik er snel op voordat bewaker De Jong het in de gaten heeft!
                    </p>
                </div>

                <div class="bg-slate-50/90 dark:bg-slate-800/60 rounded-xl p-3.5 space-y-2 text-xs border border-slate-200/80 dark:border-slate-700/70">
                    <div class="flex justify-between text-slate-600 dark:text-slate-300 font-medium">
                        <span><i class="fa-solid fa-bolt text-amber-500 mr-1.5"></i> Energie vereist:</span>
                        <span class="font-bold text-slate-900 dark:text-white">15 Energie</span>
                    </div>
                    <div class="flex justify-between text-slate-600 dark:text-slate-300 font-medium">
                        <span><i class="fa-solid fa-sack-dollar text-emerald-500 mr-1.5"></i> Mogelijke buit:</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">€250 - €1.200 + Sigaretten / Ritalin</span>
                    </div>
                    <div class="flex justify-between text-slate-600 dark:text-slate-300 font-medium">
                        <span><i class="fa-solid fa-shield-halved text-rose-500 mr-1.5"></i> Risico:</span>
                        <span class="font-bold text-rose-600 dark:text-rose-400">10% kans op 5 min Isoleercel</span>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                @if($tennisCooldown > 0)
                    <button disabled class="w-full py-3 px-4 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-not-allowed">
                        <i class="fa-solid fa-clock"></i> Afkoelen (Nog {{ ceil($tennisCooldown / 60) }} min)
                    </button>
                @else
                    <form action="{{ route('droppings.tennisball') }}" method="POST">
                        @csrf
                        <button type="submit" @disabled($character->isImprisoned() || $character->isComatose() || $character->energy < 15) class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95 text-white font-black text-xs uppercase tracking-wider shadow-xs transition-all duration-150 flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-baseball"></i> Sprint & Vang Tennisbal
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- 2. NACHTELIJKE DRONE DROPPING (High Tier) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs flex flex-col justify-between space-y-5 hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="w-12 h-12 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 border border-teal-200/80 dark:border-teal-800/70 flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid fa-helicopter"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full bg-teal-50 dark:bg-teal-950/60 text-teal-800 dark:text-teal-300 border border-teal-200/80 dark:border-teal-800/70">
                        Nacht &bull; Dakraam Dropping
                    </span>
                </div>

                <div>
                    <h2 class="text-base font-black text-slate-900 dark:text-white">Zwarte Markt Drone Dropping</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                        Schakel een gecodeerde dronepiloot in. In het holst van de nacht vliegt een stille quadcopter laag over de bossen en dropt een waterdicht pakket recht door het luchtrooster van jouw paviljoen.
                    </p>
                </div>

                <div class="bg-slate-50/90 dark:bg-slate-800/60 rounded-xl p-3.5 space-y-2 text-xs border border-slate-200/80 dark:border-slate-700/70">
                    <div class="flex justify-between text-slate-600 dark:text-slate-300 font-medium">
                        <span><i class="fa-solid fa-money-bill-wave text-rose-500 mr-1.5"></i> Kosten dronepiloot:</span>
                        <span class="font-bold text-slate-900 dark:text-white">€500 contant + 25 Energie</span>
                    </div>
                    <div class="flex justify-between text-slate-600 dark:text-slate-300 font-medium">
                        <span><i class="fa-solid fa-box-open text-teal-500 mr-1.5"></i> Zware buit:</span>
                        <span class="font-bold text-teal-600 dark:text-teal-400">€1.800 - €4.800 + Zware Medicatie</span>
                    </div>
                    <div class="flex justify-between text-slate-600 dark:text-slate-300 font-medium">
                        <span><i class="fa-solid fa-user-lock text-indigo-500 mr-1.5"></i> Vereiste:</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400">Minimaal Level 3</span>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                @if($droneCooldown > 0)
                    <button disabled class="w-full py-3 px-4 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-not-allowed">
                        <i class="fa-solid fa-clock"></i> Drone Accu Laadt Op (Nog {{ ceil($droneCooldown / 60) }} min)
                    </button>
                @elseif($character->level < 3)
                    <button disabled class="w-full py-3 px-4 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-not-allowed">
                        <i class="fa-solid fa-lock"></i> Vereist Level 3 (Jij bent Level {{ $character->level }})
                    </button>
                @else
                    <form action="{{ route('droppings.drone') }}" method="POST">
                        @csrf
                        <button type="submit" @disabled($character->isImprisoned() || $character->isComatose() || $character->energy < 25 || $character->cash < 500) class="w-full py-3 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95 text-white font-black text-xs uppercase tracking-wider shadow-xs transition-all duration-150 flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-paper-plane"></i> Bestel Drone Dropping (€500)
                        </button>
                    </form>
                @endif
            </div>
        </div>

    </div>

    <!-- Buit Geschiedenis -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-boxes-stacked text-emerald-500"></i>
                <h3 class="text-xs font-black uppercase tracking-wide text-slate-900 dark:text-white">Jouw Smokkelrapportage</h3>
            </div>
            <div class="flex items-center gap-4 text-xs font-bold">
                <span class="text-slate-500">Succesvolle drops: <strong class="text-slate-900 dark:text-white">{{ $totalIntercepted }}</strong></span>
                <span class="text-slate-500">Opbrengst: <strong class="text-emerald-600">€{{ number_format($totalCashMade, 0, ',', '.') }}</strong></span>
            </div>
        </div>

        @if($recentDroppings->isEmpty())
            <div class="py-8 text-center text-slate-400 text-xs">
                <i class="fa-solid fa-box-open text-3xl mb-2 text-slate-300 dark:text-slate-700 block"></i>
                Je hebt nog geen tennisballen of drones binnengehaald. Tijd om het luchtruim af te speuren!
            </div>
        @else
            <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                @foreach($recentDroppings as $drop)
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg flex items-center justify-center font-bold {{ $drop->type === 'tennisball' ? 'bg-lime-50 text-lime-700 dark:bg-lime-950/60 dark:text-lime-300 border border-lime-200/80 dark:border-lime-800/70' : 'bg-teal-50 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300 border border-teal-200/80 dark:border-teal-800/70' }}">
                                <i class="fa-solid {{ $drop->type === 'tennisball' ? 'fa-baseball' : 'fa-helicopter' }}"></i>
                            </span>
                            <div>
                                <span class="font-extrabold text-slate-900 dark:text-white capitalize">{{ $drop->type === 'tennisball' ? 'Tennisbal over hek' : 'Drone dropping' }}</span>
                                <p class="text-[11px] {{ $drop->caught ? 'text-rose-500 font-bold' : 'text-slate-500 dark:text-slate-400' }}">{{ $drop->loot_summary }}</p>
                            </div>
                        </div>
                        <span class="text-slate-400 text-[10px]">{{ $drop->created_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
