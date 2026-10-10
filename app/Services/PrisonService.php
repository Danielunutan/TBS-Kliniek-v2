<?php

namespace App\Services;

use App\Models\Character;
use App\Models\GameMessage;
use App\Models\GameNotification;

class PrisonService
{
    /**
     * Send a character to prison for minutes.
     */
    public function jail(Character $character, int $minutes, string $reason): void
    {
        $this->jailSeconds($character, $minutes * 60, $reason);
    }

    /**
     * Send a character to prison for a specific duration in seconds.
     */
    public function jailSeconds(Character $character, int $seconds, string $reason): void
    {
        $character->update([
            'is_in_prison' => true,
            'prison_until' => now()->addSeconds($seconds),
            'prison_reason' => $reason,
            'prison_sentences' => $character->prison_sentences + 1,
        ]);

        $timeLabel = $seconds >= 60 ? (round($seconds / 60, 1).' min ('.$seconds.' sec)') : ($seconds.' sec');

        GameNotification::create([
            'character_id' => $character->id,
            'type' => 'prison',
            'icon' => 'fa-lock',
            'title' => 'Gepakt door de beveiliging!',
            'message' => "Je bent opgesloten in de isoleercel voor {$timeLabel}. Reden: {$reason}.",
        ]);

        GameMessage::create([
            'recipient_id' => $character->id,
            'sender_name' => 'Hoofdbeveiliging & Cipiers',
            'subject' => '🚨 Arrestatierapport: Isoleercel Straf',
            'content' => "Patiënt {$character->name},\n\nJe bent zojuist overmeesterd door het beveiligingsteam en overgebracht naar de gesloten isoleercel voor {$timeLabel}.\n\nOfficiële reden:\n\"{$reason}\"\n\nJe kunt je op elk moment direct vrijkopen met 2 Sterren ⭐ of je straftijd uitzitten.",
            'type' => 'jail',
        ]);
    }

    /**
     * Check if character is free or can be automatically released.
     */
    public function checkRelease(Character $character): bool
    {
        return ! $character->isImprisoned();
    }

    /**
     * Attempt to escape prison.
     *
     * @return array{success: bool, message: string}
     */
    public function attemptEscape(Character $character, EnergyService $energyService): array
    {
        if (! $character->is_in_prison) {
            return [
                'success' => false,
                'message' => 'Je zit helemaal niet in de gevangenis!',
            ];
        }

        $escapeCost = 25;
        if (! $energyService->consume($character, $escapeCost)) {
            return [
                'success' => false,
                'message' => "Je hebt minimaal {$escapeCost} energie nodig voor een ontsnappingspoging.",
            ];
        }

        // Escape chance: Base 25% + (intelligence + stamina) / 10, capped at 60%
        $bonus = ($character->intelligence + $character->stamina) / 10;
        $chance = min(60, (int) round(25 + $bonus));

        $roll = random_int(1, 100);

        if ($roll <= $chance) {
            $character->update([
                'is_in_prison' => false,
                'prison_until' => null,
                'prison_reason' => null,
                'prison_escapes' => $character->prison_escapes + 1,
                'respect' => $character->respect + 15,
            ]);

            GameNotification::create([
                'character_id' => $character->id,
                'type' => 'escape',
                'icon' => 'fa-door-open',
                'title' => 'Succesvol ontsnapt!',
                'message' => 'Je bent via het luchtkanaal ontsnapt uit de isoleercel! Je verdiende 15 respect.',
            ]);

            return [
                'success' => true,
                'message' => 'Geweldig! Je hebt de bewakers afgeleid en bent ontsnapt via de wasruimte. Je verdient 15 respect!',
            ];
        }

        // Failed escape -> adds 5 extra minutes of sentence!
        $currentUntil = $character->prison_until ?? now();
        $newUntil = $currentUntil->copy()->addMinutes(5);

        $character->update([
            'prison_until' => $newUntil,
            'respect' => max(0, $character->respect - 3),
        ]);

        return [
            'success' => false,
            'message' => 'Mislukt! Bewaker Barry greep je bij je lurven. Je straftijd is met 5 minuten verlengd en je verliest 3 respect!',
        ];
    }
}
