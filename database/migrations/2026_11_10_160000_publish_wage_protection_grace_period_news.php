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
        Goed nieuws voor alle hardwerkende patiënten en bendeleden van TBS Kliniek! Naar aanleiding van jullie feedback hebben we zojuist een veelgevraagde veiligheidsupdate uitgerold: vanaf nu is je zuurverdiende geld direct na uitbetaling beschermd tegen inhalige rovers en overvallers.
    </p>

    <div class="rounded-2xl bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/60 p-5 space-y-3">
        <h3 class="text-lg font-bold text-emerald-900 dark:text-emerald-200 flex items-center gap-2">
            <i class="fa-solid fa-shield-halved text-emerald-600"></i> 15 Minuten 100% Cash Bescherming na Loon & Uitkering
        </h3>
        <p class="text-sm">
            Niets is zo frustrerend als het innen van je bende-uurloon of de kliniekuitkering, om vervolgens binnen vijf seconden leeggetrokken te worden door een loerende zakkenroller. 
        </p>
        <p class="text-sm">
            Vanaf heden ontvangt iedere speler na het claimen van contant bende-uurloon of het ontvangen van de 15-minuten kliniekuitkering automatisch een <strong>15 minuten durend beschermingsschild</strong> op zijn contante geld. Gedurende dit kwartier kunnen andere patiënten je contante geld niet stelen via zakkenrollen, gevechten op de Luchtplaats of huurmoordenaarscontracten.
        </p>
    </div>

    <div class="rounded-2xl bg-indigo-50/80 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-900/60 p-5 space-y-3">
        <h3 class="text-lg font-bold text-indigo-900 dark:text-indigo-200 flex items-center gap-2">
            <i class="fa-solid fa-building-columns text-indigo-600"></i> Keuzevrijheid: Direct naar Bankrekening of Zakgeld
        </h3>
        <p class="text-sm">
            Geen zin om na het claimen van je loon handmatig naar de bank te navigeren? Op de Bende-Uurloon pagina vind je nu twee duidelijke knoppen:
        </p>
        <ul class="list-disc list-inside text-sm space-y-1.5 pl-2">
            <li><strong>Claim naar Zakgeld:</strong> Het bedrag komt direct in je contante zakken inclusief de 15 minuten 100% diefstalbescherming. Ideaal als je het direct wilt inzetten voor klusjes, het casino of de zwarte markt.</li>
            <li><strong>Direct naar Bank:</strong> Het volledige bende-uurloon wordt in één klik veilig op je beveiligde bankrekening bijgeschreven zonder tussenkomst van contant geld.</li>
        </ul>
    </div>

    <div class="rounded-2xl bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 p-5 space-y-3">
        <h3 class="text-lg font-bold text-amber-900 dark:text-amber-200 flex items-center gap-2">
            <i class="fa-solid fa-scale-balanced text-amber-600"></i> Fair Play Regel: Zelf Aanvallen Verbreekt het Schild
        </h3>
        <p class="text-sm">
            Eerlijk spel staat bij ons voorop. Het beschermingsschild is bedoeld om je welverdiende centen veilig te stellen en rustig te bankieren, niet om als onschendbare sluipmoordenaar andere patiënten aan te vallen.
        </p>
        <p class="text-sm">
            Wanneer je zélf het initiatief neemt om een medepatiënt te beroven of aan te vallen op de Luchtplaats, vervalt jouw eigen 15-minuten bescherming per direct. Wie uitdeelt, moet immers ook kunnen incasseren!
        </p>
    </div>

    <div class="rounded-2xl bg-purple-50/80 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-900/60 p-5 space-y-3">
        <h3 class="text-lg font-bold text-purple-900 dark:text-purple-200 flex items-center gap-2">
            <i class="fa-solid fa-clock text-purple-600"></i> Realtime Indicatoren & Live Timers
        </h3>
        <p class="text-sm">
            Je weet altijd precies waar je aan toe bent. Zodra de bescherming actief is zie je een groen schildje met aftellende timer in:
        </p>
        <ul class="list-disc list-inside text-sm space-y-1.5 pl-2">
            <li>De mobiele navigatiebalk direct naast je contante saldo</li>
            <li>De desktop menubalk naast je saldo-overzicht</li>
            <li>Het hoofddashboard onder je contant geld tegel</li>
            <li>De snelle stortingsmodal van de bank</li>
            <li>De bende-uurloon pagina met een speciale statusbanner</li>
        </ul>
    </div>
</div>
HTML;

        if (Schema::hasTable('news_articles')) {
            NewsArticle::updateOrCreate(
                ['slug' => '15-minuten-diefstalbescherming-uurloon-en-bankstorting'],
                [
                    'title' => 'Update: 15 Minuten Diefstalbescherming na Bende-Uurloon & Direct Storten naar Bank!',
                    'slug' => '15-minuten-diefstalbescherming-uurloon-en-bankstorting',
                    'emoji' => '🛡️',
                    'content' => $content,
                    'character_id' => $authorId,
                    'is_pinned' => false,
                    'comments_enabled' => true,
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
                'message' => '🛡️ UPDATE! 15 minuten diefstalbescherming na het claimen van bende-uurloon en uitkering is nu live! Ook kun je bende-uurloon nu in één klik direct naar de bank storten. Check het nieuwsartikel!',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('news_articles')) {
            NewsArticle::where('slug', '15-minuten-diefstalbescherming-uurloon-en-bankstorting')->delete();
        }
    }
};
