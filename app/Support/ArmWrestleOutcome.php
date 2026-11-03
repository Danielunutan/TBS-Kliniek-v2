<?php

namespace App\Support;

/**
 * Decides the result of an arm wrestling match against an NPC on the server.
 *
 * The client cannot choose this result. The win chance follows the player strength
 * against the NPC strength, and a server-side random roll decides the match.
 */
class ArmWrestleOutcome
{
    /**
     * Return true when the player wins the match.
     */
    public function isPlayerWinner(int $playerStrength, int $npcStrength): bool
    {
        $safePlayerStrength = max(1, $playerStrength);
        $safeNpcStrength = max(1, $npcStrength);
        $winChancePercent = min(99, max(1, (int) round(100 * $safePlayerStrength / ($safePlayerStrength + $safeNpcStrength))));

        return random_int(1, 100) <= $winChancePercent;
    }
}
