<?php

namespace App\Console\Commands;

use App\Models\Gang;
use App\Models\GangNpc;
use Illuminate\Console\Command;

class DebugNpcsCommand extends Command
{
    protected $signature = 'debug:npcs';
    protected $description = 'Debug gang NPCs';

    public function handle(): int
    {
        $tables = \Illuminate\Support\Facades\DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = database() AND (table_name LIKE '%npc%' OR table_name LIKE '%gang%')");
        $this->info("Tables found: " . json_encode(array_map(fn($t) => array_values((array)$t)[0], $tables)));

        $characters = \App\Models\Character::whereIn('name', ['WRDMarco', 'Arjan'])->get();
        foreach ($characters as $c) {
            $this->info("Character #{$c->id}: {$c->name}, gang_id={$c->gang_id}");
        }

        $this->info('Total GangNpc: ' . GangNpc::count());
        $wrd = \App\Models\Character::where('name', 'WRDMarco')->first();
        if ($wrd && $wrd->gang) {
            $g = $wrd->gang;
            GangNpc::purgeExpiredForGang($g);
            $g->load(['properties', 'npcs.assignedProperty', 'leader', 'storedVehicles']);
            $hiredNpcs = $g->npcs;
            $totalNpcCap = $g->total_npc_capacity;
            $availableNpcSlots = max(0, $totalNpcCap - $hiredNpcs->count());
            $this->info("WRDMarco Gang #{$g->id} [{$g->name}]:");
            $this->info("  hiredNpcs->count(): {$hiredNpcs->count()}");
            $this->info("  totalNpcCap: {$totalNpcCap}");
            $this->info("  availableNpcSlots: {$availableNpcSlots}");
            $this->info("  npc_defense_bonus_percent: {$g->npc_defense_bonus_percent}%");
        }
        foreach (Gang::all() as $g) {
            $this->info("Gang #{$g->id} [{$g->tag}] {$g->name}:");
            $this->line("  total_npc_capacity: {$g->total_npc_capacity}");
            $this->line("  npcs relationship count: " . $g->npcs()->count());
            $this->line("  loaded npcs count: " . $g->npcs->count());
            $this->line("  properties count: " . $g->properties()->count());
            $props = $g->properties()->get(['id', 'property_key', 'name', 'type', 'npc_capacity']);
            foreach ($props as $p) {
                if ($p->npc_capacity > 0) {
                    $this->line("    Property: {$p->property_key} ({$p->name}) type={$p->type} cap={$p->npc_capacity}");
                }
            }
            $allNpcRows = \Illuminate\Support\Facades\DB::table('gang_npcs')->where('gang_id', $g->id)->get();
            $this->line("  raw gang_npcs rows: " . $allNpcRows->count());
            foreach ($allNpcRows as $r) {
                $this->line("    raw NPC: id={$r->id}, key={$r->npc_key}, bonus_type={$r->bonus_type}, expires_at=" . ($r->expires_at ?? 'NULL'));
            }
        }
        return self::SUCCESS;
    }
}
