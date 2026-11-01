<?php

namespace App\Services;

use App\Models\Character;
use App\Models\GameMessage;
use App\Models\GameNotification;
use Illuminate\Support\Facades\DB;

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

        $perks = is_array($character->perks) ? $character->perks : [];
        unset($perks['prison_action_cooldown_until']);

        $character->update([
            'is_in_prison' => true,
            'prison_until' => now()->addSeconds($seconds),
            'prison_reason' => $reason,
            'prison_sentences' => $character->prison_sentences + 1,
            'prison_escape_attempted' => false,
            'ad_prison_progress' => 0,
            'perks' => $perks,
        ]);

        $timeLabel = $seconds >= 60 ? (round($seconds / 60, 1).' min ('.$seconds.' sec)') : ($seconds.' sec');

        GameNotification::create([
            'character_id' => $character->id,
            'type' => 'prison',
            'icon' => 'fa-lock',
            'title' => 'Ingesloten op de Isolatievleugel!',
            'message' => "De interne interventieploeg heeft je voor {$timeLabel} in een zwaarbeveiligde cel geplaatst. Dossiernotitie: {$reason}. Verkort je straftijd via de stoomwasserij, koop de nachtcipier om of bekijk een sponsorvideo.",
        ]);

        GameMessage::create([
            'recipient_id' => $character->id,
            'sender_name' => 'Interne Interventie & Celblokbeheer',
            'subject' => '🚨 Arrestatierapport: Isoleercel Straf',
            'content' => "Patiënt {$character->name},\n\nHet tactisch interventieteam heeft je zojuist ingerekend en overgeplaatst naar het gesloten celblok voor een duur van {$timeLabel}.\n\nGeregistreerde overtreding:\n\"{$reason}\"\n\nVanuit je cel kun je dienst draaien in de stoomwasserij, een nachtcipier omkopen om de klok terug te draaien, een ontsnappingspoging wagen of via een sponsoruitzending directe vrijlating afdwingen.",
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
        return DB::transaction(function () use ($character, $energyService): array {
            /** @var Character|null $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();
            if (! $locked) {
                return [
                    'success' => false,
                    'message' => 'Patiëntdossier kon niet worden geladen.',
                ];
            }

            if (! $locked->isImprisoned()) {
                return [
                    'success' => false,
                    'message' => 'Je zit momenteel niet in de isoleercel!',
                ];
            }

            if ($locked->isComatose()) {
                return [
                    'success' => false,
                    'message' => 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.',
                ];
            }

            if ($locked->prison_escape_attempted) {
                return [
                    'success' => false,
                    'message' => 'Je hebt je ontsnappingspoging voor deze straftijd al verbruikt! Bekijk een advertentie om direct vrijgelaten te worden.',
                ];
            }

            $escapeCost = 20;
            if (! $energyService->consume($locked, $escapeCost)) {
                return [
                    'success' => false,
                    'message' => "Onvoldoende uithoudingsvermogen: een uitbraak door de luchtschacht vereist {$escapeCost} energie.",
                ];
            }

            // Escape chance: Base 25% + (effective intelligence + stamina) / 10, capped at 65%
            $bonus = ($locked->getEffectiveIntelligence() + $locked->getEffectiveStamina()) / 10;
            $chance = min(65, (int) round(25 + $bonus));

            $roll = random_int(1, 100);

            if ($roll <= $chance) {
                $newStamina = (int) $locked->stamina;
                if (random_int(1, 100) <= 25) {
                    $newStamina = min($locked->getMaxStatForLevel(), $newStamina + 1);
                }

                $locked->addRespect(15);
                $locked->update([
                    'is_in_prison' => false,
                    'prison_until' => null,
                    'prison_reason' => null,
                    'ad_prison_progress' => 0,
                    'prison_escape_attempted' => false,
                    'prison_escapes' => $locked->prison_escapes + 1,
                    'stamina' => $newStamina,
                ]);
                $character->refresh();

                GameNotification::create([
                    'character_id' => $locked->id,
                    'type' => 'escape',
                    'icon' => 'fa-door-open',
                    'title' => 'Luchtschacht-Uitbraak Geslaagd!',
                    'message' => 'Je hebt het ventilatierooster losgewrikt en bent ongezien uit het celblok geglipt! Beloning: +15 Respect.',
                ]);

                return [
                    'success' => true,
                    'message' => 'Uitbraak geslaagd! Je hebt de elektronische vergrendeling kortgesloten en bent via de technische schacht ontkomen (+15 Respect).',
                ];
            }

            // Failed escape -> adds 90 extra seconds of sentence
            $currentUntil = ($locked->prison_until && now()->lt($locked->prison_until))
                ? $locked->prison_until
                : now();
            $newUntil = $currentUntil->copy()->addSeconds(90);

            $locked->update([
                'is_in_prison' => true,
                'prison_until' => $newUntil,
                'prison_escape_attempted' => true,
            ]);
            $character->refresh();

            return [
                'success' => false,
                'message' => 'Alarmfase Rood! Hoofdcipier Barry onderschepte je bij het ventilatiekanaal. Je celstraf is verzwaard met 90 seconden en je ontsnappingskans voor deze straf is verkeken.',
            ];
        });
    }
}
