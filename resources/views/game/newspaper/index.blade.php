@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Newspaper Masthead (Authentic Classic Clinic Gazette Header) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border-2 border-slate-900 dark:border-slate-700 p-6 sm:p-8 shadow-xs text-center space-y-3">
        <div class="flex items-center justify-between border-b-2 border-slate-900 dark:border-slate-700 pb-2 text-[10px] sm:text-xs font-mono uppercase tracking-widest text-slate-500 dark:text-slate-400 font-bold">
            <span>Jaargang {{ now()->year - 2023 }} &bull; Editie #{{ now()->dayOfYear }}</span>
            <span class="text-slate-900 dark:text-white font-black">{{ now()->translatedFormat('l d F Y') }}</span>
            <span>Prijs: 1 Shagje</span>
        </div>

        <div class="py-2">
            <h1 class="text-3xl sm:text-5xl font-black font-serif tracking-tight text-slate-900 dark:text-white uppercase">
                DE KLINIEKBODE
            </h1>
            <p class="text-xs font-serif italic text-slate-500 dark:text-slate-400 mt-1">
                "Het enige onafhankelijke dagblad achter de tralies &bull; Gedrukt op gerecycled toiletpapier"
            </p>
        </div>

        <!-- Mini Ticker Strip -->
        <div class="border-t border-slate-200 dark:border-slate-800 pt-2 flex flex-wrap items-center justify-between gap-3 text-[11px] font-bold text-slate-600 dark:text-slate-300">
            <span class="inline-flex items-center gap-1.5 text-amber-600 dark:text-amber-400">
                <i class="fa-solid fa-crown text-amber-500"></i> Loterij Jackpot: <strong>€{{ number_format($lotteryRound->jackpot, 0, ',', '.') }}</strong>
            </span>
            <span class="inline-flex items-center gap-1.5">
                <i class="fa-solid fa-fire text-rose-500"></i> Luchtplaats status: <strong>Verhoogde waakzaamheid</strong>
            </span>
            <span class="inline-flex items-center gap-1.5">
                <i class="fa-solid fa-cookie text-purple-500"></i> Kantine daghap: <strong>Warm Frikandelbroodje</strong>
            </span>
        </div>
    </div>

    <!-- MAIN NEWSPAPER CONTENT GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Column 1 & 2: Front Page Lead Stories (8 Cols) -->
        <div class="lg:col-span-8 space-y-6">

            <!-- LEAD STORY: DE GROTE KRAAK VAN DE DAG -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                <div class="inline-block px-2.5 py-0.5 rounded bg-rose-600 text-white text-[10px] font-black uppercase tracking-wider">
                    Voorpagina Nieuws &bull; Misdaad
                </div>

                @if($topThefts->isNotEmpty())
                    @php $lead = $topThefts->first(); @endphp
                    <div class="space-y-3">
                        <h2 class="text-2xl sm:text-3xl font-black font-serif tracking-tight text-slate-900 dark:text-white leading-tight">
                            BRUTALE ROOF OP DE GANGEN: {{ $lead->thief ? $lead->thief->display_name : 'Een onbekende dader' }} plundert €{{ number_format($lead->cash_stolen, 0, ',', '.') }} contant!
                        </h2>
                        
                        <div class="flex items-center gap-3 text-xs text-slate-400 font-mono border-y border-slate-100 dark:border-slate-800 py-1.5">
                            <span>Verslaggever: Afdelingsspion</span>
                            <span>&bull;</span>
                            <span>{{ $lead->created_at?->diffForHumans() }}</span>
                        </div>

                        <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 leading-relaxed font-serif">
                            In de vroege ochtenduren sloeg de dader geruisloos toe in de corridor. Terwijl de bewaking een bak koffie inschonk, werd slachtoffer <strong>{{ $lead->victim ? $lead->victim->display_name : 'een mede-patiënt' }}</strong> vakkundig van zijn contanten ontdaan. Zaalwachten hebben een onderzoek ingesteld, maar de buit is vermoedelijk al witgewassen via de afdelingsloterij of de kantine.
                        </p>
                    </div>

                    <!-- Other recent heists -->
                    @if($topThefts->count() > 1)
                    <div class="border-t border-slate-100 dark:border-slate-800 pt-4 space-y-2">
                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-400">Verder in het politierapport:</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            @foreach($topThefts->skip(1) as $sub)
                            <div class="p-3 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-xs space-y-1">
                                <span class="text-[10px] font-black text-rose-600 dark:text-rose-400 block">€{{ number_format($sub->cash_stolen, 0, ',', '.') }} buitgemaakt</span>
                                <p class="text-[11px] text-slate-700 dark:text-slate-300 font-bold truncate">Door: {{ $sub->thief ? $sub->thief->display_name : 'Dief' }}</p>
                                <span class="text-[10px] text-slate-400 block">{{ $sub->created_at?->diffForHumans() }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @else
                    <div class="py-6 text-center text-slate-400 space-y-2">
                        <i class="fa-solid fa-feather text-3xl opacity-40"></i>
                        <h3 class="text-sm font-black text-slate-800 dark:text-white">Opvallende rust in de wandelgangen</h3>
                        <p class="text-xs max-w-md mx-auto">Er zijn de afgelopen uren geen grote diefstallen geregistreerd. De zakkenrollers houden zich voorlopig koest.</p>
                    </div>
                @endif
            </div>

            <!-- ISOLEERCEL KRONIEKEN: WIE ZIT ER IN DE BOEIEN? -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-lock text-rose-600 text-lg"></i>
                        <h3 class="text-sm font-extrabold uppercase tracking-wide text-slate-900 dark:text-slate-100">
                            Isoleercel Kronieken &bull; Huidige Bewoners
                        </h3>
                    </div>
                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70">
                        {{ $imprisonedInmates->count() }} achter de dikke deur
                    </span>
                </div>

                @if($imprisonedInmates->isNotEmpty())
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($imprisonedInmates as $inmate)
                    <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                        <div class="flex items-center gap-3">
                            <img src="{{ $inmate->avatar_url }}" alt="{{ $inmate->name }}" class="w-10 h-10 rounded-xl object-cover border border-slate-200">
                            <div>
                                <h4 class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-2">
                                    <span>{{ $inmate->display_name }}</span>
                                    <span class="text-[10px] font-bold text-slate-400">Level {{ $inmate->level }}</span>
                                </h4>
                                <p class="text-[11px] text-rose-600 dark:text-rose-400 font-medium">
                                    Aanklacht: {{ $inmate->prison_reason ?? 'Wangedrag en opruiing tijdens de dagbesteding' }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-[10px] font-mono font-bold text-slate-400 block">Nog vast</span>
                            <span class="text-xs font-mono font-black text-rose-600">
                                {{ now()->diffForHumans($inmate->prison_until, true) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="py-4 text-center text-xs text-slate-400">
                    <p>De isoleercellen zijn momenteel leeg. Een historisch moment van gehoorzaamheid!</p>
                </div>
                @endif
            </div>

            <!-- INTERVIEW MET PATIËNT VAN DE DAG -->
            @if($spotlightPatient)
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                <div class="inline-block px-2.5 py-0.5 rounded bg-purple-600 text-white text-[10px] font-black uppercase tracking-wider">
                    Interview van de Dag &bull; Profiel in de Kijker
                </div>

                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">
                    <img src="{{ $spotlightPatient->avatar_url }}" alt="{{ $spotlightPatient->name }}" class="w-20 h-20 rounded-2xl object-cover border-2 border-purple-300 dark:border-purple-700 shrink-0 shadow-2xs">
                    <div class="space-y-2 text-center sm:text-left">
                        <div class="flex items-center justify-center sm:justify-start gap-2 flex-wrap">
                            <h3 class="text-base font-black text-slate-900 dark:text-white">{{ $spotlightPatient->display_name }}</h3>
                            <span class="text-xs font-bold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 px-2 py-0.5 rounded-md border border-purple-200/80 dark:border-purple-800/70">{{ $spotlightPatient->title ?? 'Modelpatiënt' }}</span>
                        </div>
                        <p class="text-xs font-serif italic text-slate-600 dark:text-slate-300 leading-relaxed bg-slate-50/90 dark:bg-slate-800/60 p-3 rounded-xl border border-slate-200/80 dark:border-slate-700/70">
                            "{{ $spotlightPatient->status_message ?: ($spotlightPatient->bio ?: 'Geen commentaar. Mijn advocaat heeft me geadviseerd om niks te zeggen tegen de pers.') }}"
                        </p>
                        <div class="flex items-center justify-center sm:justify-start gap-3 text-[11px] text-slate-400 font-bold">
                            <span>Level {{ $spotlightPatient->level }}</span>
                            <span>&bull;</span>
                            <span>{{ number_format($spotlightPatient->respect) }} Respect</span>
                            <span>&bull;</span>
                            <a href="{{ route('profile.show', $spotlightPatient->name) }}" class="text-purple-600 dark:text-purple-400 hover:underline">Bekijk Dossier &raquo;</a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>

        <!-- Column 3: Sidebar Columns (4 Cols) -->
        <div class="lg:col-span-4 space-y-6">

            <!-- LUCHTPLAATS WEERBERICHT -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-cloud-sun text-amber-500"></i>
                        <h3 class="text-xs font-extrabold uppercase tracking-wide text-slate-900 dark:text-slate-100">Kliniek Weerbericht</h3>
                    </div>
                    <span class="text-[10px] font-mono text-slate-400">Luchtplaats</span>
                </div>

                <div class="flex items-center gap-4 py-2">
                    <div class="w-14 h-14 rounded-2xl bg-amber-50 dark:bg-slate-800/80 border border-amber-200 dark:border-slate-700 flex items-center justify-center text-2xl text-amber-500 shrink-0">
                        <i class="fa-solid {{ $todayWeather['icon'] }}"></i>
                    </div>
                    <div>
                        <span class="text-2xl font-black text-slate-900 dark:text-white leading-none font-mono">{{ $todayWeather['temp'] }}</span>
                        <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 mt-1">{{ $todayWeather['sky'] }}</h4>
                    </div>
                </div>

                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed font-serif bg-slate-50/90 dark:bg-slate-800/60 p-3 rounded-xl border border-slate-200/80 dark:border-slate-700/70">
                    {{ $todayWeather['comment'] }}
                </p>
            </div>

            <!-- DE VREES VAN DE LUCHTPLAATS (TOP VECHTER) -->
            @if($topFighter)
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-hand-fist text-rose-500"></i>
                        <h3 class="text-xs font-extrabold uppercase tracking-wide text-slate-900 dark:text-slate-100">Bokskoning van de Afdeling</h3>
                    </div>
                    <span class="text-[10px] font-bold text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/60 border border-rose-200/80 dark:border-rose-800/70 px-1.5 py-0.5 rounded-md">#1 Vechter</span>
                </div>

                <div class="flex items-center gap-3 py-1">
                    <img src="{{ $topFighter->avatar_url }}" alt="{{ $topFighter->name }}" class="w-12 h-12 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shrink-0">
                    <div class="min-w-0">
                        <h4 class="text-sm font-black text-slate-900 dark:text-white truncate">{{ $topFighter->display_name }}</h4>
                        <span class="text-xs font-extrabold text-rose-600 dark:text-rose-400">{{ $topFighter->fights_won }} gevechten gewonnen</span>
                    </div>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-snug">
                    Houdt de luchtplaats in een ijzeren greep. Niemand durft zijn schaduw te passeren bij de optrekstang.
                </p>
            </div>
            @endif

            <!-- DE MACHTIGSTE BENDE -->
            @if($topGang)
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-users-viewfinder text-purple-600"></i>
                        <h3 class="text-xs font-extrabold uppercase tracking-wide text-slate-900 dark:text-slate-100">Heersende Bende</h3>
                    </div>
                    <span class="text-[10px] font-bold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 px-1.5 py-0.5 rounded-md">Top Bende</span>
                </div>

                <div class="space-y-2">
                    <h4 class="text-base font-black text-slate-900 dark:text-white">{{ $topGang->name }}</h4>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-snug">{{ $topGang->description ?: 'Controleert de smokkelkanalen en de recreatiezaal.' }}</p>
                    <div class="flex items-center justify-between text-xs font-bold pt-1 border-t border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">Leden: {{ $topGang->members_count }}</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-mono">€{{ number_format($topGang->treasury, 0, ',', '.') }} in kas</span>
                    </div>
                </div>
            </div>
            @endif

            <!-- ADVERTENTIE VAN DE DIRECTIE -->
            <div class="p-5 rounded-2xl bg-amber-50/80 dark:bg-slate-800/80 border border-amber-200/80 dark:border-slate-700/70 space-y-2 text-center">
                <span class="text-[9px] font-black uppercase tracking-widest text-amber-800 dark:text-amber-400 block">Mededeling van de Directie</span>
                <h4 class="text-xs font-black text-slate-900 dark:text-white">Verplichte Dagbesteding</h4>
                <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed font-medium">
                    Patiënten worden vriendelijk verzocht zich tijdig te melden in de werkplaats en gaarkeuken. Goed gedrag versnelt je behandelplan!
                </p>
                <a href="{{ route('daycare.index') }}" class="inline-block mt-1 px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs shadow-xs transition-all duration-150 active:scale-95 cursor-pointer">
                    Naar Dagbesteding &raquo;
                </a>
            </div>

        </div>

    </div>

</div>
@endsection
