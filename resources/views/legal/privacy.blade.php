@extends('layouts.app')

@section('title', 'Privacybeleid (AVG / GDPR) - TBS Kliniek')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs relative overflow-hidden">
        <div class="inline-flex items-center gap-2 bg-purple-50 text-purple-700 text-xs font-bold px-3 py-1 rounded-full border border-purple-200 mb-2">
            <i class="fa-solid fa-user-shield text-purple-600"></i> AVG & Gegevensbescherming
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Privacybeleid</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Conform de Europese Algemene Verordening Gegevensbescherming (AVG / GDPR) • Laatst bijgewerkt: {{ date('d F Y') }}
        </p>
    </div>

    <!-- Main Content Body -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs space-y-8 text-sm text-slate-700 leading-relaxed">

        <!-- Intro -->
        <section class="space-y-2">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold">1</span>
                Wie zijn wij (Verwerkingsverantwoordelijke)?
            </h2>
            <p>
                TBS Kliniek (bereikbaar via <strong>tbs-kliniek.nl</strong>) respecteert de privacy van alle bezoekers en geregistreerde spelers. Wij zorgen ervoor dat de persoonlijke informatie die je ons verschaft vertrouwelijk en in overeenstemming met de Europese privacywetgeving (AVG) wordt behandeld.
            </p>
            <p>
                Voor vragen over gegevensbescherming kun je contact opnemen via een Support Ticket in het spel of per e-mail via <em>privacy@tbs-kliniek.nl</em>.
            </p>
        </section>

        <!-- Artikel 2: Welke gegevens -->
        <section class="space-y-2">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold">2</span>
                Welke Persoonsgegevens Verwerken Wij?
            </h2>
            <p>Wij verzamelen uitsluitend gegevens die strikt noodzakelijk zijn voor het functioneren van het spel en de beveiliging van jouw account:</p>
            <ul class="list-disc pl-5 space-y-1 text-slate-600">
                <li><strong>Accountgegevens:</strong> Gebruikersnaam, e-mailadres en een cryptografisch gehasht wachtwoord (via moderne algoritmes, wij kunnen jouw wachtwoord nooit in platte tekst zien).</li>
                <li><strong>Spelstatistieken:</strong> Virtuele karakternaam, level, spelvoortgang, inventaris, in-game berichten en tijdsstempels van acties.</li>
                <li><strong>Technische & Loggegevens:</strong> IP-adres, browser user-agent, tijdstip van inloggen en sessie-identificatoren (gebruikt voor accountbeveiliging en botpreventie).</li>
                <li><strong>Cookie-voorkeuren:</strong> De status van jouw toestemming voor het plaatsen van functionele en advertentiecookies.</li>
            </ul>
        </section>

        <!-- Artikel 3: Doeleinden & Rechtsgrond -->
        <section class="space-y-2">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold">3</span>
                Doeleinden & Rechtsgronden van Verwerking
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl">
                    <h4 class="font-extrabold text-xs text-slate-900 mb-1">Uitvoering Overeenkomst</h4>
                    <p class="text-xs text-slate-500">Om jouw spelaccount aan te maken, inloggen mogelijk te maken en jouw spelvoortgang op te slaan.</p>
                </div>
                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl">
                    <h4 class="font-extrabold text-xs text-slate-900 mb-1">Gerechtvaardigd Belang</h4>
                    <p class="text-xs text-slate-500">Het beveiligen van de servers, tegengaan van cheats/bots en voorkomen van ongeautoriseerde toegang.</p>
                </div>
                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl">
                    <h4 class="font-extrabold text-xs text-slate-900 mb-1">Jouw Toestemming</h4>
                    <p class="text-xs text-slate-500">Voor het tonen van gerichte externe advertenties (o.a. Monetag / sponsoren) via de cookiebanner.</p>
                </div>
            </div>
        </section>

        <!-- Artikel 4: Bewaartermijnen -->
        <section class="space-y-2">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold">4</span>
                Bewaartermijnen
            </h2>
            <p>
                Wij bewaren jouw gegevens niet langer dan noodzakelijk is. Accountgegevens worden bewaard zolang jouw spelaccount actief is. Wanneer een account wordt verwijderd, worden de bijbehorende persoonlijke identificatoren (e-mail en IP-logs) definitief gewist of geanonimiseerd.
            </p>
        </section>

        <!-- Artikel 5: Delen met derden -->
        <section class="space-y-2">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold">5</span>
                Delen van Gegevens met Derden
            </h2>
            <p>
                Wij verkopen jouw persoonsgegevens <strong>nooit</strong> aan derden. Wij delen uitsluitend gegevens met verwerkers die noodzakelijk zijn voor de technische werking van onze dienst:
            </p>
            <ul class="list-disc pl-5 space-y-1 text-slate-600">
                <li><strong>Hosting & Serverinfrastructuur:</strong> Beveiligde servers in de Europese Unie (o.a. OVHcloud).</li>
                <li><strong>DDoS & Botbescherming:</strong> Cloudflare (voor verificatie via Turnstile en netwerkbeveiliging).</li>
                <li><strong>Advertentiepartners:</strong> Wanneer je advertenties bekijkt in de Sponsorkamer, kunnen externe netwerken (zoals Monetag / PropellerAds) cookies of identificatoren verwerken conform hun eigen privacybeleid en mits jij hiervoor toestemming hebt gegeven.</li>
            </ul>
        </section>

        <!-- Artikel 6: Google AdSense & Externe Advertentienetwerken -->
        <section class="space-y-3">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold">6</span>
                Google AdSense & Advertentiecookies
            </h2>
            <p>
                Wij maken op onze website gebruik van <strong>Google AdSense</strong> (aangeboden door Google LLC / Google Ireland Limited) om advertenties weer te geven. Om te voldoen aan de strenge richtlijnen van Google en de Europese privacywetgeving (AVG) informeren wij je over het volgende:
            </p>
            <ul class="list-disc pl-5 space-y-1.5 text-slate-600">
                <li>Externe leveranciers, waaronder <strong>Google</strong>, gebruiken cookies om advertenties weer te geven op basis van eerdere bezoeken van gebruikers aan onze website of aan andere websites op het internet.</li>
                <li>Door het gebruik van advertentiecookies kunnen Google en zijn partners advertenties weergeven aan onze gebruikers op basis van hun bezoek aan onze website en/of andere websites op het internet.</li>
                <li>Gebruikers kunnen zich afmelden voor gepersonaliseerde advertenties door naar <a href="https://adssettings.google.com" target="_blank" rel="noopener noreferrer" class="text-purple-600 font-bold hover:underline">Google Advertentie-instellingen</a> te gaan.</li>
                <li>Als alternatief kunnen gebruikers zich afmelden voor cookies van externe leveranciers voor gepersonaliseerde reclame door naar <a href="https://www.aboutads.info" target="_blank" rel="noopener noreferrer" class="text-purple-600 font-bold hover:underline">www.aboutads.info</a> of <a href="https://www.youronlinechoices.eu/" target="_blank" rel="noopener noreferrer" class="text-purple-600 font-bold hover:underline">Your Online Choices (EU)</a> te gaan.</li>
                <li>Meer informatie over hoe Google gegevens gebruikt wanneer je onze partnersites gebruikt, vind je op: <a href="https://policies.google.com/technologies/partner-sites" target="_blank" rel="noopener noreferrer" class="text-purple-600 font-bold hover:underline">Hoe Google gegevens gebruikt op sites van partners</a>.</li>
            </ul>
        </section>

        <!-- Artikel 7: Rechten van Betrokkenen -->
        <section class="space-y-2">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold">7</span>
                Jouw Rechten (Inzage, Verwijdering & Bezwaar)
            </h2>
            <p>Onder de AVG beschik je over de volgende rechten:</p>
            <ul class="list-disc pl-5 space-y-1 text-slate-600">
                <li><strong>Recht op inzage:</strong> Je mag te allen tijde opvragen welke gegevens wij over jou bewaren.</li>
                <li><strong>Recht op rectificatie:</strong> Je kunt onjuiste gegevens (zoals je wachtwoord of e-mail) laten corrigeren.</li>
                <li><strong>Recht op gegevenswissing ("Recht op Vergetelheid"):</strong> Je kunt te allen tijde verzoeken om volledige verwijdering van je account en gegevens via een Support Ticket.</li>
                <li><strong>Recht van bezwaar & intrekken toestemming:</strong> Je kunt je toestemming voor advertentiecookies op elk gewenst moment intrekken via de cookie-instellingen in de footer.</li>
                <li><strong>Klacht indienen:</strong> Je hebt het recht om een klacht in te dienen bij de <em>Autoriteit Persoonsgegevens (AP)</em> indien je meent dat wij jouw gegevens onrechtmatig verwerken.</li>
            </ul>
        </section>

    </div>

</div>
@endsection
