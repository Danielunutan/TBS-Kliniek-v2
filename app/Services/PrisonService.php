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
        if ($character->hasPerk('goede_advocaat')) {
            $seconds = (int) max(15, round($seconds * 0.75));
        }

        $character->update([
            'is_in_prison' => true,
            'prison_until' => now()->addSeconds($seconds),
            'prison_reason' => $reason,
            'prison_sentences' => $character->prison_sentences + 1,
            'prison_escape_attempted' => false,
            'ad_prison_progress' => 0,
        ]);

        $timeLabel = $seconds >= 60 ? (round($seconds / 60, 1).' min ('.$seconds.' sec)') : ($seconds.' sec');

        GameNotification::create([
            'character_id' => $character->id,
            'type' => 'prison',
            'icon' => 'fa-lock',
            'title' => 'Gepakt door de beveiliging!',
            'message' => "Je bent opgesloten in de isoleercel voor {$timeLabel}. Reden: {$reason}. Bekijk een korte advertentie om direct vrij te komen!",
        ]);

        GameMessage::create([
            'recipient_id' => $character->id,
            'sender_name' => 'Hoofdbeveiliging & Cipiers',
            'subject' => '🚨 Arrestatierapport: Isoleercel Straf',
            'content' => "Patiënt {$character->name},\n\nJe bent zojuist overmeesterd door het beveiligingsteam en overgebracht naar de gesloten isoleercel voor {$timeLabel}.\n\nOfficiële reden:\n\"{$reason}\"\n\nIn de gevangenis kun je direct een korte sponsoradvertentie bekijken om per direct vrijgelaten te worden, een ontsnappingspoging wagen of je straftijd uitzitten.",
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
        if (! $character->isImprisoned()) {
            return [
                'success' => false,
                'message' => 'Je zit momenteel niet in de isoleercel!',
            ];
        }

        if ($character->isComatose()) {
            return [
                'success' => false,
                'message' => 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.',
            ];
        }

        if ($character->prison_escape_attempted) {
            return [
                'success' => false,
                'message' => 'Je hebt je ontsnappingspoging voor deze straftijd al verbruikt! Bekijk een advertentie om direct vrijgelaten te worden.',
            ];
        }

        $escapeCost = 20;
        if (! $energyService->consume($character, $escapeCost)) {
            return [
                'success' => false,
                'message' => "Je hebt minimaal {$escapeCost} energie nodig voor een ontsnappingspoging.",
            ];
        }

        // Escape chance: Base 25% + (effective intelligence + stamina) / 10, capped at 65%
        $bonus = ($character->getEffectiveIntelligence() + $character->getEffectiveStamina()) / 10;
        $chance = min(65, (int) round(25 + $bonus));

        $roll = random_int(1, 100);

        if ($roll <= $chance) {
            $newStamina = (int) $character->stamina;
            if (random_int(1, 100) <= 25) {
                $newStamina = min($character->getMaxStatForLevel(), $newStamina + 1);
            }

            $character->addRespect(15);
            $character->update([
                'is_in_prison' => false,
                'prison_until' => null,
                'prison_reason' => null,
                'ad_prison_progress' => 0,
                'prison_escape_attempted' => false,
                'prison_escapes' => $character->prison_escapes + 1,
                'stamina' => $newStamina,
            ]);
            $character->refresh();

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

        // Failed escape -> adds 90 extra seconds of sentence
        $currentUntil = ($character->prison_until && now()->lt($character->prison_until))
            ? $character->prison_until
            : now();
        $newUntil = $currentUntil->copy()->addSeconds(90);

        $character->update([
            'is_in_prison' => true,
            'prison_until' => $newUntil,
            'prison_escape_attempted' => true,
            'respect' => max(0, $character->respect - 1),
        ]);
        $character->refresh();

        return [
            'success' => false,
            'message' => 'Mislukt! Bewaker Barry greep je bij je lurven bij het luchtrooster. Je straftijd is met 90 seconden verlengd en je hebt je ontsnappingspoging verbruikt! Bekijk een advertentie om alsnog direct vrij te komen.',
        ];
    }
}
