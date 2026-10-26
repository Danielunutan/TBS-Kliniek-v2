@extends('layouts.app')

@section('title', 'Disclaimer & Colofon - TBS Kliniek')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs relative overflow-hidden">
        <div class="inline-flex items-center gap-2 bg-purple-50 text-purple-700 text-xs font-bold px-3 py-1 rounded-full border border-purple-200 mb-2">
            <i class="fa-solid fa-circle-exclamation text-purple-600"></i> Fictief Rollenspel Verklaring
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Disclaimer & Colofon</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Belangrijke informatie over het fictieve en satirische karakter van dit browsergame.
        </p>
    </div>

    <!-- Main Content Body -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs space-y-8 text-sm text-slate-700 leading-relaxed">

        <!-- Disclaimer Fictief -->
        <section class="space-y-3">
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-950 flex items-start gap-3">
                <i class="fa-solid fa-triangle-exclamation text-amber-600 text-lg mt-0.5 shrink-0"></i>
                <div class="space-y-1 text-xs">
                    <strong class="font-black uppercase tracking-wider text-amber-900">Belangrijke Mededeling:</strong>
                    <p class="text-amber-800 leading-relaxed">
                        <strong>TBS Kliniek is een 100% fictief online browsergame (RPG / satire)</strong>. Alle situaties, karakters, misdrijven, instellingen, namen van behandelaars en gebeurtenissen in dit spel zijn verzonnen en uitsluitend bedoeld voor recreatieve en amusementsdoeleinden.
                    </p>
                </div>
            </div>

            <p>
                Dit platform is op geen enkele wijze gelieerd aan, gesponsord door, of verbonden met:
            </p>
            <ul class="list-disc pl-5 space-y-1 text-slate-600">
                <li>Het Nederlandse Ministerie van Justitie en Veiligheid;</li>
                <li>De Dienst Justitiële Inrichtingen (DJI);</li>
                <li>Bestaande Forensische Psychiatrische Centra (FPC's) of klinieken in Nederland of het buitenland;</li>
                <li>Echte reclasserings- of politieorganisaties.</li>
            </ul>
            <p>
                Het spel stimuleert noch verheerlijkt reëel crimineel gedrag. Virtuele acties zoals 'zakkenrollen', 'hosselen' of 'vechten' zijn zuiver digitale spelmechanieken.
            </p>
        </section>

        <!-- Colofon -->
        <section class="space-y-2">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold">2</span>
                Colofon & Contact
            </h2>
            <div class="p-4 bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 rounded-xl space-y-2 text-xs">
                <p><strong>Website:</strong> tbs-kliniek.nl</p>
                <p><strong>Status:</strong> Particulier hobbymatig & non-profit project (géén onderneming, geen commerciële verkoop)</p>
                <p><strong>Beheer / Portfolio:</strong> <a href="https://werkenmetdaniel.nl" target="_blank" rel="noopener" class="text-purple-600 dark:text-purple-400 font-bold underline">werkenmetdaniel.nl</a></p>
                <p><strong>E-mail voor algemene zaken:</strong> <em>contact@tbs-kliniek.nl</em></p>
                <p><strong>E-mail voor privacy & AVG:</strong> <em>privacy@tbs-kliniek.nl</em></p>
                <p><strong>Spelondersteuning:</strong> Maak een ticket aan via <a href="{{ route('help.tickets.index') }}" class="text-purple-600 dark:text-purple-400 font-bold underline">Help & Support Tickets</a> of praat met de Zaalwacht AI.</p>
                <p><strong>Discord Community:</strong> <a href="https://discord.gg/tbs" target="_blank" rel="noopener" class="text-purple-600 dark:text-purple-400 font-bold underline">discord.gg/tbs</a></p>
                <p><strong>YouTube:</strong> <a href="https://www.youtube.com/@tbsklinieknl" target="_blank" rel="noopener" class="text-red-600 font-bold underline">TBS-Kliniek op YouTube (@tbsklinieknl)</a></p>
                <p><strong>TikTok:</strong> <a href="https://www.tiktok.com/@tbsklinieknl" target="_blank" rel="noopener" class="text-purple-600 dark:text-purple-400 font-bold underline">@tbsklinieknl</a></p>
                <p><strong>Instagram:</strong> <a href="https://www.instagram.com/tbsklinieknl/" target="_blank" rel="noopener" class="text-purple-600 dark:text-purple-400 font-bold underline">@tbsklinieknl</a></p>
                <p><strong>Twitter / X:</strong> <a href="https://x.com/tbskliniek" target="_blank" rel="noopener" class="text-purple-600 dark:text-purple-400 font-bold underline">@tbskliniek</a></p>
                <p><strong>Hyves:</strong> <a href="https://hyves.nl/#my-profile/hyve?id=Zv3DWejo" target="_blank" rel="noopener" class="text-orange-600 font-bold underline inline-flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded bg-[#f36f21] text-white inline-flex items-center justify-center font-black text-[9px] leading-none shrink-0 shadow-2xs">H</span> TBS Kliniek op Hyves</a></p>
            </div>
        </section>

        <!-- Auteursrecht -->
        <section class="space-y-2">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold">3</span>
                Auteursrecht & Intellectueel Eigendom
            </h2>
            <p>
                Alle rechten van intellectueel eigendom met betrekking tot de spelcode, vormgeving, teksten en illustraties van TBS Kliniek berusten bij de ontwikkelaars en makers van tbs-kliniek.nl. Kopiëren, verspreiden of anderszins verveelvoudigen van spelcontent zonder schriftelijke toestemming is niet toegestaan.
            </p>
        </section>

    </div>

</div>
@endsection
