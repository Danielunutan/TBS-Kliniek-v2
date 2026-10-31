@extends('layouts.app')

@section('title', 'Disclaimer & Colofon - TBS Kliniek')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xs relative overflow-hidden">
        <div class="inline-flex items-center gap-2 bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 text-xs font-bold px-3 py-1 rounded-full border border-purple-200/80 dark:border-purple-800/70 mb-2">
            <i class="fa-solid fa-circle-exclamation text-purple-600 dark:text-purple-400"></i> Fictief Rollenspel Verklaring
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Disclaimer & Colofon</h1>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
            Belangrijke informatie over het fictieve en satirische karakter van dit browsergame.
        </p>
    </div>

    <!-- Prominent Non-Profit & Hobby Disclaimer Box -->
    <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-purple-50 via-slate-50 to-indigo-50 dark:from-purple-950/40 dark:via-slate-900/60 dark:to-indigo-950/40 border border-purple-200/90 dark:border-purple-800/60 shadow-xs flex items-start gap-3.5">
        <div class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-900/70 text-purple-700 dark:text-purple-300 flex items-center justify-center shrink-0 text-base font-black shadow-2xs mt-0.5">
            <i class="fa-solid fa-scale-balanced"></i>
        </div>
        <div class="space-y-1.5 text-xs text-slate-700 dark:text-slate-300 leading-relaxed">
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-black uppercase tracking-wider text-purple-900 dark:text-purple-200 text-xs">
                    Particulier Hobbyproject &bull; Geen KvK &bull; Vrijwillige Donaties
                </span>
                <span class="px-2 py-0.5 rounded-full bg-purple-200/80 dark:bg-purple-900/80 text-purple-800 dark:text-purple-200 text-[10px] font-mono font-bold">
                    werkenmetdaniel.nl
                </span>
            </div>
            <p>
                <strong>TBS Kliniek is een 100% hobbymatig en educatief browsergame</strong>. Wij zijn g&eacute;&eacute;n commercieel bedrijf en hebben <strong>geen inschrijving bij de Kamer van Koophandel (geen KvK-nummer)</strong>.
            </p>
            <p>
                Alle financi&euml;le bijdragen (waaronder VIP-lidmaatschappen, Gouden Sterren, Koffers en Bakkie Koffie via Buy Me a Coffee) vallen volledig onder <strong>vrijwillige supportersdonaties</strong> ter dekking van directe server-, licentie- en hostingkosten. Het betreft g&eacute;&eacute;n commerci&euml;le aankoop van goederen of diensten.
            </p>
        </div>
    </div>

    <!-- Main Content Body -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xs space-y-8 text-sm text-slate-700 dark:text-slate-300 leading-relaxed">

        <!-- Disclaimer Fictief -->
        <section class="space-y-3">
            <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/70 text-amber-950 dark:text-amber-200 flex items-start gap-3">
                <i class="fa-solid fa-triangle-exclamation text-amber-600 dark:text-amber-400 text-lg mt-0.5 shrink-0"></i>
                <div class="space-y-1 text-xs">
                    <strong class="font-black uppercase tracking-wider text-amber-900 dark:text-amber-300">Belangrijke Mededeling</strong>
                    <p class="text-amber-800 dark:text-amber-200/90 leading-relaxed">
                        <strong>TBS Kliniek is een 100% fictief online browsergame (RPG / satire)</strong>. Het betreft een particulier hobbymatig initiatief zonder commercieel oogmerk en <strong>zonder inschrijving bij de Kamer van Koophandel (geen KvK-nummer)</strong>. Alle optionele bijdragen (zoals VIP-lidmaatschappen, Gouden Sterren en Bakkie Koffie via Buy Me a Coffee) zijn 100% vrijwillige donaties ter dekking van server- en hostingkosten. Alle situaties, karakters, misdrijven, instellingen, namen van behandelaars en gebeurtenissen in dit spel zijn verzonnen en uitsluitend bedoeld voor recreatieve en amusementsdoeleinden.
                    </p>
                </div>
            </div>

            <p>
                Dit platform is op geen enkele wijze gelieerd aan, gesponsord door, of verbonden met
            </p>
            <ul class="list-disc pl-5 space-y-1 text-slate-600 dark:text-slate-400">
                <li>Het Nederlandse Ministerie van Justitie en Veiligheid</li>
                <li>De Dienst Justitiële Inrichtingen (DJI)</li>
                <li>Bestaande Forensische Psychiatrische Centra (FPC's) of klinieken in Nederland of het buitenland</li>
                <li>Echte reclasserings- of politieorganisaties</li>
            </ul>
            <p>
                Het spel stimuleert noch verheerlijkt reëel crimineel gedrag. Virtuele acties zoals 'zakkenrollen', 'hosselen' of 'vechten' zijn zuiver digitale spelmechanieken.
            </p>
        </section>

        <!-- Colofon -->
        <section class="space-y-2">
            <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 flex items-center justify-center text-xs font-bold">2</span>
                Colofon & Contact
            </h2>
            <div class="p-4 bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl space-y-2 text-xs text-slate-700 dark:text-slate-300">
                <p><strong>Website</strong> tbs-kliniek.nl</p>
                <p><strong>Status</strong> Particulier hobbymatig &amp; non-profit project (géén onderneming, geen commerciële verkoop)</p>
                <p><strong>Kamer van Koophandel (KvK)</strong> Geen. TBS Kliniek is een particulier hobbyproject zonder winstoogmerk en geen onderneming in de zin van de Handelsregisterwet. Wij hebben dan ook géén KvK-nummer.</p>
                <p><strong>Donaties &amp; Financiële Steun</strong> Alle verkrijgbare opties via Buy Me a Coffee (waaronder VIP-lidmaatschappen, Gouden Sterren, XXL Koffers en Bakkie Koffie) vallen 100% onder een vrijwillige supportersdonatie ter dekking van reële server- en hostingkosten. Dit is géén commerciële koop of aankoop van goederen of diensten.</p>
                <p><strong>Beheer / Portfolio</strong> <a href="https://werkenmetdaniel.nl" target="_blank" rel="noopener" class="text-purple-600 dark:text-purple-400 font-bold underline">werkenmetdaniel.nl</a></p>
                <p><strong>E-mail voor algemene zaken</strong> <em>contact@tbs-kliniek.nl</em></p>
                <p><strong>E-mail voor privacy & AVG</strong> <em>privacy@tbs-kliniek.nl</em></p>
                <p><strong>Spelondersteuning</strong> Maak een ticket aan via <a href="{{ route('help.tickets.index') }}" class="text-purple-600 dark:text-purple-400 font-bold underline">Help & Support Tickets</a> of praat met de Zaalwacht AI.</p>
                <p><strong>Discord Community</strong> <a href="https://discord.gg/tbs" target="_blank" rel="noopener" class="text-purple-600 dark:text-purple-400 font-bold underline">discord.gg/tbs</a></p>
                <p><strong>YouTube</strong> <a href="https://www.youtube.com/@tbsklinieknl" target="_blank" rel="noopener" class="text-red-600 font-bold underline">TBS-Kliniek op YouTube (@tbsklinieknl)</a></p>
                <p><strong>TikTok</strong> <a href="https://www.tiktok.com/@tbsklinieknl" target="_blank" rel="noopener" class="text-purple-600 dark:text-purple-400 font-bold underline">@tbsklinieknl</a></p>
                <p><strong>Instagram</strong> <a href="https://www.instagram.com/tbsklinieknl/" target="_blank" rel="noopener" class="text-purple-600 dark:text-purple-400 font-bold underline">@tbsklinieknl</a></p>
                <p><strong>Twitter / X</strong> <a href="https://x.com/tbskliniek" target="_blank" rel="noopener" class="text-purple-600 dark:text-purple-400 font-bold underline">@tbskliniek</a></p>
                <p><strong>Hyves</strong> <a href="https://hyves.nl/#my-profile/hyve?id=Zv3DWejo" target="_blank" rel="noopener" class="text-orange-600 font-bold underline inline-flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded bg-[#f36f21] text-white inline-flex items-center justify-center font-black text-[9px] leading-none shrink-0 shadow-2xs">H</span> TBS Kliniek op Hyves</a></p>
            </div>
        </section>

        <!-- Auteursrecht -->
        <section class="space-y-2">
            <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 flex items-center justify-center text-xs font-bold">3</span>
                Auteursrecht & Intellectueel Eigendom
            </h2>
            <p>
                Alle rechten van intellectueel eigendom met betrekking tot de spelcode, vormgeving, teksten en illustraties van TBS Kliniek berusten bij de ontwikkelaars en makers van tbs-kliniek.nl. Kopiëren, verspreiden of anderszins verveelvoudigen van spelcontent zonder schriftelijke toestemming is niet toegestaan.
            </p>
        </section>

    </div>

</div>
@endsection
