<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Character;
use App\Models\ChatMessage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AnalyzePlayerActivityCommand extends Command
{
    protected $signature = 'tbs:player-activity {name=Arjan}';
    protected $description = 'Bereken de actieve speeltijd van een speler op basis van daadwerkelijke acties';

    public function handle(): int
    {
        $search = $this->argument('name');
        $character = Character::where('name', 'like', "%{$search}%")->first();

        if (! $character) {
            $this->error("Karakter '{$search}' niet gevonden!");
            return self::FAILURE;
        }

        $user = $character->user;

        $this->info("==================================================");
        $this->info("ANALYSE ACTIEVE SPEELTIJD: {$character->name} (Char ID: {$character->id}, User ID: {$user?->id})");
        $this->info("Account aangemaakt: {$character->created_at}");
        $this->info("Laatst actief: {$character->last_active_at}");
        $this->info("==================================================");

        // Collect all distinct timestamps of user actions
        $timestamps = collect();

        // 1. Audit logs
        if (Schema::hasTable('audit_logs')) {
            $auditTimes = DB::table('audit_logs')
                ->where('character_id', $character->id)
                ->orWhere('user_id', $user?->id)
                ->pluck('created_at')
                ->filter();
            $this->line("Audit logs gevonden: " . $auditTimes->count());
            $timestamps = $timestamps->merge($auditTimes);
        }

        // 2. Chat messages
        if (Schema::hasTable('chat_messages')) {
            $chatTimes = DB::table('chat_messages')
                ->where('character_id', $character->id)
                ->pluck('created_at')
                ->filter();
            $this->line("Chatberichten gevonden: " . $chatTimes->count());
            $timestamps = $timestamps->merge($chatTimes);
        }

        // 3. Bank transactions (if preserved or in logs)
        if (Schema::hasTable('bank_transactions')) {
            $bankTimes = DB::table('bank_transactions')
                ->where('character_id', $character->id)
                ->pluck('created_at')
                ->filter();
            $this->line("Banktransacties gevonden: " . $bankTimes->count());
            $timestamps = $timestamps->merge($bankTimes);
        }

        // 4. Game notifications sent TO or initiated BY character
        if (Schema::hasTable('game_notifications')) {
            $notifTimes = DB::table('game_notifications')
                ->where('character_id', $character->id)
                ->pluck('created_at')
                ->filter();
            $this->line("Notificaties gevonden: " . $notifTimes->count());
            $timestamps = $timestamps->merge($notifTimes);
        }

        // 5. Support tickets & ticket messages
        if (Schema::hasTable('support_tickets')) {
            $ticketTimes = DB::table('support_tickets')
                ->where('character_id', $character->id)
                ->orWhere('user_id', $user?->id)
                ->pluck('created_at')
                ->filter();
            $timestamps = $timestamps->merge($ticketTimes);
        }
        if (Schema::hasTable('support_ticket_messages')) {
            $ticketMsgTimes = DB::table('support_ticket_messages')
                ->where('character_id', $character->id)
                ->orWhere('user_id', $user?->id)
                ->pluck('created_at')
                ->filter();
            $timestamps = $timestamps->merge($ticketMsgTimes);
        }

        // 6. Direct messages / Chat conversations
        if (Schema::hasTable('direct_messages')) {
            $dmTimes = DB::table('direct_messages')
                ->where('sender_id', $character->id)
                ->pluck('created_at')
                ->filter();
            $this->line("Direct messages verstuurd: " . $dmTimes->count());
            $timestamps = $timestamps->merge($dmTimes);
        }

        // 7. Heist logs / character heists
        if (Schema::hasTable('heist_logs')) {
            $heistTimes = DB::table('heist_logs')
                ->where('character_id', $character->id)
                ->pluck('created_at')
                ->filter();
            $timestamps = $timestamps->merge($heistTimes);
        }

        // 8. Fight logs / attacks
        if (Schema::hasTable('attacks')) {
            $attackTimes = DB::table('attacks')
                ->where('attacker_id', $character->id)
                ->orWhere('defender_id', $character->id)
                ->pluck('created_at')
                ->filter();
            $timestamps = $timestamps->merge($attackTimes);
        }

        // 9. Check sessions table if exists
        if (Schema::hasTable('sessions') && $user) {
            $sessionTimes = DB::table('sessions')
                ->where('user_id', $user->id)
                ->pluck('last_activity')
                ->map(fn($ts) => Carbon::createFromTimestamp($ts)->toDateTimeString());
            $timestamps = $timestamps->merge($sessionTimes);
        }

        // Convert all to Carbon timestamps and sort chronologically
        $sortedTimestamps = $timestamps->map(function ($t) {
            return Carbon::parse($t)->timestamp;
        })->unique()->sort()->values();

        $this->info("Totaal aantal unieke interactie meetpunten: " . $sortedTimestamps->count());

        if ($sortedTimestamps->isEmpty()) {
            $this->warn("Geen meetpunten gevonden voor deze speler.");
            return self::SUCCESS;
        }

        // Active playtime calculation using session window clustering
        // If two interactions are within 5 minutes (300s) of each other, they belong to the same active session.
        // Between any two consecutive actions within the session, the time elapsed is considered active playtime.
        // A single standalone action without subsequent actions within 5 minutes is credited with 1 minute of active play.
        $totalActiveSeconds = 0;
        $sessionCount = 0;
        $maxGap = 300; // 5 minutes inactivity threshold

        $prev = null;
        $sessionStart = null;

        foreach ($sortedTimestamps as $curr) {
            if ($prev === null) {
                $sessionStart = $curr;
                $sessionCount++;
                $totalActiveSeconds += 60; // minimum credit for opening and taking an action
            } else {
                $diff = $curr - $prev;
                if ($diff <= $maxGap) {
                    $totalActiveSeconds += $diff;
                } else {
                    // New session started
                    $sessionCount++;
                    $totalActiveSeconds += 60; // minimum credit for new action
                }
            }
            $prev = $curr;
        }

        $totalMinutes = (int) round($totalActiveSeconds / 60);
        $totalHours = round($totalActiveSeconds / 3600, 1);
        $days = floor($totalHours / 24);
        $remHours = $totalHours - ($days * 24);

        $this->newLine();
        $this->info("==================================================");
        $this->info("RESULTAAT VOOR {$character->name}:");
        $this->info("Aantal actieve speelsessies: {$sessionCount}");
        $this->info("Totale ACTIEVE speeltijd in seconden: {$totalActiveSeconds}s");
        $this->info("Totale ACTIEVE speeltijd in minuten: {$totalMinutes} minuten");
        $this->info("Totale ACTIEVE speeltijd in uren: {$totalHours} uur");
        if ($days > 0) {
            $this->info("Omgerekend: {$days} dagen en {$remHours} uur");
        }
        $this->info("Eerste geregistreerde actie: " . Carbon::createFromTimestamp($sortedTimestamps->first())->toDateTimeString());
        $this->info("Laatste geregistreerde actie: " . Carbon::createFromTimestamp($sortedTimestamps->last())->toDateTimeString());
        $this->info("==================================================");

        return self::SUCCESS;
    }
}
