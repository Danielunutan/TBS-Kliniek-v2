@extends('layouts.app')

@section('title', 'Algemene Voorwaarden - TBS Kliniek')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Card -->
    <div class="bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xs relative overflow-hidden">
        <div class="inline-flex items-center gap-2 bg-purple-50 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 text-xs font-bold px-3 py-1 rounded-full border border-purple-200 dark:border-purple-800/80 mb-2">
            <i class="fa-solid fa-scale-balanced text-purple-600 dark:text-purple-400"></i> Gebruikersovereenkomst & Bepalingen
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Algemene Voorwaarden</h1>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
            Laatst bijgewerkt: {{ date('d F Y') }} • Van toepassing op alle bezoekers en geregistreerde spelers van tbs-kliniek.nl.
        </p>
    </div>

    <!-- Prominent Non-Profit & Hobby Disclaimer Box -->
    <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-purple-50 via-slate-50 to-indigo-50 dark:from-purple-950/40 dark:via-slate-900/60 dark:to-indigo-950/40 border border-purple-200/90 dark:border-purple-800/60 shadow-xs flex items-start gap-3.5">
        <div class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-900/70 text-purple-700 dark:text-purple-300 flex items-center justify-center shrink-0 text-base font-black shadow-2xs mt-0.5">
            <i class="fa-solid fa-heart-pulse"></i>
        </div>
        <div class="space-y-1.5 text-xs text-slate-700 dark:text-slate-300 leading-relaxed">
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-black uppercase tracking-wider text-purple-900 dark:text-purple-200 text-xs">
                    Belangrijk: Particulier & Hobbymatig Initiatief (Geen Onderneming)
                </span>
                <span class="px-2 py-0.5 rounded-full bg-purple-200/80 dark:bg-purple-900/80 text-purple-800 dark:text-purple-200 text-[10px] font-mono font-bold">
                    werkenmetdaniel.nl
                </span>
            </div>
            <p>
                <strong>TBS Kliniek (tbs-kliniek.nl) is géén commercieel bedrijf of onderneming</strong>, maar een puur hobbymatig non-profit project, opgezet en beheerd door Daniël via <a href="https://werkenmetdaniel.nl" target="_blank" rel="noopener" class="text-purple-700 dark:text-purple-400 font-bold underline hover:text-purple-900">werkenmetdaniel.nl</a>.
            </p>
            <p>
                <strong>Vrijwillige ondersteuning:</strong> Er zijn geen verplichte betaalde producten of commerciële abonnementen. Vrijwillige supportersdonaties via het onafhankelijke platform Buy Me a Coffee (<a href="https://buymeacoffee.com/tbskliniek" target="_blank" rel="noopener noreferrer" class="text-purple-700 dark:text-purple-400 font-bold underline">buymeacoffee.com/tbskliniek</a>) worden <strong>100% en uitsluitend gebruikt om de directe hosting-, server- en onderhoudskosten te dekken</strong>. Er is uitdrukkelijk <strong>geen winstoogmerk</strong> en er zijn geen commerciële belangen.
            </p>
        </div>
    </div>

    <!-- Main Content Body -->
    <div class="bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xs space-y-8 text-sm text-slate-700 dark:text-slate-300 leading-relaxed">

        <!-- Artikel 1 -->
        <section class="space-y-2.5">
            <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 flex items-center justify-center text-xs font-black">1</span>
                Algemene Bepalingen & Fictief Rollenspel
            </h2>
            <p>
                1.1. Deze algemene voorwaarden zijn van toepassing op elk bezoek aan en ieder gebruik van de website <strong>tbs-kliniek.nl</strong> (hierna: "het Spel" of "het Platform").
            </p>
            <p>
                1.2. Door een account aan te maken, in te loggen of de website te bezoeken, verklaart de gebruiker akkoord te gaan met deze algemene voorwaarden en het bijbehorende Privacy- en Cookiebeleid.
            </p>
            <p>
                1.3. <strong>TBS Kliniek is een 100% fictief satirisch online rollenspel (browser RPG)</strong>. Alle situaties, karakternamen, locaties, behandelaars, dialoogteksten en verhaallijnen zijn ontsproten aan de verbeelding en uitsluitend bedoeld als ontspanning en digitaal vermaak.
            </p>
            <p>
                1.4. Het spel heeft geen enkele band met reële forensisch psychiatrische centra (FPC's), het Nederlandse Ministerie van Justitie en Veiligheid, de Dienst Justitiële Inrichtingen (DJI) of reële strafrechtelijke instanties. Het spel keurt reële misdaad ten strengste af en stimuleert dit op geen enkele wijze.
            </p>
        </section>

        <!-- Artikel 2 -->
        <section class="space-y-2.5">
            <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 flex items-center justify-center text-xs font-black">2</span>
                Juridische Status: Particulier Hobbymatig Project & Geen Onderneming
            </h2>
            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60 space-y-2 text-xs">
                <p>
                    2.1. <strong>Geen onderneming:</strong> TBS Kliniek is geen commerciële onderneming, eenmanszaak, maatschap of vennootschap in de zin van het Handelsregister of het Burgerlijk Wetboek. Het platform is een particulier en educatief hobbyproject beheerd vanuit het persoonlijke portfolio en webinitiatief <a href="https://werkenmetdaniel.nl" target="_blank" rel="noopener" class="text-purple-600 dark:text-purple-400 font-bold underline">werkenmetdaniel.nl</a>.
                </p>
                <p>
                    2.2. <strong>Gratis toegankelijk:</strong> Iedere speler heeft kosteloos en gelijkwaardig toegang tot alle spelonderdelen door actief deel te nemen aan het spel.
                </p>
                <p>
                    2.3. <strong>Vrijwillige supportersdonaties via Buy Me a Coffee:</strong> Spelers kunnen het platform optioneel ondersteunen via Buy Me a Coffee (<a href="https://buymeacoffee.com/tbskliniek" target="_blank" rel="noopener noreferrer" class="text-purple-600 dark:text-purple-400 font-bold underline">buymeacoffee.com/tbskliniek</a>). De eventuele inkomsten hieruit worden <strong>uitsluitend en volledig aangewend ter dekking van de reële hosting-, domein-, licentie- en serveronderhoudskosten</strong> die noodzakelijk zijn om het spel kosteloos en advertentievrij voor de community online te houden.
                </p>
                <p>
                    2.4. <strong>Geen winstoogmerk of financieel belang:</strong> De beheerder streeft uitdrukkelijk geen winst na en heeft geen financieel of commercieel gewin bij de exploitatie van tbs-kliniek.nl. Eventuele overschotten vloeien te allen tijde integraal terug in serverinfrastructuur, beveiliging en optimalisaties van het spel.
                </p>
            </div>
        </section>

        <!-- Artikel 3 -->
        <section class="space-y-2.5">
            <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 flex items-center justify-center text-xs font-black">3</span>
                Toegang, Leeftijd & Accounts
            </h2>
            <p>
                3.1. Gebruikers dienen minimaal <strong>16 jaar</strong> oud te zijn om een account aan te maken, conform de Algemene Verordening Gegevensbescherming (AVG). Spelers jonger dan 16 jaar mogen uitsluitend spelen met toestemming van een ouder of wettelijke voogd.
            </p>
            <p>
                3.2. <strong>Accountlimiet per persoon (Strikt verbod op dubbele accounts / AKA's):</strong> Iedere natuurlijke persoon is gerechtigd tot het aanmaken en beheren van <strong>maximaal één (1) actief account</strong>. Het bezitten, aanmaken of bedienen van meerdere accounts door dezelfde persoon (zogenaamde "multi-accounts", dubbele accounts of aka's) is ten strengste verboden. Overtreding leidt tot een permanente verbanning van alle secundaire accounts en een schorsing (minimaal 31 dagen tot permanent) van het hoofdaccount.
            </p>
            <p>
                3.3. <strong>Huishoudens & Maximaal 2 Accounts per IP-adres:</strong> Om eerlijk spel te waarborgen geldt een technisch en reglementair maximum van <strong>maximaal twee (2) accounts per IP-adres</strong>. Dit is uitsluitend bedoeld om gezinsleden of huisgenoten op hetzelfde netwerk samen te laten spelen. Het is verboden om accounts binnen hetzelfde huishouden te gebruiken om elkaar oneerlijk te bevoordelen (zoals geld of spullen doorsluizen, georganiseerde bende-overnames of schijnaanvallen).
            </p>
            <p>
                3.4. <strong>Uitzondering aanvragen voor grotere huishoudens:</strong> Heeft jouw huishouden meer dan twee gezinsleden die het spel willen spelen? Dan kan er <strong>vooraf een officiële uitzonderingsaanvraag</strong> worden ingediend via ons interne <a href="{{ url('/help/tickets') }}" class="text-purple-600 dark:text-purple-400 font-bold underline">Ticket Support Systeem</a>. Na verificatie door de leiding kan er een IP-ontheffing worden verleend.
            </p>
            <p>
                3.5. <strong>Verbod op VPN's en Anonieme Proxies:</strong> Het gebruik van VPN-verbindingen (Virtual Private Networks), datacenter proxies of geanonimiseerde tunnels om IP-limieten, geolocatie of sancties te omzeilen is niet toegestaan. Registraties via actieve VPN- of proxy-verbindingen worden automatisch door ons beveiligingssysteem geweigerd.
            </p>
            <p>
                3.6. De gebruiker is te allen tijde zelf verantwoordelijk voor de geheimhouding van de inloggegevens. Het delen van accounts met derden of accountoverdracht is niet toegestaan.
            </p>
        </section>

        <!-- Artikel 4 -->
        <section class="space-y-2.5">
            <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 flex items-center justify-center text-xs font-black">4</span>
                Gedragsregels & Fair Play
            </h2>
            <p>
                4.1. <strong>Verbod op automatisering:</strong> Het gebruik van bots, auto-clickers, macro's, geautomatiseerde browserextensies of externe scripts om zonder menselijke handelingen spelacties uit te voeren is ten strengste verboden. Het platform controleert periodiek op geautomatiseerde interacties via rate-limiting en beveiligingsmechanismen.
            </p>
            <p>
                4.2. <strong>Respectvolle communicatie:</strong> Het is strikt verboden om andere spelers te bedreigen, discrimineren, beledigen, stalken of haatdragende teksten te verspreiden in chatberichten, gastenboeken, bende-manifesten of spelersprofielen.
            </p>
            <p>
                4.3. <strong>Melden van fouten:</strong> Het opzettelijk misbruiken van softwarefouten of bugs (exploits) voor virtueel voordeel is verboden. Ontdekte kwetsbaarheden dienen direct gemeld te worden via het interne Ticket Support Systeem.
            </p>
        </section>

        <!-- Artikel 5 -->
        <section class="space-y-2.5">
            <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 flex items-center justify-center text-xs font-black">5</span>
                Virtuele Valuta & Geen Economische Waarde
            </h2>
            <p>
                5.1. Alle in het spel voorkomende valuta (zoals contant geld, banktegoed, Sterren ⭐, Kluismunten 🪙, maskers en goederen) zijn <strong>fictieve digitale speelelementen</strong>.
            </p>
            <p>
                5.2. <strong>Geen economische waarde:</strong> Virtuele in-game valuta en bezittingen hebben geen economische, financiële of tastbare waarde in de echte wereld. Het is niet mogelijk in-game bezit in te wisselen voor echt geld of reële goederen.
            </p>
            <p>
                5.3. <strong>Verbod op Real Money Trading (RMT):</strong> Het kopen, verkopen of ruilen van accounts, virtuele valuta of items tegen echt geld of externe diensten is verboden en leidt tot onmiddellijke permanente uitsluiting van het platform.
            </p>
            <p>
                5.4. Aangezien er op het platform niets wordt verkocht, zijn consumentenregelingen inzake herroeping of bedenktijd voor betaalde aankopen niet van toepassing op dit kosteloze spel.
            </p>
        </section>

        <!-- Artikel 6 -->
        <section class="space-y-2.5">
            <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 flex items-center justify-center text-xs font-black">6</span>
                Aansprakelijkheid & Beschikbaarheid
            </h2>
            <p>
                6.1. Het spel wordt kosteloos ter beschikking gesteld op een <em>"as is"</em> en <em>"as available"</em> basis. De beheerder garandeert geen ononderbroken, foutloze of constante beschikbaarheid van de servers.
            </p>
            <p>
                6.2. De beheerder is, behoudens opzet of grove schuld, niet aansprakelijk voor enige directe of indirecte schade, virtueel dataverlies of tijdelijke serveruitval.
            </p>
            <p>
                6.3. De beheerder behoudt zich het recht voor om naar eigen inzicht spelmechanieken te balanceren, updates door te voeren, onderhoud uit te voeren of accounts te blokkeren die in strijd handelen met deze voorwaarden.
            </p>
        </section>

        <!-- Artikel 7 -->
        <section class="space-y-2.5">
            <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 flex items-center justify-center text-xs font-black">7</span>
                Intellectueel Eigendom & Auteursrecht
            </h2>
            <p>
                7.1. De intellectuele eigendomsrechten met betrekking tot de softwarecode, teksten, vormgeving, grafische elementen en databanken van TBS Kliniek berusten bij de maker (Daniël / werkenmetdaniel.nl), tenzij anders vermeld.
            </p>
            <p>
                7.2. Het is niet toegestaan om zonder voorafgaande schriftelijke toestemming substantiële delen van de spelcode of ontwerpen te kopiëren, te reverse-engineeren of commercieel te exploiteren.
            </p>
        </section>

        <!-- Artikel 8 -->
        <section class="space-y-2.5">
            <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 flex items-center justify-center text-xs font-black">8</span>
                Toepasselijk Recht & Contact
            </h2>
            <p>
                8.1. Op deze algemene voorwaarden en het gebruik van het platform is uitsluitend het <strong>Nederlands recht</strong> van toepassing.
            </p>
            <p>
                8.2. Voor vragen, opmerkingen of contact omtrent deze voorwaarden kun je terecht via:
            </p>
            <div class="p-3 bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 rounded-xl text-xs space-y-1">
                <p><strong>Platform:</strong> tbs-kliniek.nl</p>
                <p><strong>Beheer / Portfolio:</strong> <a href="https://werkenmetdaniel.nl" target="_blank" rel="noopener" class="text-purple-600 dark:text-purple-400 font-bold underline">werkenmetdaniel.nl</a></p>
                <p><strong>E-mailadres:</strong> <em>contact@tbs-kliniek.nl</em></p>
                <p><strong>Spelondersteuning:</strong> Via het in-game <a href="{{ route('help.tickets.index') }}" class="text-purple-600 dark:text-purple-400 font-bold underline">Ticket Support Systeem</a></p>
            </div>
        </section>

    </div>

</div>
@endsection
