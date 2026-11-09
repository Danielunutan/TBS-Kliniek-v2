<?php

use App\Models\Character;
use App\Models\ChatMessage;
use App\Models\NewsArticle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $authorChar = Character::where('name', 'Daniël')
            ->orWhere('name', 'Daniel')
            ->first() ?? Character::first();

        $authorId = $authorChar?->id;

        $content = <<<'HTML'
<div class="space-y-6 text-slate-700 dark:text-slate-300 leading-relaxed">
    <p class="text-base sm:text-lg font-medium text-slate-900 dark:text-white">
        Om de strijd op de kliniekafdelingen dynamischer, eerlijker en actiever te maken voor iedereen, heeft de directie vandaag een grondige herziening doorgevoerd op het liquidatie- en gevechtssysteem. 
    </p>

    <div class="rounded-2xl bg-rose-50/80 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 p-5 space-y-3">
        <h3 class="text-lg font-bold text-rose-900 dark:text-rose-200 flex items-center gap-2">
            <i class="fa-solid fa-crosshairs text-rose-600"></i> Kogelplafond en Vergrote Slagkracht
        </h3>
        <p class="text-sm">
            Niemand binnen de kliniek is meer een onbereikbare tank. Het aantal vereiste kogels is drastisch omlaag gebracht en de slagkracht per patroon is vervijfvoudigd. Waar voorheen duizenden patronen nodig waren, ligt het absolute maximum nu op 450 patronen bij de allereerste graad, en loopt dit snel af naar slechts 10 tot 35 patronen voor topschutters. Hierdoor heeft elke actieve bende met goede voorbereiding altijd een reële kans om een doelwit neer te halen.
        </p>
    </div>

    <div class="rounded-2xl bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/60 p-5 space-y-3">
        <h3 class="text-lg font-bold text-emerald-900 dark:text-emerald-200 flex items-center gap-2">
            <i class="fa-solid fa-vault text-emerald-600"></i> Bank en Kluis 100 Procent Veilig
        </h3>
        <p class="text-sm">
            Harde arbeid en slim sparen worden beschermd. Bij een succesvolle liquidatie wordt er vanaf nu <strong>nul euro</strong> van de bankrekening of uit de kluis van het slachtoffer gestolen. Aanvallers maken uitsluitend buit op contant zakgeld (maximaal 45 procent). Zet je vermogen netjes op de bank en je zuurverdiende fortuin blijft te allen tijde gegarandeerd van jou.
        </p>
    </div>

    <div class="rounded-2xl bg-indigo-50/80 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-900/60 p-5 space-y-3">
        <h3 class="text-lg font-bold text-indigo-900 dark:text-indigo-200 flex items-center gap-2">
            <i class="fa-solid fa-scale-balanced text-indigo-600"></i> Eerlijke en Wederzijdse Levelgrenzen
        </h3>
        <p class="text-sm">
            De regel van maximaal 15 levels verschil geldt vanaf nu strikt in beide richtingen. Het is niet langer mogelijk om van grote afstand spelers buiten bereik aan te vallen als diegene niet op gelijke wijze terug kan slaan. Ter bescherming van alle spelers geldt de strikte limiet van maximaal 15 levels verschil op alle liquidaties.
        </p>
    </div>

    <div class="rounded-2xl bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 p-5 space-y-3">
        <h3 class="text-lg font-bold text-amber-900 dark:text-amber-200 flex items-center gap-2">
            <i class="fa-solid fa-heart-pulse text-amber-600"></i> Sneller Herstel en Vol Uur Medisch Schild
        </h3>
        <p class="text-sm">
            Niemand wil urenlang vastzitten zonder te kunnen spelen. De tijd op de Intensive Care na een liquidatie is gehalveerd van 20 minuten naar slechts 10 minuten. Daarnaast ontvangt elke patiënt na het ontwaken automatisch een <strong>Medisch Infuus-Schild van 60 minuten</strong>. Binnen dat uur kun je ongestoord bewegen, praten, werken en hosselen zonder aangevallen te kunnen worden. Zodra je zélf besluit een vijand aan te vallen, vervalt je bescherming en sta je weer op scherp.
        </p>
    </div>

    <div class="rounded-2xl bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900/60 p-5 space-y-3">
        <h3 class="text-lg font-bold text-blue-900 dark:text-blue-200 flex items-center gap-2">
            <i class="fa-solid fa-shield-halved text-blue-600"></i> Tactische Dagelijkse Liquidaties
        </h3>
        <p class="text-sm">
            Om ervoor te zorgen dat liquidaties tactisch blijven en pesten wordt voorkomen, geldt een daglimiet van maximaal 5 willekeurige liquidaties per dag (onbeperkt bij officiële Bendeoorlog &amp; Bloedwraak) en een anti-pestslot van maximaal 3 liquidaties per doelwit per dag.
        </p>
    </div>

    <p class="text-sm text-slate-500 dark:text-slate-400 italic">
        Pak je munitie, overleg met je bende en verover je positie binnen het TBS complex. Veel succes en strijdvaardigheid gewenst namens het hele team.
    </p>
</div>
HTML;

        NewsArticle::updateOrCreate(
            ['slug' => 'grote-kliniek-balans-en-vechtsysteem-herziening'],
            [
                'title' => 'Grote Kliniek Balans & Vechtsysteem Herziening',
                'image_url' => 'images/news/pvp_update.png',
                'content' => $content,
                'character_id' => $authorId,
                'is_published' => true,
                'published_at' => now(),
                'is_pinned' => true,
            ]
        );

        if ($authorChar && Schema::hasTable('chat_messages')) {
            try {
                $newsUrl = route('news.show', 'grote-kliniek-balans-en-vechtsysteem-herziening');
                $chatMsg = "📢 **[DIRECTIE BERICHT]** Er is zojuist een grote **Balans & Vechtsysteem Herziening** doorgevoerd! Kogelplafonds verlaagd, bank en kluis 100% veilig bij liquidatie, wederzijdse levelgrenzen en een heel uur medische bescherming na coma. Lees alle details in het nieuwsartikel {$newsUrl}";

                foreach (['global', 'algemeen'] as $ch) {
                    ChatMessage::firstOrCreate(
                        [
                            'character_id' => $authorChar->id,
                            'channel' => $ch,
                            'message' => $chatMsg,
                        ],
                        [
                            'hearts_count' => 0,
                            'hearts_users' => [],
                        ]
                    );
                    Cache::forget("chat_last_id_{$ch}");
                }
            } catch (Throwable) {
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        NewsArticle::where('slug', 'grote-kliniek-balans-en-vechtsysteem-herziening')->delete();
    }
};
