<?php

namespace App\Services;

use App\Models\Character;
use App\Models\ControlPoint;
use App\Models\ControlPointLog;
use App\Models\GameMessage;
use App\Models\GameNotification;
use Illuminate\Support\Facades\DB;

class ControlPointService
{
    public function __construct(
        protected EnergyService $energyService,
    ) {}

    /**
     * Capture / take over a control point.
     *
     * @return array{success: bool, message: string, reward_cash?: int, reward_stars?: int, duration_minutes?: int}
     */
    public function capture(Character $character, ControlPoint $point): array
    {
        if ($character->isImprisoned()) {
            return [
                'success' => false,
                'message' => 'Vanuit de isoleercel kun je geen controlepunten in de kliniek overnemen!',
            ];
        }

        if ($point->current_character_id === $character->id) {
            $mins = $point->getHeldMinutes();

            return [
                'success' => false,
                'message' => "Jij bezit dit controlepunt ('{$point->name}') al! Je hebt het punt al {$mins} minuten stevig in handen.",
            ];
        }

        if (! $this->energyService->consume($character, $point->energy_cost)) {
            return [
                'success' => false,
                'message' => "Je hebt minimaal {$point->energy_cost} Energie nodig om een controlepunt te veroveren!",
            ];
        }

        $now = now();
        $payoutData = null;

        DB::transaction(function () use ($character, $point, $now, &$payoutData) {
            $lockedPoint = ControlPoint::where('id', $point->id)->lockForUpdate()->first();
            $prevHolderId = $lockedPoint->current_character_id;

            if ($prevHolderId && $lockedPoint->captured_at) {
                $heldMinutes = max(1, (int) $lockedPoint->captured_at->diffInMinutes($now));
                $rewardCash = $heldMinutes * $lockedPoint->cash_per_minute;
                $rewardStars = $heldMinutes * $lockedPoint->stars_per_minute;

                $prevHolder = Character::where('id', $prevHolderId)->lockForUpdate()->first();
                if ($prevHolder) {
                    $prevHolder->cash = ($prevHolder->cash ?? 0) + $rewardCash;
                    $prevHolder->stars = ($prevHolder->stars ?? 0) + $rewardStars;
                    $prevHolder->save();

                    // Log history
                    ControlPointLog::create([
                        'control_point_id' => $lockedPoint->id,
                        'character_id' => $prevHolder->id,
                        'gang_id' => $prevHolder->gang_id,
                        'held_from' => $lockedPoint->captured_at,
                        'held_until' => $now,
                        'duration_minutes' => $heldMinutes,
                        'reward_cash' => $rewardCash,
                        'reward_stars' => $rewardStars,
                        'overtaken_by_character_id' => $character->id,
                    ]);

                    // Send notification
                    GameNotification::create([
                        'character_id' => $prevHolder->id,
                        'type' => 'control_point',
                        'icon' => 'fa-flag',
                        'title' => 'Controlepunt Overgenomen!',
                        'message' => "Patiënt {$character->name} heeft controlepunt '{$lockedPoint->name}' overgenomen! Je hebt het punt {$heldMinutes} minuten behouden en ontvangt direct €".number_format($rewardCash, 0, ',', '.')." contant en +{$rewardStars} Sterren ⭐!",
                    ]);

                    // Send official in-game mail
                    GameMessage::create([
                        'recipient_id' => $prevHolder->id,
                        'sender_id' => $character->id,
                        'sender_name' => 'Kliniek Controlepunt Beheer',
                        'subject' => "🚩 Uitbetaling Controlepunt: {$lockedPoint->name}",
                        'content' => "Beste {$prevHolder->name},\n\nPatiënt {$character->name} heeft zojuist controlepunt '{$lockedPoint->name}' ({$lockedPoint->location_name}) van je overgenomen.\n\nTotale bezettingstijd: {$heldMinutes} minuten.\nUitbetaalde buit:\n• Contant geld: €".number_format($rewardCash, 0, ',', '.')."\n• Exclusieve Sterren: +{$rewardStars} ⭐\n\nHet geld en de sterren zijn direct bijgeschreven op je saldo!",
                        'type' => 'system',
                    ]);

                    $payoutData = [
                        'prev_name' => $prevHolder->name,
                        'minutes' => $heldMinutes,
                        'cash' => $rewardCash,
                        'stars' => $rewardStars,
                    ];
                }
            }

            // Assign new holder
            $lockedPoint->current_character_id = $character->id;
            $lockedPoint->current_gang_id = $character->gang_id;
            $lockedPoint->captured_at = $now;
            $lockedPoint->total_captures++;
            $lockedPoint->save();
        });

        // Small respect bonus for taking a point
        $character->addRespect(1);
        $character->save();

        $point->refresh();

        $msg = $payoutData
            ? "🚩 Controlepunt '{$point->name}' veroverd van {$payoutData['prev_name']}! Je bouwt nu elke minuut +€{$point->cash_per_minute} en +{$point->stars_per_minute}⭐ op!"
            : "🚩 Controlepunt '{$point->name}' succesvol ingenomen! Je bouwt nu elke minuut +€{$point->cash_per_minute} en +{$point->stars_per_minute}⭐ op totdat iemand anders het overneemt!";

        return [
            'success' => true,
            'message' => $msg,
        ];
    }
}
