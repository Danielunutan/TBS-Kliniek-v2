@extends('layouts.app')

@section('content')
<div class="space-y-6">

    @if($character->isImprisoned())
    <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-2xl p-4 text-xs font-bold text-rose-700 dark:text-rose-300 flex items-center gap-3">
        <i class="fa-solid fa-lock text-rose-500 text-base shrink-0"></i>
        <span>Je zit momenteel in de isoleercel. Je hebt geen toegang tot je eigen cel en de uitbraaktunnel.</span>
    </div>
    @elseif($character->isComatose() || $character->needsReviveSerum())
    <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-2xl p-4 text-xs font-bold text-rose-700 dark:text-rose-300 flex items-center gap-3">
        <i class="fa-solid fa-heart-pulse text-rose-500 text-base shrink-0"></i>
        <span>Je ligt momenteel op de Intensive Care en kunt niet graven aan de tunnel.</span>
    </div>
    @endif

    <!-- Verwijzing naar Gevangenis (Isoleercel) & Uitbreken -->
    <div class="bg-rose-50/90 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/70 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
        <div class="flex items-start sm:items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-rose-100 dark:bg-rose-900/60 text-rose-600 dark:text-rose-300 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-dungeon"></i>
            </div>
            <div class="text-xs text-slate-700 dark:text-slate-200 leading-relaxed">
                <strong class="font-black text-rose-700 dark:text-rose-300">Zoek je waar je gevangenen of jezelf kunt uitbreken uit de gevangenis?</strong>
                <span class="block sm:inline sm:ml-1">Deze pagina is je 5-etappe graaftunnel. Voor <strong>celbevrijdingen (24u Battle)</strong> of <strong>zelf uitbreken uit de isoleercel</strong> ga je naar de Gevangenis.</span>
            </div>
        </div>
        <a href="{{ route('prison') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-black text-xs uppercase tracking-wider shrink-0 transition shadow-xs">
            <i class="fa-solid fa-unlock-keyhole"></i>
            <span>Naar Gevangenis &amp; Uitbreken &rarr;</span>
        </a>
    </div>

    <!-- Top Header Banner (TBS Kliniek Website Styling) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 sm:p-7 border border-slate-200/90 dark:border-slate-800 shadow-xs relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 dark:bg-slate-800 border border-amber-200 dark:border-slate-700 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl shrink-0 shadow-2xs mt-0.5">
                    <i class="fa-solid fa-person-digging"></i>
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-700/60">
                            <i class="fa-solid fa-spoon"></i> Het Grote Uitbraakcomplot
                        </span>
                        <span class="text-[10px] font-bold text-amber-700 dark:text-amber-300 bg-amber-100/70 dark:bg-amber-900/40 px-2 py-0.5 rounded-full border border-amber-200/80 dark:border-amber-800/70">
                            Etappe {{ $tunnel->stage }}/5 &bull; {{ $tunnel->progress_pct }}% Voltooid
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        De Geheime Uitbraaktunnel
                    </h1>
                    <p class="text-xs text-slate-600 dark:text-slate-300 max-w-2xl leading-relaxed font-medium">
                        Verborgen achter een vergeelde poster in je cel graaf je lepel voor lepel een ondergrondse vluchtweg. Strooi het zand ongezien uit tijdens het luchtuurtje en bereik de buitenmuur voor een megabeloning van €25.000 en 25 Sterren ⭐!
                    </p>
                </div>
            </div>

            <!-- Stats Summary Badges -->
            <div class="flex items-center gap-3 shrink-0 self-start md:self-center">
                <div class="p-3 sm:p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-center min-w-[100px]">
                    <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block mb-0.5">Lepels</span>
                    <span class="text-lg font-black text-slate-900 dark:text-white leading-none font-mono">
                        {{ $tunnel->spoons_used }}x
                    </span>
                </div>
                <div class="p-3 sm:p-3.5 rounded-xl bg-amber-50/80 dark:bg-slate-800/60 border border-amber-200/80 dark:border-slate-700/70 text-center min-w-[120px]">
                    <span class="text-[9px] font-black uppercase tracking-wider text-amber-700 dark:text-amber-400 block mb-0.5">Voortgang</span>
                    <span class="text-lg font-black text-amber-600 dark:text-amber-400 leading-none font-mono">
                        {{ $tunnel->progress_pct }}%
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback Alerts -->
    

    <!-- Voortgangsbalk & Huidige Etappe Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400">Huidige Etappe ({{ $tunnel->stage }}/5)</span>
                <h2 class="text-base font-black text-slate-900 dark:text-white">{{ $currentStageInfo['name'] }}</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $currentStageInfo['desc'] }}</p>
            </div>
            <div class="text-left sm:text-right">
                <span class="text-2xl font-black text-amber-600 dark:text-amber-400 font-mono">{{ $tunnel->progress_pct }}%</span>
                <span class="text-[11px] text-slate-400 block font-semibold">totale tunnel voltooid</span>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="space-y-2">
            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-3.5 overflow-hidden p-0.5 border border-slate-200 dark:border-slate-700">
                <div class="bg-gradient-to-r from-amber-500 to-emerald-500 h-full rounded-full transition-all duration-500" style="width: {{ $tunnel->progress_pct }}%"></div>
            </div>

            <!-- Steps -->
            <div class="grid grid-cols-5 gap-1 pt-1 text-center">
                @foreach($stages as $num => $st)
                    <div class="space-y-1">
                        <div class="w-6 h-6 rounded-full mx-auto flex items-center justify-center text-[10px] font-black {{ $tunnel->stage > $num || $tunnel->progress_pct >= $st['target_progress'] ? 'bg-emerald-500 text-white' : ($tunnel->stage == $num ? 'bg-amber-500 text-white ring-2 ring-amber-300 dark:ring-amber-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-200 border border-slate-200 dark:border-slate-700') }}">
                            @if($tunnel->stage > $num || $tunnel->progress_pct >= $st['target_progress'])
                                <i class="fa-solid fa-check text-[9px]"></i>
                            @else
                                {{ $num }}
                            @endif
                        </div>
                        <span class="text-[10px] font-bold text-slate-600 dark:text-slate-400 hidden sm:block truncate">{{ $st['name'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Acties: Graven & Zand Dumpen -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Actie 1: Graven met Eetlepel -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs flex flex-col justify-between space-y-5">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800 flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid fa-spoon"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                        20 Energie per beurt
                    </span>
                </div>

                <div>
                    <h3 class="text-base font-black text-slate-900 dark:text-white">Graven met Eetlepel</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                        Kruip onder je bed en schraap zand en puin los. Elke graafsessie levert 2% tot 5% voortgang op, maar vult ook je zakken en sokken met zand.
                    </p>
                </div>

                <div class="bg-slate-50/90 dark:bg-slate-800/60 rounded-xl p-3 text-xs space-y-1.5 font-medium text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700/70">
                    <div class="flex justify-between">
                        <span><i class="fa-solid fa-utensils text-amber-500 mr-1.5"></i> Versleten lepels:</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $tunnel->spoons_used }} stuks</span>
                    </div>
                    <div class="flex justify-between">
                        <span><i class="fa-solid fa-bolt text-indigo-500 mr-1.5"></i> Energie vereist:</span>
                        <span class="font-bold text-slate-900 dark:text-white">20 Energie</span>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                @if($character->isComatose())
                    <button disabled class="w-full py-3 px-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70 font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-not-allowed">
                        <i class="fa-solid fa-heart-pulse"></i> Coma (Code Blauw)
                    </button>
                @elseif($character->isImprisoned())
                    <button disabled class="w-full py-3 px-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70 font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-not-allowed">
                        <i class="fa-solid fa-lock"></i> In Isoleercel
                    </button>
                @elseif($tunnel->is_completed)
                    <button disabled class="w-full py-3 px-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-not-allowed">
                        <i class="fa-solid fa-trophy"></i> Tunnel Voltooid & Ontsnapt!
                    </button>
                @elseif($tunnel->progress_pct >= 100)
                    <form action="{{ route('tunnel.escape') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 active:scale-95 text-white font-black text-xs uppercase tracking-wider shadow-xs transition-all duration-150 flex items-center justify-center gap-2 cursor-pointer animate-pulse">
                            <i class="fa-solid fa-person-running"></i> Breek Uit Naar Vrijheid (€25.000 + 25 Sterren)
                        </button>
                    </form>
                @elseif($tunnel->sand_in_socks >= 100)
                    <button disabled class="w-full py-3 px-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70 font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-not-allowed">
                        <i class="fa-solid fa-triangle-exclamation"></i> Zakken Vol Zand (Eerst Dumpen)
                    </button>
                @elseif($digCooldown > 0)
                    <button disabled class="w-full py-3 px-4 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-not-allowed">
                        <i class="fa-solid fa-clock"></i> Uitrusten (Nog {{ ceil($digCooldown / 60) }} min)
                    </button>
                @else
                    <form action="{{ route('tunnel.dig') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white font-black text-xs uppercase tracking-wider shadow-xs transition-all duration-150 flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-person-digging"></i> Schraap Zand Af met Lepel
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Actie 2: Zand Afvoeren op Luchtplaats -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs flex flex-col justify-between space-y-5">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid fa-mound"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full {{ $tunnel->sand_in_socks >= 80 ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700' }}">
                        Zandcapaciteit: {{ $tunnel->sand_in_socks }}% / 100%
                    </span>
                </div>

                <div>
                    <h3 class="text-base font-black text-slate-900 dark:text-white">Zand Afvoeren op Luchtplaats</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                        Verstop het uitgegraven zand in je sokken en trek tijdens het luchtuurtje aan een geheim touwtje in je broekspijp om het zand onopgemerkt tussen het grind te laten glijden.
                    </p>
                </div>

                <div class="space-y-1.5">
                    <div class="flex justify-between text-xs font-bold text-slate-600 dark:text-slate-400">
                        <span>Zand in je zakken:</span>
                        <span class="{{ $tunnel->sand_in_socks >= 100 ? 'text-rose-600 dark:text-rose-400 font-black animate-pulse' : 'text-slate-900 dark:text-white' }}">{{ $tunnel->sand_in_socks }}% vol</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-3 overflow-hidden p-0.5 border border-slate-200 dark:border-slate-700">
                        <div class="h-full rounded-full transition-all duration-300 {{ $tunnel->sand_in_socks >= 100 ? 'bg-rose-500' : 'bg-purple-600' }}" style="width: {{ $tunnel->sand_in_socks }}%"></div>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                @if($character->isComatose())
                    <button disabled class="w-full py-3 px-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70 font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-not-allowed">
                        <i class="fa-solid fa-heart-pulse"></i> Coma (Code Blauw)
                    </button>
                @elseif($character->isImprisoned())
                    <button disabled class="w-full py-3 px-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70 font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-not-allowed">
                        <i class="fa-solid fa-lock"></i> In Isoleercel
                    </button>
                @elseif($dumpCooldown > 0)
                    <button disabled class="w-full py-3 px-4 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-not-allowed">
                        <i class="fa-solid fa-clock"></i> Bewaker Kijkt (Nog {{ ceil($dumpCooldown / 60) }} min)
                    </button>
                @elseif($tunnel->sand_in_socks <= 0)
                    <button disabled class="w-full py-3 px-4 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-not-allowed">
                        <i class="fa-solid fa-check"></i> Zakken zijn Schoon (0% Zand)
                    </button>
                @else
                    <form action="{{ route('tunnel.dump') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white font-black text-xs uppercase tracking-wider shadow-xs transition-all duration-150 flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-shoe-prints"></i> Strooi Zand Uit op Luchtplaats (Leeg {{ $tunnel->sand_in_socks }}%)
                        </button>
                    </form>
                @endif
            </div>
        </div>

    </div>

</div>
@endsection
