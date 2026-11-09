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
        $this->info('Total GangNpc: ' . GangNpc::count());
        foreach (Gang::all() as $g) {
            $this->info("Gang #{$g->id} [{$g->tag}] {$g->name}:");
            $this->line("  total_npc_capacity: {$g->total_npc_capacity}");
            $this->line("  npcs relationship count: " . $g->npcs()->count());
            $this->line("  loaded npcs count: " . $g->npcs->count());
            $this->line("  active npcs (future): " . $g->npcs()->where('expires_at', '>', now())->count());
            $this->line("  expired npcs: " . $g->npcs()->where('expires_at', '<=', now())->count());
            $this->line("  null expires_at: " . $g->npcs()->whereNull('expires_at')->count());
            foreach ($g->npcs as $n) {
                $this->line("    NPC #{$n->id}: key={$n->npc_key}, expires_at={$n->expires_at}");
            }
        }
        return self::SUCCESS;
    }
}
