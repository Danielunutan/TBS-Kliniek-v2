<?php

namespace App\Console\Commands;

use App\Models\Character;
use App\Models\GameNotification;
use App\Models\NewsArticle;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaunchSeasonOneCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tbs:launch-season-one';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close pending tickets and publish official Seizoen 1 news article';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=== 1. BEHANDELEN EN SLUITEN VAN OPENSTAANDE TICKETS ===');

        $adminChar = Character::where('id', 1)->first() ?? Character::first();
        $adminCharId = $adminChar ? $adminChar->id : null;

        // Ticket 37: carootje
        $t37 = SupportTicket::find(37);
        if ($t37 && $t37->status !== 'gesloten') {
            SupportTicketMessage::create([
                'support_ticket_id' => 37,
                'user_id' => $adminChar?->user_id,
                'character_id' => $adminCharId,
                'message' => 'Hoi Carootje, bedankt voor je oplettendheid! We hebben direct een strikte aankoop- en consumptielimiet van maximaal 1x per 6 uur doorgevoerd op zowel multivitaminen als eiwitshakes. Hierdoor kan niemand meer oneindig stats stacken en blijft de competitie 100% eerlijk voor iedereen in Seizoen 1. Heel veel succes in het nieuwe seizoen!',
                'is_staff' => true,
            ]);
            $t37->update([
                'status' => 'gesloten',
                'closed_at' => now(),
                'last_reply_at' => now(),
                'last_reply_by_staff' => true,
            ]);
            $this->info('Ticket #37 beantwoord en gesloten.');
        }

        // Ticket 38: WRDMarco
        $t38 = SupportTicket::find(38);
        if ($t38 && $t38->status !== 'gesloten') {
            SupportTicketMessage::create([
                'support_ticket_id' => 38,
                'user_id' => $adminChar?->user_id,
                'character_id' => $adminCharId,
                'message' => 'Hoi Marco, bedankt voor je scherpe melding! De pagination knoppen werden beinvloed door Turbo caching waardoor dezelfde records bleven staan. Dit is nu direct verholpen met een refresh bypass op alle pagineringen, zodat bij elke pagina de juiste nieuwe records direct worden ingeladen. Veel succes in Seizoen 1!',
                'is_staff' => true,
            ]);
            $t38->update([
                'status' => 'gesloten',
                'closed_at' => now(),
                'last_reply_at' => now(),
                'last_reply_by_staff' => true,
            ]);
            $this->info('Ticket #38 beantwoord en gesloten.');
        }

        // Ticket 39: WRDMarco
        $t39 = SupportTicket::find(39);
        if ($t39 && $t39->status !== 'gesloten') {
            SupportTicketMessage::create([
                'support_ticket_id' => 39,
                'user_id' => $adminChar?->user_id,
                'character_id' => $adminCharId,
                'message' => 'Hoi Marco, bedankt voor het doorgeven! De energievalidatie en real-time synchronisatie bij het starten van de bende heists is geoptimaliseerd zodat je direct met de exacte energiekosten kunt aftrappen namens je bende. Zet hem op in het nieuwe seizoen!',
                'is_staff' => true,
            ]);
            $t39->update([
                'status' => 'gesloten',
                'closed_at' => now(),
                'last_reply_at' => now(),
                'last_reply_by_staff' => true,
            ]);
            $this->info('Ticket #39 beantwoord en gesloten.');
        }

        $this->info('=== 2. ACCOUNT BIJENTEMMER EMAIL BIJWERKEN ===');
        $bijenUser = User::where('name', 'like', '%bijen%')
            ->orWhere('email', 'like', '%bijen%')
            ->orWhere('id', 72)
            ->first();

        if ($bijenUser) {
            $oldEmail = $bijenUser->email;
            $bijenUser->email = 'phactar@gmail.com';
            $bijenUser->email_verified_at = now();
            $bijenUser->save();
            $this->info("Account {$bijenUser->name} (ID: {$bijenUser->id}) e-mailadres succesvol bijgewerkt van '{$oldEmail}' naar 'phactar@gmail.com'.");
        } else {
            $this->warn('Geen bijentemmer / bijenkoning account gevonden.');
        }

        $this->info('=== 3. BENDE STATS, UURLOON EN AANVAL/VERDEDIGING RESETTEN ===');
        if (Schema::hasTable('gangs')) {
            $gangUpdate = [
                'bling_shops' => 0,
                'protection_points' => 1000,
                'attack_power' => 500,
                'treasury' => 0,
                'respect' => 0,
                'masks' => 0,
                'gang_stars' => 0,
                'shield_until' => null,
                'last_attack_at' => null,
                'vip_boost_until' => null,
                'personal_shopper' => null,
                'money_magnet' => null,
                'leave_fee_multiplier' => 0,
                'min_respect' => 0,
            ];
            $gangColumns = Schema::getColumnListing('gangs');
            $filteredGangUpdates = array_intersect_key($gangUpdate, array_flip($gangColumns));
            if (! empty($filteredGangUpdates)) {
                DB::table('gangs')->update($filteredGangUpdates);
            }
        }
        if (Schema::hasTable('control_points')) {
            $cpColumns = Schema::getColumnListing('control_points');
            $cpUpdates = array_intersect_key([
                'current_character_id' => null,
                'current_gang_id' => null,
                'captured_at' => null,
                'total_captures' => 0,
            ], array_flip($cpColumns));
            if (! empty($cpUpdates)) {
                DB::table('control_points')->update($cpUpdates);
            }
        }

        // Wis alle bende gevechten, logs, donaties en bende inventaris
        DB::table('gang_attacks')->truncate();
        DB::table('gang_donations')->truncate();
        DB::table('gang_items')->truncate();
        DB::table('gang_properties')->truncate();
        DB::table('gang_npcs')->truncate();
        DB::table('gang_requests')->truncate();
        if (Schema::hasTable('heist_logs')) {
            DB::table('heist_logs')->truncate();
        }
        if (Schema::hasTable('control_point_logs')) {
            DB::table('control_point_logs')->truncate();
        }
        $this->info('Bendes gereset: uurloon (winkels = 0), attack power (500), bescherming (1000), kas (0), respect (0) en alle gevechtshistorie gewist. Bendes zelf en bende chat zijn behouden.');

        $this->info('=== 4. SEIZOEN 1 UITGEBREID NIEUWSARTIKEL PUBLICEREN ===');

        NewsArticle::query()->update(['is_pinned' => false]);

        $articleContent = '<div class="space-y-6">
    <div class="p-5 rounded-2xl bg-gradient-to-r from-purple-900 via-indigo-950 to-slate-900 text-white border-2 border-amber-400 shadow-md">
        <div class="flex items-center gap-3 mb-2">
            <span class="px-3 py-1 rounded-full bg-amber-500 text-purple-950 text-xs font-black uppercase tracking-wider">Officiële Aftrap</span>
            <span class="text-xs text-amber-300 font-bold">Seizoensduur: Precies 3 Maanden</span>
        </div>
        <h2 class="text-xl sm:text-2xl font-black text-white">Welkom in Seizoen 1 van TBS Kliniek! 🏆</h2>
        <p class="text-xs sm:text-sm text-purple-200 mt-2 leading-relaxed">
            Vandaag openen we het allereerste officiële seizoen in TBS Kliniek! Iedereen begint weer met een schone lei, een frisse economie en 100% gelijke kansen op de kopposities van de ranglijst.
        </p>
    </div>

    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3">
        <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-rotate text-purple-600"></i>
            <span>Wat is er gereset voor de Eerlijke Competitie?</span>
        </h3>
        <ul class="text-xs text-slate-700 dark:text-slate-300 space-y-2 list-disc pl-5">
            <li><strong>Spelersstatus:</strong> Alle patiënten beginnen weer fris op <strong>Level 1</strong> met <strong>€250 contant zakgeld</strong> en <strong>€1.000 op de bank</strong>.</li>
            <li><strong>Stats &amp; Inventarissen:</strong> Alle RPG statistieken zijn teruggezet naar 10 en persoonlijke inventarissen zijn leeggemaakt zodat niemand een oneerlijke voorsprong heeft.</li>
            <li><strong>Bende Economie &amp; Stats:</strong> Alle bende-uurloon (winkels), attack power, beschermingspunten en bende-kas zijn gereset. Ook alle gevechts- en winstatistieken zijn gewist zodat bendes opnieuw moeten strijden voor de overwinning!</li>
            <li><strong>Controleposten:</strong> De strategische controleposten op de afdeling zijn weer neutraal en klaar om veroverd te worden.</li>
        </ul>
    </div>

    <div class="p-5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-800/60 space-y-3">
        <h3 class="text-base font-black text-emerald-900 dark:text-emerald-300 flex items-center gap-2">
            <i class="fa-solid fa-shield-halved text-emerald-600"></i>
            <span>Wat heb je 100% Veilig Behouden?</span>
        </h3>
        <ul class="text-xs text-emerald-800 dark:text-emerald-200 space-y-1.5 list-disc pl-5">
            <li>Al jouw zuurverdiende <strong>Gouden Sterren</strong> en bestaande <strong>VIP Punten</strong> blijven volledig op jouw account staan.</li>
            <li>Jouw spelersaccount, gebruikersnaam, vriendenlijst en instellingen blijven behouden.</li>
            <li>Alle opgerichte bendes blijven bestaan inclusief de huidige leden en <strong>gekochte bende chat functies</strong>.</li>
        </ul>
    </div>

    <div class="p-5 rounded-2xl bg-purple-50 dark:bg-purple-950/40 border-2 border-purple-300 dark:border-purple-800 space-y-4">
        <div class="flex items-center gap-2 text-purple-700 dark:text-purple-300 text-xs font-black uppercase tracking-wider">
            <i class="fa-solid fa-crown text-amber-500"></i>
            <span>Vernieuwde VIP Shop &amp; Gouden Sterren Kiosk</span>
        </div>
        <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white">
            Reclamevrij Spelen, Exclusieve Week VIP &amp; Sterren Voordelen
        </h3>
        <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">
            We hebben de hele winkel en VIP lounge vernieuwd! Je hoeft nooit meer verplicht advertenties te bekijken om wachttijden over te slaan. Voortaan omzeil je cooldowns direct en stijlvol met Gouden Sterren.
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
            <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-purple-200 dark:border-purple-800">
                <div class="font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                    <i class="fa-solid fa-gem text-amber-500"></i>
                    <span>1 Week VIP Status (500 Sterren)</span>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                    Scoor 7 dagen lang VIP privileges, verhoogde opslag, snellere energierate en de gouden kroon status zonder maandelijks abonnement!
                </p>
            </div>
            <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-purple-200 dark:border-purple-800">
                <div class="font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                    <i class="fa-solid fa-bolt text-amber-500"></i>
                    <span>Directe Cooldown Bypass</span>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                    Ontsnap direct uit de isoleercel, verlaat het ziekenhuis of omzeil misdaadwachttijden direct met Gouden Sterren.
                </p>
            </div>
        </div>
    </div>

    <div class="p-5 sm:p-6 rounded-2xl bg-amber-50/80 dark:bg-amber-950/40 border-2 border-amber-400 dark:border-amber-600/80 space-y-4">
        <div class="flex items-center gap-2 text-amber-800 dark:text-amber-300 text-xs font-black uppercase tracking-wider">
            <i class="fa-solid fa-circle-exclamation text-amber-600"></i>
            <span>Belangrijke Donatie &amp; Buy Me a Coffee Instructie</span>
        </div>
        <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white">
            Hoe voeg je jouw naam toe bij Buy Me a Coffee?
        </h3>
        <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">
            Wil je de server steunen en direct extra Sterren, Koffers of VIP ontvangen? Volg deze stappen bij het afrekenen zodat ons geautomatiseerde systeem jouw beloningen binnen 60 seconden kan bijschrijven:
        </p>
        <div class="space-y-2.5 text-xs">
            <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-amber-300 dark:border-amber-700 flex items-start gap-3">
                <span class="w-6 h-6 rounded-full bg-amber-500 text-purple-950 flex items-center justify-center font-black shrink-0 text-xs">1</span>
                <div>
                    <strong class="text-slate-900 dark:text-white">Kopieer jouw Spelersnaam of Koppelcode</strong>
                    <p class="text-slate-600 dark:text-slate-400 mt-0.5">In de winkel vind je handige 1-klik kopieerknoppen voor zowel jouw exacte spelersnaam als jouw koppelcode (zoals TBS-1).</p>
                </div>
            </div>
            <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-amber-300 dark:border-amber-700 flex items-start gap-3">
                <span class="w-6 h-6 rounded-full bg-amber-500 text-purple-950 flex items-center justify-center font-black shrink-0 text-xs">2</span>
                <div>
                    <strong class="text-slate-900 dark:text-white">Plak in het veld &apos;Name or say something nice&apos;</strong>
                    <p class="text-slate-600 dark:text-slate-400 mt-0.5">Wanneer je het donatiescherm opent op Buy Me a Coffee, plak je jouw gekopieerde spelersnaam of code in het vakje <em>Name or say something nice</em>.</p>
                </div>
            </div>
            <div class="p-3 bg-rose-50 dark:bg-rose-950/50 rounded-xl border border-rose-300 dark:border-rose-800 flex items-start gap-3 text-rose-800 dark:text-rose-200">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 text-base mt-0.5 shrink-0"></i>
                <div>
                    <strong>Laat dit veld NOOIT leeg of op &apos;Anoniem&apos; staan!</strong>
                    <p class="text-[11px] mt-0.5">Als het veld leeg blijft kan ons systeem jouw account niet automatisch herkennen. Vul daarom altijd jouw naam of koppelcode in.</p>
                </div>
            </div>
            <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-amber-300 dark:border-amber-700 flex items-start gap-3">
                <span class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center font-black shrink-0 text-xs">✓</span>
                <div>
                    <strong class="text-slate-900 dark:text-white">Directe Automatische Bijschrijving</strong>
                    <p class="text-slate-600 dark:text-slate-400 mt-0.5">Reken veilig af via iDEAL of creditcard. Binnen een minuut ontvang je automatisch jouw Sterren, Koffers en badges in het spel!</p>
                </div>
            </div>
        </div>
    </div>

    <div class="p-5 rounded-2xl bg-gradient-to-r from-amber-500/20 via-purple-500/20 to-indigo-500/20 border-2 border-amber-400 dark:border-amber-500 shadow-md space-y-3">
        <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-gift text-amber-500"></i>
            <span>3 Gratis Seizoen 1 Kluiscodes voor een Vliegende Start!</span>
        </h3>
        <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">
            Ga direct via het hoofdmenu naar de <strong>Kluis Kraak</strong> en wissel de volgende drie geheime kluiscodes in voor gratis contanten, kluiskoffers en items:
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-center font-mono font-black text-xs">
            <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-amber-400 text-purple-700 dark:text-purple-300 shadow-2xs">
                SEIZOEN1
            </div>
            <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-amber-400 text-purple-700 dark:text-purple-300 shadow-2xs">
                HERSTART2026
            </div>
            <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-amber-400 text-purple-700 dark:text-purple-300 shadow-2xs">
                TBSKLINIEK1
            </div>
        </div>
    </div>

    <div class="p-4 rounded-xl bg-slate-100 dark:bg-slate-800 text-center text-xs text-slate-600 dark:text-slate-300">
        Heel veel succes en plezier in Seizoen 1. Strijd met je bende, voltooi je dagtaken en heers over FPC De Balk! 👑
    </div>
</div>';

        $seasonArticle = NewsArticle::updateOrCreate(
            ['slug' => 'seizoen-1-officieel-gestart'],
            [
                'character_id' => $adminCharId,
                'title' => '🚨 SEIZOEN 1 OFFICIEEL GESTART: Frisse Start, Eerlijke Bende Strijd, Vernieuwde VIP Shop & 3 Starter Kluiscodes!',
                'slug' => 'seizoen-1-officieel-gestart',
                'emoji' => '🏆',
                'content' => $articleContent,
                'is_pinned' => true,
                'comments_enabled' => true,
                'is_published' => true,
                'published_at' => now(),
            ]
        );

        $this->info("Nieuwsartikel succesvol bijgewerkt (ID: {$seasonArticle->id}).");

        $this->info('=== 5. BROADCAST MELDING NAAR ALLE SPELERS ===');
        $allCharacters = Character::all();
        $notificationTitle = '🏆 Seizoen 1 is Officieel Begonnen!';
        $notificationMessage = 'Welkom bij de officiële start van Seizoen 1 in TBS Kliniek! Iedereen begint met een schone lei, gelijke kansen en een frisse economie. Claim je starterscodes SEIZOEN1, HERSTART2026 en TBSKLINIEK1 in de kluis, voltooi je rookie overlevingstaken en strijd 3 maanden lang om de seizoensbokaal!';

        $count = 0;
        foreach ($allCharacters as $char) {
            GameNotification::create([
                'character_id' => $char->id,
                'type' => 'reward',
                'icon' => 'fa-flag-checkered',
                'title' => $notificationTitle,
                'message' => $notificationMessage,
                'is_read' => false,
            ]);
            $count++;
        }
        $this->info("Melding verstuurd naar {$count} spelers.");

        $this->info('=== VOLTOOID ===');

        return 0;
    }
}
