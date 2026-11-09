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
        De kliniekleiding introduceert vandaag een reeks grote vernieuwingen gericht op zinvolle bendeconflicten, nieuwe prestigieuze geldputten en eerlijke beloningen voor trouwe bendespelers.
    </p>

    <div class="rounded-2xl bg-rose-50/80 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 p-5 space-y-3">
        <h3 class="text-lg font-bold text-rose-900 dark:text-rose-200 flex items-center gap-2">
            <i class="fa-solid fa-flag text-rose-600"></i> Van Doelloos Schieten naar Officiële 24-Uurs Bendeoorlogen
        </h3>
        <p class="text-sm">
            Willekeurig en doelloos patiënten neerleggen levert buiten een officiële oorlog of bloedwraak vanaf heden nul aanzien op. In plaats daarvan kunnen bendeleiders nu een officiële 24-uurs bendeoorlog uitroepen. Gedurende 24 uur vechten bendes om de strategische zones van de kliniek zoals de Luchtplaats, de Kantine en de Smokkellijn.
        </p>
        <p class="text-sm">
            Elke zoneovername levert 15 oorlogspunten op, een succesvolle overval brengt 25 punten en het uitschakelen van een rivaliserend bendelid levert maar liefst 50 punten op. De winnende bende claimt na 24 uur tien procent van de vijandelijke bendekas, 100 respect en 24 uur lang Kliniek Heerschappij met 20 procent extra bende-uurloon.
        </p>
    </div>

    <div class="rounded-2xl bg-indigo-50/80 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-900/60 p-5 space-y-3">
        <h3 class="text-lg font-bold text-indigo-900 dark:text-indigo-200 flex items-center gap-2">
            <i class="fa-solid fa-bed text-indigo-600"></i> Mijn TBS Cel en Upgrades (Prestigieuze Geldput)
        </h3>
        <p class="text-sm">
            Spelers met miljoenen op de bank kunnen hun vermogen nu steken in hun persoonlijke patiëntenverblijf via Mijn TBS Cel. Er zijn vier uitgebreide upgrade-lijnen beschikbaar tot en met level vijf.
        </p>
        <ul class="list-disc list-inside text-sm space-y-1.5 pl-2">
            <li><strong>Luxe Boxspring en Traagschuim Matras</strong> voor tot wel 40 procent sneller energieherstel.</li>
            <li><strong>Versterkte Celdeur en Camerasysteem</strong> voor tot plus 2000 passieve celverdediging bij overvallen.</li>
            <li><strong>Verborgen Vloerkluis</strong> waardoor tot 50 procent van je contant zakgeld beschermd blijft.</li>
            <li><strong>Luxe Lounge en Smart TV</strong> voor extra comfort en tot 25 procent bonus op respect en ervaringspunten.</li>
        </ul>
    </div>

    <div class="rounded-2xl bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 p-5 space-y-3">
        <h3 class="text-lg font-bold text-amber-900 dark:text-amber-200 flex items-center gap-2">
            <i class="fa-solid fa-coins text-amber-600"></i> Bende-uurloon Meegroeiend met Level en Inzet
        </h3>
        <p class="text-sm">
            Het uurloon binnen bendes is vernieuwd. Hooggeplaatste leden en bendeleiders die veel tijd en inzet in het spel steken ontvangen nu een aanzienlijk hoger persoonlijk uurloon. Elk bereikt level geeft 2.5 procent extra op het basisloon en leidinggevende rollen ontvangen tot 25 procent leiderschapstoeslag. Actieve spelers behouden hun volledige loon terwijl langdurig inactieve accounts automatisch terugzakken.
        </p>
    </div>
</div>
HTML;

        if (Schema::hasTable('news_articles')) {
            NewsArticle::updateOrCreate(
                ['slug' => 'bendeoorlogen-celupgrades-en-nieuw-uurloon-systeem'],
                [
                    'title' => 'Bendeoorlogen, Celupgrades en Nieuw Uurloon Systeem',
                    'content' => $content,
                    'character_id' => $authorId,
                    'is_published' => true,
                    'published_at' => now(),
                ]
            );

            Cache::forget('latest_news_articles');
            Cache::forget('homepage_news_articles');
        }

        if (Schema::hasTable('chat_messages') && $authorChar) {
            ChatMessage::create([
                'character_id' => $authorChar->id,
                'message' => '🚨 NIEUWS! Officiële 24-uurs bendeoorlogen om kliniekzones, persoonlijke celupgrades en een nieuw uurloon systeem zijn zojuist live gegaan! Lees het volledige nieuwsbericht voor alle details.',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('news_articles')) {
            NewsArticle::where('slug', 'bendeoorlogen-celupgrades-en-nieuw-uurloon-systeem')
                ->orWhere('title', 'Bendeoorlogen, Celupgrades en Nieuw Uurloon Systeem')
                ->delete();
        }
    }
};
