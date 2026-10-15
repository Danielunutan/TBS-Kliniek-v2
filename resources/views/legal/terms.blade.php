@extends('layouts.app')

@section('title', 'Algemene Voorwaarden - TBS Kliniek')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs relative overflow-hidden">
        <div class="inline-flex items-center gap-2 bg-purple-50 text-purple-700 text-xs font-bold px-3 py-1 rounded-full border border-purple-200 mb-2">
            <i class="fa-solid fa-scale-balanced text-purple-600"></i> Juridische Voorwaarden
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Algemene Voorwaarden</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Laatst gewijzigd: {{ date('d F Y') }} • Van toepassing op alle gebruikers van het platform tbs-kliniek.nl.
        </p>
    </div>

    <!-- Main Content Body -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs space-y-8 text-sm text-slate-700 leading-relaxed">

        <!-- Artikel 1 -->
        <section class="space-y-2">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold">1</span>
                Algemene Bepalingen & Definities
            </h2>
            <p>
                1.1. Deze algemene voorwaarden zijn van toepassing op elk bezoek aan en gebruik van de website <strong>tbs-kliniek.nl</strong> (hierna: "het Spel" of "Platform").
            </p>
            <p>
                1.2. Door een account aan te maken of de website te bezoeken, verklaart de gebruiker akkoord te gaan met deze voorwaarden en het bijbehorende Privacy- en Cookiebeleid.
            </p>
            <p>
                1.3. <strong>TBS Kliniek is een fictief rollenspel (satire / browser RPG)</strong>. De context, namen, verhaallijnen en elementen in het spel zijn puur ter vermaak bedoeld en hebben geen enkele relatie met bestaande forensische psychiatrische centra, het Ministerie van Justitie en Veiligheid, de Dienst Justitiële Inrichtingen (DJI) of reële misdaad.
            </p>
        </section>

        <!-- Artikel 2 -->
        <section class="space-y-2">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold">2</span>
                Toegang & Leeftijd
            </h2>
            <p>
                2.1. Gebruikers dienen minimaal <strong>16 jaar</strong> oud te zijn om een account aan te maken, conform de Algemene Verordening Gegevensbescherming (AVG). Gebruikers jonger dan 16 jaar mogen uitsluitend spelen met uitdrukkelijke toestemming van een ouder of wettelijke voogd.
            </p>
            <p>
                2.2. Elke speler is gerechtigd tot het aanmaken van <strong>maximaal één (1) account</strong>. Het beheren van meerdere accounts (multi-accounting) om oneerlijk voordeel te behalen is strikt verboden en leidt tot onmiddellijke schorsing.
            </p>
        </section>

        <!-- Artikel 3 -->
        <section class="space-y-2">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold">3</span>
                Gedragsregels & Fair Play
            </h2>
            <p>
                3.1. Het gebruik van bots, macro’s, auto-clickers, geautomatiseerde scripts of exploits om acties in het spel uit te voeren zonder menselijke interactie is ten strengste verboden. Het spel hanteert geautomatiseerde beveiligingscontroles (o.a. Cloudflare Turnstile en server-side ratelimits).
            </p>
            <p>
                3.2. Het is niet toegestaan om andere spelers te intimideren, discrimineren, bedreigen of haatdragende uitingen te plaatsen in profielen, chatberichten of bendenamen.
            </p>
            <p>
                3.3. Het opzettelijk misbruiken van bugs zonder deze te melden via het Support Ticket systeem wordt beschouwd als fraude.
            </p>
        </section>

        <!-- Artikel 4 -->
        <section class="space-y-2">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold">4</span>
                Virtuele Valuta & Geen Echte Waarde
            </h2>
            <p>
                4.1. Alle in-game valuta, waaronder contant geld, bankgeld, Sterren ⭐, Kluismunten 🪙, maskers en koffers, zijn zuiver fictieve digitale speelelementen.
            </p>
            <p>
                4.2. <strong>Virtuele valuta en items hebben GEEN economische of monetaire waarde</strong> in de echte wereld. Het is niet mogelijk om virtuele valuta in te wisselen voor echt geld, noch om in-game goederen te verhandelen tegen reële valuta buiten het spel (Real Money Trading / RMT is verboden).
            </p>
            <p>
                4.3. Donaties of vrijwillige VIP-lidmaatschappen (bijvoorbeeld via Discord) worden aangemerkt als ondersteuningsbijdragen voor server- en ontwikkelkosten en geven geen recht op terugbetaling zodra digitale voordelen zijn geleverd.
            </p>
        </section>

        <!-- Artikel 5 -->
        <section class="space-y-2">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold">5</span>
                Aansprakelijkheid & Beschikbaarheid
            </h2>
            <p>
                5.1. Het spel wordt aangeboden op een "as is" en "as available" basis. De beheerders garanderen geen ononderbroken of foutloze werking van het platform.
            </p>
            <p>
                5.2. De beheerders zijn in geen geval aansprakelijk voor enige directe of indirecte schade, dataverlies of tijdelijke onbereikbaarheid van de servers.
            </p>
            <p>
                5.3. De beheerders behouden zich te allen tijde het recht voor om het spel te wijzigen, balanceren, tijdelijk te onderhouden of accounts te beëindigen bij schending van deze voorwaarden.
            </p>
        </section>

        <!-- Artikel 6 -->
        <section class="space-y-2">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold">6</span>
                Toepasselijk Recht & Geschillen
            </h2>
            <p>
                6.1. Op deze algemene voorwaarden is uitsluitend het <strong>Nederlands recht</strong> van toepassing.
            </p>
            <p>
                6.2. Eventuele geschillen die voortvloeien uit of verband houden met het spel zullen worden voorgelegd aan de bevoegde rechter in Nederland.
            </p>
        </section>

    </div>

</div>
@endsection
