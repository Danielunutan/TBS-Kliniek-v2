<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Revert the unauthorized theft of €495.028 from Bella while under isolation protection,
     * deduct the stolen funds from OOHPA Arjan, and restore Bella's 24h isolation protection.
     */
    public function up(): void
    {
        if (! Schema::hasTable('characters')) {
            return;
        }

        $stolenAmount = 495028;

        // 1. Locate Bella and OOHPA Arjan
        $bella = DB::table('characters')
            ->where(function ($query) {
                $query->where('name', 'LIKE', '%Bella%')
                    ->orWhere('name', 'LIKE', 'Bella%');
            })
            ->first();

        $arjan = DB::table('characters')
            ->where(function ($query) {
                $query->where('name', 'LIKE', '%Arjan%')
                    ->orWhere('name', 'LIKE', '%OOHPA%');
            })
            ->first();

        // 2. Compensate Bella
        if ($bella) {
            // Check if there was an existing message to extract exact stolen amount if different
            if (Schema::hasTable('game_messages')) {
                $theftMsg = DB::table('game_messages')
                    ->where('recipient_id', $bella->id)
                    ->where('type', 'theft')
                    ->where('content', 'LIKE', '%495%')
                    ->latest()
                    ->first();

                if ($theftMsg && preg_match('/€([\d\.]+)/', $theftMsg->content, $matches)) {
                    $parsed = (int) str_replace('.', '', $matches[1]);
                    if ($parsed > 0) {
                        $stolenAmount = $parsed;
                    }
                }
            }

            $currentCash = (int) ($bella->cash ?? 0);
            $newCash = $currentCash + $stolenAmount;

            $currentProt = $bella->protection_until ? Carbon::parse($bella->protection_until) : null;
            $newProt = ($currentProt && $currentProt->isFuture())
                ? $currentProt->copy()->addHours(24)
                : now()->addHours(24);

            $newFightsLost = max(0, (int) ($bella->fights_lost ?? 0) - 1);

            DB::table('characters')
                ->where('id', $bella->id)
                ->update([
                    'cash' => $newCash,
                    'protection_until' => $newProt,
                    'fights_lost' => $newFightsLost,
                    'updated_at' => now(),
                ]);

            if (Schema::hasTable('bank_transactions')) {
                DB::table('bank_transactions')->insert([
                    'character_id' => $bella->id,
                    'type' => 'deposit',
                    'source' => 'cash',
                    'amount' => $stolenAmount,
                    'balance_after' => $newCash,
                    'description' => 'Compensatie directie: onterechte beroving op de luchtplaats tijdens isolatie',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (Schema::hasTable('game_notifications')) {
                DB::table('game_notifications')->insert([
                    'character_id' => $bella->id,
                    'type' => 'shield',
                    'icon' => 'fa-shield-halved',
                    'title' => '🛡️ Buit Teruggestort & 24u Isolatie Hersteld!',
                    'message' => 'De €'.number_format($stolenAmount, 0, ',', '.').' die op de Luchtplaats van je was ontvreemd is teruggestort op je contante rekening en je 24u Vrijwillige Isolatie is weer actief!',
                    'is_read' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (Schema::hasTable('game_messages')) {
                DB::table('game_messages')->insert([
                    'recipient_id' => $bella->id,
                    'sender_name' => 'Directie TBS Kliniek',
                    'subject' => '⚖️ Uitspraak Directie: Beroving Ongeldig Verklaard',
                    'content' => "Beste {$bella->name},\n\nDe kliniekdirectie heeft geconstateerd dat de beroving van €".number_format($stolenAmount, 0, ',', '.')." door OOHPA Arjan heeft plaatsgevonden terwijl je onder actieve isolatiebescherming hoorde te vallen.\n\nDit incident is zojuist volledig hersteld:\n- €".number_format($stolenAmount, 0, ',', '.')." is teruggestort op je contante rekening.\n- Je 24 uur Vrijwillige Isolatie is opnieuw geactiveerd tot ".$newProt->format('d-m-Y H:i')." uur.\n\nOnze excuses voor het ongemak!\n\nMet vriendelijke groet,\nDirecteur TBS Kliniek",
                    'type' => 'system',
                    'is_read' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 3. Deduct from OOHPA Arjan
        if ($arjan) {
            $arjanCash = (int) ($arjan->cash ?? 0);
            $arjanBank = (int) ($arjan->bank ?? 0);

            $deductFromCash = min($arjanCash, $stolenAmount);
            $remaining = $stolenAmount - $deductFromCash;
            $deductFromBank = min($arjanBank, $remaining);

            $newArjanCash = max(0, $arjanCash - $deductFromCash);
            $newArjanBank = max(0, $arjanBank - $deductFromBank);
            $newThefts = max(0, (int) ($arjan->thefts_succeeded ?? 0) - 1);
            $newFightsWon = max(0, (int) ($arjan->fights_won ?? 0) - 1);

            DB::table('characters')
                ->where('id', $arjan->id)
                ->update([
                    'cash' => $newArjanCash,
                    'bank' => $newArjanBank,
                    'thefts_succeeded' => $newThefts,
                    'fights_won' => $newFightsWon,
                    'updated_at' => now(),
                ]);

            if (Schema::hasTable('bank_transactions')) {
                DB::table('bank_transactions')->insert([
                    'character_id' => $arjan->id,
                    'type' => 'withdrawal',
                    'source' => $deductFromCash >= $stolenAmount ? 'cash' : 'bank',
                    'amount' => $stolenAmount,
                    'balance_after' => $newArjanCash,
                    'description' => 'Correctie directie: onreglementaire buit beroving Bella tijdens isolatie',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (Schema::hasTable('game_notifications')) {
                DB::table('game_notifications')->insert([
                    'character_id' => $arjan->id,
                    'type' => 'theft',
                    'icon' => 'fa-scale-unbalanced',
                    'title' => '⚠️ Correctie Beroving Luchtplaats',
                    'message' => 'De beroving van €'.number_format($stolenAmount, 0, ',', '.').' op Bella is gecorrigeerd en teruggestort omdat het doelwit onder actieve isolatiebescherming viel.',
                    'is_read' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (Schema::hasTable('game_messages')) {
                DB::table('game_messages')->insert([
                    'recipient_id' => $arjan->id,
                    'sender_name' => 'Directie TBS Kliniek',
                    'subject' => '⚖️ Mededeling Directie: Correctie Buit Luchtplaats',
                    'content' => "Beste {$arjan->name},\n\nDe kliniekdirectie heeft vastgesteld dat je beroving op Bella (€".number_format($stolenAmount, 0, ',', '.').") heeft plaatsgevonden door een systeemfout in de isolatiebeveiliging van haar celblok. Omdat zij onder geldige isolatiebeveiliging viel, was deze aanval niet reglementair.\n\nDe buit van €".number_format($stolenAmount, 0, ',', '.')." is daarom gecorrigeerd en teruggestort naar het slachtoffer.\n\nMet vriendelijke groet,\nDirecteur TBS Kliniek",
                    'type' => 'system',
                    'is_read' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversal of disciplinary correction is non-reversible.
    }
};
