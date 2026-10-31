@extends('layouts.app')

@section('title', 'Cookiebeleid - TBS Kliniek')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xs relative overflow-hidden">
        <div class="inline-flex items-center gap-2 bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 text-xs font-bold px-3 py-1 rounded-full border border-purple-200/80 dark:border-purple-800/70 mb-2">
            <i class="fa-solid fa-cookie-bite text-purple-600 dark:text-purple-400"></i> ePrivacy & Cookies
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Cookiebeleid</h1>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
            Conform artikel 11.7a van de Nederlandse Telecommunicatiewet & AVG • Laatst bijgewerkt {{ date('d F Y') }}
        </p>
    </div>

    <!-- Main Content Body -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xs space-y-8 text-sm text-slate-700 dark:text-slate-300 leading-relaxed">

        <!-- Wat zijn cookies -->
        <section class="space-y-2">
            <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 flex items-center justify-center text-xs font-bold">1</span>
                Wat zijn Cookies?
            </h2>
            <p>
                Cookies zijn kleine tekstbestanden die door een website op jouw computer, tablet of mobiele telefoon worden geplaatst wanneer je de website bezoekt. Hierin wordt informatie opgeslagen om jouw bezoek prettiger te maken (zoals ingelogd blijven) en om de website goed te laten functioneren.
            </p>
        </section>

        <!-- Categorieën cookies -->
        <section class="space-y-4">
            <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 flex items-center justify-center text-xs font-bold">2</span>
                Welke Cookies Gebruikt TBS Kliniek?
            </h2>

            <div class="space-y-4">

                <!-- 1. Noodzakelijk -->
                <div class="p-4 bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-extrabold text-slate-900 dark:text-white text-sm flex items-center gap-2">
                            <i class="fa-solid fa-shield-check text-emerald-600 dark:text-emerald-400"></i> Strikt Noodzakelijke Cookies
                        </span>
                        <span class="text-[10px] font-black uppercase bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/70 px-2 py-0.5 rounded-full">Altijd Actief</span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-400">
                        Deze cookies zijn technisch vereist om het spel te laten werken. Zonder deze cookies kun je niet inloggen, worden formulieren niet beveiligd tegen Cross-Site Request Forgery (CSRF) en functioneert de spel-engine niet.
                    </p>
                    <table class="w-full text-xs text-left text-slate-500 dark:text-slate-400 mt-2">
                        <thead>
                            <tr class="border-b border-slate-200/80 dark:border-slate-700/80 text-slate-700 dark:text-slate-300 font-bold">
                                <th class="py-1">Cookie Naam</th>
                                <th class="py-1">Doel</th>
                                <th class="py-1">Bewaartermijn</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            <tr>
                                <td class="py-1 font-mono font-bold text-slate-800 dark:text-slate-200">tbs_session</td>
                                <td class="py-1">Behoudt jouw ingelogde sessie en authenticatie</td>
                                <td class="py-1">Sessie (tot sluiten browser of 7 dagen)</td>
                            </tr>
                            <tr>
                                <td class="py-1 font-mono font-bold text-slate-800 dark:text-slate-200">XSRF-TOKEN</td>
                                <td class="py-1">Beveiliging tegen ongeautoriseerde POST-aanvragen</td>
                                <td class="py-1">Sessie</td>
                            </tr>
                            <tr>
                                <td class="py-1 font-mono font-bold text-slate-800 dark:text-slate-200">tbs_cookie_consent</td>
                                <td class="py-1">Onthoudt jouw cookievoorkeuren in de browser (localStorage)</td>
                                <td class="py-1">12 maanden</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- 2. Functioneel / Voorkeuren -->
                <div class="p-4 bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-extrabold text-slate-900 dark:text-white text-sm flex items-center gap-2">
                            <i class="fa-solid fa-sliders text-purple-600 dark:text-purple-400"></i> Functionele & Voorkeur Cookies
                        </span>
                        <span class="text-[10px] font-black uppercase bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70 px-2 py-0.5 rounded-full">Optioneel</span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-400">
                        Deze cookies onthouden jouw persoonlijke spelinstellingen, zoals de staat van het navigatiemenu, audio-instellingen en helpbot-interacties.
                    </p>
                </div>

                <!-- 3. Marketing & Advertenties -->
                <div class="p-4 bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-extrabold text-slate-900 dark:text-white text-sm flex items-center gap-2">
                            <i class="fa-solid fa-rectangle-ad text-amber-600 dark:text-amber-400"></i> Advertentie & Google AdSense Cookies
                        </span>
                        <span class="text-[10px] font-black uppercase bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70 px-2 py-0.5 rounded-full">Toestemming Vereist</span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-400">
                        Wij maken gebruik van Google AdSense en geselecteerde advertentiepartners om advertenties weer te geven op onze website en in de Sponsorkamer. Deze partijen plaatsen advertentiecookies om advertenties af te stemmen op jouw interesses, advertentiefrequentie te beperken en klikfraude tegen te gaan. Deze cookies worden uitsluitend geplaatst na jouw toestemming.
                    </p>
                    <table class="w-full text-xs text-left text-slate-500 dark:text-slate-400 mt-2">
                        <thead>
                            <tr class="border-b border-slate-200/80 dark:border-slate-700/80 text-slate-700 dark:text-slate-300 font-bold">
                                <th class="py-1">Cookie / Identifier</th>
                                <th class="py-1">Aanbieder</th>
                                <th class="py-1">Doel</th>
                                <th class="py-1">Bewaartermijn</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            <tr>
                                <td class="py-1 font-mono font-bold text-slate-800 dark:text-slate-200">__gads, __gpi</td>
                                <td class="py-1">Google AdSense</td>
                                <td class="py-1">Meting van advertentieprestaties, frequentielimiet en fraudepreventie</td>
                                <td class="py-1">13 maanden</td>
                            </tr>
                            <tr>
                                <td class="py-1 font-mono font-bold text-slate-800 dark:text-slate-200">IDE, DSID</td>
                                <td class="py-1">Google DoubleClick</td>
                                <td class="py-1">Gerichte advertentieweergave op basis van eerdere bezoeken</td>
                                <td class="py-1">12 maanden</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </section>

        <!-- Beheren en verwijderen -->
        <section class="space-y-3">
            <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 flex items-center justify-center text-xs font-bold">3</span>
                Hoe kun je jouw Cookie-instellingen Wijzigen?
            </h2>
            <p>
                Je kunt jouw toestemming op elk moment herzien of intrekken. Klik op de knop hieronder om het voorkeurenvenster direct opnieuw te openen
            </p>
            <div>
                <button type="button" onclick="window.tbsOpenCookieSettings()"
                        class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs transition shadow-xs cursor-pointer inline-flex items-center gap-2 active:scale-95">
                    <i class="fa-solid fa-cookie-bite"></i>
                    <span>Cookie-instellingen Openen & Aanpassen</span>
                </button>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 pt-1">
                Daarnaast kun je cookies altijd handmatig verwijderen via de instellingen van jouw webbrowser (zoals Chrome, Safari, Firefox of Edge).
            </p>
        </section>

    </div>

</div>
@endsection
