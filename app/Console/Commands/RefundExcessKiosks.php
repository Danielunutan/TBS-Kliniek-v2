<?php

namespace App\Console\Commands;

use App\Models\GameMessage;
use App\Models\Gang;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefundExcessKiosks extends Command
{
    protected $signature = 'gang:refund-excess-kiosks {--max=1000} {--price=50000}';

    protected $description = 'Refund 100% purchase cost of all gang smokkelkiosken above maximum limit into the gang treasury';

    public function handle(): int
    {
        $max = (int) $this->option('max');
        $unitPrice = (int) $this->option('price');

        $gangs = Gang::where('bling_shops', '>', $max)->get();

        if ($gangs->isEmpty()) {
            $this->info("Geen bendes gevonden met meer dan {$max} Smokkelkiosken.");

            return Command::SUCCESS;
        }

        $this->info("Gevonden bendes met overtollige kiosken: {$gangs->count()}");

        foreach ($gangs as $gang) {
            DB::transaction(function () use ($gang, $max, $unitPrice) {
                /** @var Gang $lockedGang */
                $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->firstOrFail();
                $originalCount = (int) $lockedGang->bling_shops;
                $excess = $originalCount - $max;

                if ($excess <= 0) {
                    return;
                }

                $refundAmount = $excess * $unitPrice;
                $lockedGang->bling_shops = $max;
                $lockedGang->treasury += $refundAmount;
                $lockedGang->save();

                $formattedExcess = number_format($excess, 0, ',', '.');
                $formattedRefund = number_format($refundAmount, 0, ',', '.');
                $formattedTreasury = number_format($lockedGang->treasury, 0, ',', '.');

                $this->info("Bende [{$lockedGang->tag}] {$lockedGang->name}: {$originalCount} -> {$max} kiosken. Teruggestort: €{$formattedRefund}. Nieuwe kas: €{$formattedTreasury}");

                if ($lockedGang->leader) {
                    GameMessage::create([
                        'recipient_id' => $lockedGang->leader->id,
                        'sender_id' => null,
                        'sender_name' => 'TBS Kliniek Directie',
                        'subject' => '💰 100% Restitutie: Smokkelkiosken Krediet Teruggestort!',
                        'content' => "Beste {$lockedGang->leader->name},\n\nIn verband met de nieuwe eerlijke bende-economie geldt er vanaf heden een officiële bende-limiet van maximaal {$max} Smokkelkiosken (met een vaste opbrengst van €50/uur per kiosk).\n\nVoor alle {$formattedExcess} kiosken die jullie boven deze limiet bezaten, is 100% van de aanschafwaarde (€50.000 per stuk) zojuist direct teruggestort in jullie gezamenlijke bendekas!\n\nEr is zojuist €{$formattedRefund} bijgeschreven in de bendekas. Jullie actuele kassaldo bedraagt nu €{$formattedTreasury}.\n\nDit kapitaal kunnen jullie direct inzetten voor de 10 nieuwe syndicate panden, productieruns en uitbreiding van de Bende Garage!\n\nMet vriendelijke groet,\nTBS Kliniek Leiding",
                        'type' => 'system',
                    ]);
                }
            });
        }

        $this->info('Alle overtollige kiosken zijn succesvol verwerkt en gerestitueerd!');

        return Command::SUCCESS;
    }
}

