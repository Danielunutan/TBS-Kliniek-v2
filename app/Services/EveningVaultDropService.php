<?php

namespace App\Services;

use App\Models\Character;
use App\Models\EveningVaultDrop;
use App\Models\GameMessage;
use App\Models\VaultCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EveningVaultDropService
{
    /**
     * Check if the current time is within the 22:00 - 22:30 evening drop window.
     */
    public function isDropWindowActive(?Carbon $now = null): bool
    {
        return Character::isLoginBonusWindowActive($now);
    }

    /**
     * Check if character has already received today's evening drop.
     */
    public function hasReceivedToday(Character $character, ?string $date = null): bool
    {
        $tz = config('app.timezone', 'Europe/Amsterdam');
        $date = $date ?? Carbon::now($tz)->toDateString();

        return EveningVaultDrop::where('character_id', $character->id)
            ->where('drop_date', $date)
            ->exists();
    }

    /**
     * Award 3 free vault codes to character.
     *
     * @return array<int, VaultCode>
     */
    public function award(Character $character, ?string $date = null): array
    {
        $tz = config('app.timezone', 'Europe/Amsterdam');
        $date = $date ?? Carbon::now($tz)->toDateString();

        // Lock the character row so concurrent requests during the 22:00-22:30 window cannot award duplicate vault code drops.
        return DB::transaction(function () use ($character, $date): array {
            /** @var Character|null $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();
            if (! $locked || $this->hasReceivedToday($locked, $date)) {
                return [];
            }

            // Record drop to prevent duplicates
            EveningVaultDrop::create([
                'character_id' => $locked->id,
                'drop_date' => $date,
            ]);

            $codes = [];
            for ($i = 1; $i <= 3; $i++) {
                $uniqueCode = VaultCode::generateUniqueCode('AVOND');
                $codes[] = VaultCode::create([
                    'code' => $uniqueCode,
                    'reward_cash' => rand(2500, 5000),
                    'reward_stars' => ($i === 1 && rand(1, 100) <= 25) ? 1 : 0,
                    'reward_tokens' => 2,
                    'character_id' => $locked->id,
                    'is_redeemed' => false,
                ]);
            }

            GameMessage::create([
                'recipient_id' => $locked->id,
                'sender_name' => 'Directie TBS Kluisbeheer',
                'subject' => '🎁 22:00 Avondronde Inlog Bonus: 3 Gratis Kluiscodes Ontvangen!',
                'content' => "Patiënt {$locked->name},\n\nOmdat je vanavond tijdens de inlog bonus ronde tussen 22:00 en 22:30 aanwezig en ingelogd bent in de TBS Kliniek, heeft de directie 3 gratis geheime MedicijnKluis vouchers toegekend!\n\nJouw 3 persoonlijke kluiscodes:\n• 🔑 **{$codes[0]->code}**\n• 🔑 **{$codes[1]->code}**\n• 🔑 **{$codes[2]->code}**\n\nDeze codes staan direct in jouw dossier. Wissel ze in bij de MedicijnKluis voor contant geld, sterren en kluismunten, of verkoop ze voor winst op de Marktplaats!\n\nVeel succes vanavond in de kliniek!",
                'type' => 'reward',
            ]);

            return $codes;
        });
    }

    /**
     * Check if drop window is active, and if so, award 3 codes if not yet claimed today.
     */
    public function checkAndAward(Character $character): bool
    {
        if (! $this->isDropWindowActive()) {
            return false;
        }

        if ($this->hasReceivedToday($character)) {
            return false;
        }

        $codes = $this->award($character);

        return ! empty($codes);
    }

    /**
     * Award to all characters currently marked online.
     */
    public function awardAllOnline(): int
    {
        $tz = config('app.timezone', 'Europe/Amsterdam');
        $date = Carbon::now($tz)->toDateString();

        $onlineThreshold = Carbon::now($tz)->subMinutes(15);
        $onlineCharacters = Character::where('last_active_at', '>=', $onlineThreshold)->get();

        $count = 0;
        foreach ($onlineCharacters as $character) {
            if (! $this->hasReceivedToday($character, $date)) {
                $this->award($character, $date);
                $count++;
            }
        }

        return $count;
    }
}
