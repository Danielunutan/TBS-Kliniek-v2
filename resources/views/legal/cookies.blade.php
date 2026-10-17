@extends('layouts.app')

@section('title', 'Cookiebeleid - TBS Kliniek')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs relative overflow-hidden">
        <div class="inline-flex items-center gap-2 bg-purple-50 text-purple-700 text-xs font-bold px-3 py-1 rounded-full border border-purple-200 mb-2">
            <i class="fa-solid fa-cookie-bite text-purple-600"></i> ePrivacy & Cookies
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Cookiebeleid</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Conform artikel 11.7a van de Nederlandse Telecommunicatiewet & AVG • Laatst bijgewerkt: {{ date('d F Y') }}
        </p>
    </div>

    <!-- Main Content Body -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs space-y-8 text-sm text-slate-700 leading-relaxed">

        <!-- Wat zijn cookies -->
        <section class="space-y-2">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold">1</span>
                Wat zijn Cookies?
            </h2>
            <p>
                Cookies zijn kleine tekstbestanden die door een website op jouw computer, tablet of mobiele telefoon worden geplaatst wanneer je de website bezoekt. Hierin wordt informatie opgeslagen om jouw bezoek prettiger te maken (zoals ingelogd blijven) en om de website goed te laten functioneren.
            </p>
        </section>

        <!-- Categorieën cookies -->
        <section class="space-y-4">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold">2</span>
                Welke Cookies Gebruikt TBS Kliniek?
            </h2>

            <div class="space-y-4">

                <!-- 1. Noodzakelijk -->
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-shield-check text-emerald-600"></i> Strikt Noodzakelijke Cookies
                        </span>
                        <span class="text-[10px] font-black uppercase bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full">Altijd Actief</span>
                    </div>
                    <p class="text-xs text-slate-600">
                        Deze cookies zijn technisch vereist om het spel te laten werken. Zonder deze cookies kun je niet inloggen, worden formulieren niet beveiligd tegen Cross-Site Request Forgery (CSRF) en functioneert de spel-engine niet.
                    </p>
                    <table class="w-full text-xs text-left text-slate-500 mt-2">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-700 font-bold">
                                <th class="py-1">Cookie Naam</th>
                                <th class="py-1">Doel</th>
                                <th class="py-1">Bewaartermijn</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr>
                                <td class="py-1 font-mono font-bold text-slate-800">tbs_session</td>
                                <td class="py-1">Behoudt jouw ingelogde sessie en authenticatie</td>
                                <td class="py-1">Sessie (tot sluiten browser of 7 dagen)</td>
                            </tr>
                            <tr>
                                <td class="py-1 font-mono font-bold text-slate-800">XSRF-TOKEN</td>
                                <td class="py-1">Beveiliging tegen ongeautoriseerde POST-aanvragen</td>
                                <td class="py-1">Sessie</td>
                            </tr>
                            <tr>
                                <td class="py-1 font-mono font-bold text-slate-800">tbs_cookie_consent</td>
                                <td class="py-1">Onthoudt jouw cookievoorkeuren in de browser (localStorage)</td>
                                <td class="py-1">12 maanden</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- 2. Functioneel / Voorkeuren -->
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-sliders text-purple-600"></i> Functionele & Voorkeur Cookies
                        </span>
                        <span class="text-[10px] font-black uppercase bg-purple-100 text-purple-800 px-2 py-0.5 rounded-full">Optioneel</span>
                    </div>
                    <p class="text-xs text-slate-600">
                        Deze cookies onthouden jouw persoonlijke spelinstellingen, zoals de staat van het navigatiemenu, audio-instellingen en helpbot-interacties.
                    </p>
                </div>

                <!-- 3. Marketing & Advertenties -->
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-rectangle-ad text-amber-600"></i> Advertentie & Google AdSense Cookies
                        </span>
                        <span class="text-[10px] font-black uppercase bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full">Toestemming Vereist</span>
                    </div>
                    <p class="text-xs text-slate-600">
                        Wij maken gebruik van Google AdSense en geselecteerde advertentiepartners om advertenties weer te geven op onze website en in de Sponsorkamer. Deze partijen plaatsen advertentiecookies om advertenties af te stemmen op jouw interesses, advertentiefrequentie te beperken en klikfraude tegen te gaan. Deze cookies worden uitsluitend geplaatst na jouw toestemming.
                    </p>
                    <table class="w-full text-xs text-left text-slate-500 mt-2">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-700 font-bold">
                                <th class="py-1">Cookie / Identifier</th>
                                <th class="py-1">Aanbieder</th>
                                <th class="py-1">Doel</th>
                                <th class="py-1">Bewaartermijn</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr>
                                <td class="py-1 font-mono font-bold text-slate-800">__gads, __gpi</td>
                                <td class="py-1">Google AdSense</td>
                                <td class="py-1">Meting van advertentieprestaties, frequentielimiet en fraudepreventie</td>
                                <td class="py-1">13 maanden</td>
                            </tr>
                            <tr>
                                <td class="py-1 font-mono font-bold text-slate-800">IDE, DSID</td>
                                <td class="py-1">Google DoubleClick</td>
                                <td class="py-1">Gerichte advertentieweergave op basis van eerdere bezoeken</td>
                                <td class="py-1">12 maanden</td>
                            </tr>
                            <tr>
                                <td class="py-1 font-mono font-bold text-slate-800">robin_reward, robin_sid</td>
                                <td class="py-1">Robins Ads</td>
                                <td class="py-1">Verificatie van voltooide sponsor video-advertenties en toekennen van in-game beloningen</td>
                                <td class="py-1">Sessie / 30 dagen</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </section>

        <!-- Beheren en verwijderen -->
        <section class="space-y-3">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold">3</span>
                Hoe kun je jouw Cookie-instellingen Wijzigen?
            </h2>
            <p>
                Je kunt jouw toestemming op elk moment herzien of intrekken. Klik op de knop hieronder om het voorkeurenvenster direct opnieuw te openen:
            </p>
            <div>
                <button type="button" onclick="window.tbsOpenCookieSettings()"
                        class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs transition shadow-xs cursor-pointer inline-flex items-center gap-2">
                    <i class="fa-solid fa-cookie-bite"></i>
                    <span>Cookie-instellingen Openen & Aanpassen</span>
                </button>
            </div>
            <p class="text-xs text-slate-500 pt-1">
                Daarnaast kun je cookies altijd handmatig verwijderen via de instellingen van jouw webbrowser (zoals Chrome, Safari, Firefox of Edge).
            </p>
        </section>

    </div>

</div>
@endsection
