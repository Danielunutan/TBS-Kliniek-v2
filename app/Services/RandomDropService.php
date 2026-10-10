<?php

namespace App\Services;

use App\Models\Character;
use App\Models\GameMessage;
use App\Models\VaultCode;

class RandomDropService
{
    /**
     * Roll for random drop rewards across activities (Koffers, Kluiscodes).
     */
    public static function roll(Character $character): ?string
    {
        $roll = rand(1, 100);

        // 12% chance to drop an unopened Koffer
        if ($roll <= 12) {
            $character->unopened_bags = ($character->unopened_bags ?? 0) + 1;
            $character->save();

            return '💼 BONUS BUIT: Je vond een mysterieuze smokkelkoffer tussen de spullen! Open hem bij Koffers.';
        }

        // 6% chance to drop a secret MedicijnKluis code via GameMessage
        if ($roll >= 94) {
            $uniqueCode = 'DROP-'.strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 6));

            VaultCode::create([
                'code' => $uniqueCode,
                'reward_cash' => rand(1500, 4000),
                'reward_stars' => 1,
                'reward_tokens' => 2,
                'character_id' => $character->id,
                'is_redeemed' => false,
            ]);

            GameMessage::create([
                'recipient_id' => $character->id,
                'sender_name' => 'Kliniek Scharrelbode',
                'subject' => '🔐 Mysterieus briefje met kluiscode gevonden!',
                'content' => "Patiënt {$character->name},\n\nTijdens je activiteiten vond je een weggestopt briefje met een geheime MedicijnKluis voucher code:\n\n👉 **{$uniqueCode}**\n\nWissel hem in op de MedicijnKluis voor wit geld op je bankrekening!",
                'type' => 'reward',
            ]);

            return "🔐 GEHEIM GEVONDEN: Een kluiscode voucher ({$uniqueCode}) is direct naar je postvak gestuurd!";
        }

        return null;
    }
}
