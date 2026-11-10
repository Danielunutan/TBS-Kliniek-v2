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
use Illuminate\Support\Str;

class AnalyzePlayerActivityCommand extends Command
{
    protected $signature = 'tbs:player-activity {name=Arjan} {--include-logs : Also scan web server access logs for active HTTP requests}';
    protected $description = 'Bereken de actieve speeltijd van een speler op basis van alle interacties (database en optioneel webserver logs)';

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
        $this->info("UITGEBREIDE ANALYSE ACTIEVE SPEELTIJD: {$character->name}");
        $this->info("Character ID: {$character->id} | User ID: " . ($user?->id ?? 'onbekend'));
        $this->info("Account aangemaakt: {$character->created_at}");
        $this->info("Laatst actief gerapporteerd: {$character->last_active_at}");
        $this->info("==================================================");

        // 1. Gather all known IP addresses for this user
        $knownIps = collect();
        if ($user?->registration_ip) {
            $knownIps->push($user->registration_ip);
        }
        if ($user?->last_login_ip) {
            $knownIps->push($user->last_login_ip);
        }
        if (Schema::hasTable('audit_logs')) {
            $auditIps = DB::table('audit_logs')
                ->where('character_id', $character->id)
                ->orWhere('user_id', $user?->id)
                ->whereNotNull('ip_address')
                ->distinct()
                ->pluck('ip_address');
            $knownIps = $knownIps->merge($auditIps);
        }
        $knownIps = $knownIps->unique()->filter()->values();
        $this->info("Bekende IP-adressen van {$character->name}: " . ($knownIps->isEmpty() ? 'geen gevonden' : $knownIps->implode(', ')));

        // Collect all distinct timestamps of user actions
        $timestamps = collect();
        $sourceBreakdown = [];

        // 2. Scan ALL database tables dynamically for character_id or user_id references
        $this->info("\n--- 1. DATABASE TABELLEN DOORZOEKEN ---");
        try {
            $candidateColumns = DB::select("
                SELECT TABLE_NAME, COLUMN_NAME 
                FROM information_schema.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                  AND COLUMN_NAME IN ('character_id', 'user_id', 'player_id', 'sender_id', 'author_id', 'attacker_id', 'defender_id', 'challenger_id', 'opponent_id', 'creator_id')
            ");

            $scannedTables = [];
            foreach ($candidateColumns as $col) {
                $table = $col->TABLE_NAME;
                $column = $col->COLUMN_NAME;

                // Determine whether to match by character_id or user_id
                $idToMatch = in_array($column, ['user_id', 'author_id', 'creator_id']) ? $user?->id : $character->id;
                if (! $idToMatch) {
                    continue;
                }

                $pairKey = "{$table}.{$column}";
                if (isset($scannedTables[$pairKey])) {
                    continue;
                }
                $scannedTables[$pairKey] = true;

                // Find timestamp columns on this table
                $timeCols = DB::select("
                    SELECT COLUMN_NAME 
                    FROM information_schema.COLUMNS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                      AND TABLE_NAME = ? 
                      AND (
                          DATA_TYPE IN ('datetime', 'timestamp') 
                          OR COLUMN_NAME IN ('created_at', 'updated_at', 'timestamp', 'date', 'last_activity', 'completed_at', 'claimed_at', 'cooldown_until', 'scanned_at')
                      )
                ", [$table]);

                if (empty($timeCols)) {
                    continue;
                }

                foreach ($timeCols as $tCol) {
                    $tc = $tCol->COLUMN_NAME;
                    try {
                        $records = DB::table($table)
                            ->where($column, $idToMatch)
                            ->whereNotNull($tc)
                            ->pluck($tc);

                        if ($records->isNotEmpty()) {
                            $valid = $records->filter(function ($val) {
                                return ! empty($val) && $val !== '0000-00-00 00:00:00';
                            });
                            if ($valid->isNotEmpty()) {
                                $sourceBreakdown["{$table} ({$column} -> {$tc})"] = ($sourceBreakdown["{$table} ({$column} -> {$tc})"] ?? 0) + $valid->count();
                                $timestamps = $timestamps->merge($valid);
                            }
                        }
                    } catch (\Throwable $e) {
                        // ignore query error on incompatible table/column
                    }
                }
            }
        } catch (\Throwable $e) {
            $this->warn("Dynamische schema scan waarschuwing: " . $e->getMessage());
        }

        // Specifically check casino and minigame cooldowns
        if (Schema::hasTable('character_cooldowns')) {
            $casinoCooldowns = DB::table('character_cooldowns')
                ->where('character_id', $character->id)
                ->where(function ($q) {
                    $q->where('action', 'like', 'casino%')
                      ->orWhere('action', 'like', '%roulette%')
                      ->orWhere('action', 'like', '%slots%')
                      ->orWhere('action', 'like', '%dice%');
                })
                ->get();
            if ($casinoCooldowns->isNotEmpty()) {
                $this->info("Casino cooldown registraties gevonden: " . $casinoCooldowns->count());
            }
        }

        // Print table breakdown
        $this->info("Gevonden database activiteitsrecords per tabel:");
        foreach ($sourceBreakdown as $source => $count) {
            $this->line("   - {$source}: {$count} acties");
        }

        // 3. Scan Web Server Access Logs if available
        $logTimestamps = collect();
        $casinoRequests = collect();
        $logPaths = [
            '/var/www/vhosts/system/tbs-kliniek.nl/logs/access_ssl_log',
            '/var/www/vhosts/system/tbs-kliniek.nl/logs/access_ssl_log.processed',
            '/var/www/vhosts/system/tbs-kliniek.nl/logs/proxy_access_ssl_log',
        ];

        // Also check rotated .gz logs
        for ($i = 1; $i <= 10; $i++) {
            $gzPath = "/var/www/vhosts/system/tbs-kliniek.nl/logs/access_ssl_log.processed.{$i}.gz";
            if (file_exists($gzPath)) {
                $logPaths[] = $gzPath;
            }
        }

        $readableLogs = array_filter($logPaths, 'file_exists');

        if (! empty($readableLogs) && $knownIps->isNotEmpty()) {
            $this->info("\n--- 2. WEBSERVER ACCESS LOGS CONTROLEREN (INCL. CASINO & KLIKKEN) ---");
            $this->info("Controleren op IPs: " . $knownIps->implode(', '));

            $totalActiveLogEntries = 0;
            $totalCasinoHttpClicks = 0;

            foreach ($readableLogs as $logPath) {
                $isGz = Str::endsWith($logPath, '.gz');
                $fp = $isGz ? @gzopen($logPath, 'rb') : @fopen($logPath, 'r');
                if (! $fp) {
                    continue;
                }

                while (($line = $isGz ? gzgets($fp, 8192) : fgets($fp, 8192)) !== false) {
                    // Quick check if any known IP appears in this line
                    $matchedIp = false;
                    foreach ($knownIps as $ip) {
                        if (str_contains($line, $ip)) {
                            $matchedIp = true;
                            break;
                        }
                    }
                    if (! $matchedIp) {
                        continue;
                    }

                    // Extract IP, timestamp, method, url
                    // Format: 84.86.22.190 - - [09/Nov/2026:06:33:27 +0000] "GET /path HTTP/1.1" 200 7257
                    if (! preg_match('/^(\S+)\s+\S+\s+\S+\s+\[([^\]]+)\]\s+"([A-Z]+)\s+([^\s\?]+)/', $line, $matches)) {
                        continue;
                    }

                    $method = $matches[3];
                    $path = $matches[4];
                    $dateStr = $matches[2];

                    // Filter out passive background polls and static assets
                    if (
                        $path === '/chat/poll-inkomend' ||
                        $path === '/cinema/poll' ||
                        $path === '/favicon.ico' ||
                        $path === '/up' ||
                        Str::startsWith($path, ['/build/', '/vendor/', '/images/', '/img/']) ||
                        Str::endsWith($path, ['.css', '.js', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.woff', '.woff2', '.ico', '.map', '.webp'])
                    ) {
                        continue; // skip passive or asset request
                    }

                    // Track casino specific requests
                    if (str_contains($path, 'casino') || str_contains($path, 'roulette') || str_contains($path, 'slots') || str_contains($path, 'highlow') || str_contains($path, 'numbergame') || str_contains($path, 'rps') || str_contains($path, 'lottery')) {
                        $totalCasinoHttpClicks++;
                        $casinoRequests->push("{$method} {$path} [{$dateStr}]");
                    }

                    // Parse timestamp
                    try {
                        $parsedDate = Carbon::createFromFormat('d/M/Y:H:i:s O', $dateStr);
                        if ($parsedDate) {
                            $logTimestamps->push($parsedDate->toDateTimeString());
                            $totalActiveLogEntries++;
                        }
                    } catch (\Throwable $e) {
                        // ignore parse failure
                    }
                }

                if ($isGz) {
                    gzclose($fp);
                } else {
                    fclose($fp);
                }
            }

            $this->info("Actieve webserver verzoeken van Arjan gevonden (excl. passive pollers): {$totalActiveLogEntries}");
            $this->info("Waarvan directe casino & mini-game verzoeken: {$totalCasinoHttpClicks}");

            if ($casinoRequests->isNotEmpty()) {
                $this->info("Voorbeeld casino verzoeken uit web logs:");
                foreach ($casinoRequests->take(8) as $cr) {
                    $this->line("   - {$cr}");
                }
            }

            $timestamps = $timestamps->merge($logTimestamps);
        } else {
            $this->warn("\nWebserver logs niet rechtstreeks leesbaar als huidige gebruiker of geen logs gevonden.");
        }

        // 4. Calculate Clustering & Speeltijd
        $this->info("\n--- 3. SPEELTIJD BEREKENING (SESSION CLUSTERING) ---");

        $sortedTimestamps = $timestamps->map(function ($t) {
            try {
                if (is_numeric($t)) {
                    return (int) $t;
                }
                return Carbon::parse($t)->timestamp;
            } catch (\Throwable $e) {
                return null;
            }
        })->filter()->unique()->sort()->values();

        $this->info("Totaal aantal unieke interactie meetpunten (DB + HTTP): " . $sortedTimestamps->count());

        if ($sortedTimestamps->isEmpty()) {
            $this->warn("Geen meetpunten gevonden voor deze speler.");
            return self::SUCCESS;
        }

        // Test with different session gap thresholds: 3 min, 5 min, 10 min
        $thresholds = [
            '3 minuten (zeer strikt: alleen aaneengesloten actie)' => 180,
            '5 minuten (standaard: rekening houdend met lezen/nadenken/chatten)' => 300,
            '10 minuten (ruim: langere pauzes tussen acties)' => 600,
        ];

        foreach ($thresholds as $label => $maxGap) {
            $totalActiveSeconds = 0;
            $sessionCount = 0;
            $prev = null;

            foreach ($sortedTimestamps as $curr) {
                if ($prev === null) {
                    $sessionCount++;
                    $totalActiveSeconds += 60; // minimum 1 minuut per opstartsessie
                } else {
                    $diff = $curr - $prev;
                    if ($diff <= $maxGap) {
                        $totalActiveSeconds += $diff;
                    } else {
                        $sessionCount++;
                        $totalActiveSeconds += 60;
                    }
                }
                $prev = $curr;
            }

            $totalMinutes = (int) round($totalActiveSeconds / 60);
            $totalHours = round($totalActiveSeconds / 3600, 1);
            $days = floor($totalHours / 24);
            $remHours = round($totalHours - ($days * 24), 1);

            $this->info("Drempel [{$label}]:");
            $this->line("   -> {$totalMinutes} actieve minuten ({$totalHours} uur" . ($days > 0 ? " | {$days} dagen en {$remHours} uur" : "") . ") over {$sessionCount} afzonderlijke speelsessies");
        }

        // Calculate primary 5-min threshold
        $maxGap = 300;
        $totalActiveSeconds = 0;
        $sessionCount = 0;
        $prev = null;
        $dailyActivity = [];

        foreach ($sortedTimestamps as $curr) {
            $day = Carbon::createFromTimestamp($curr)->toDateString();
            if ($prev === null) {
                $sessionCount++;
                $totalActiveSeconds += 60;
                $dailyActivity[$day] = ($dailyActivity[$day] ?? 0) + 60;
            } else {
                $diff = $curr - $prev;
                if ($diff <= $maxGap) {
                    $totalActiveSeconds += $diff;
                    $dailyActivity[$day] = ($dailyActivity[$day] ?? 0) + $diff;
                } else {
                    $sessionCount++;
                    $totalActiveSeconds += 60;
                    $dailyActivity[$day] = ($dailyActivity[$day] ?? 0) + 60;
                }
            }
            $prev = $curr;
        }

        $totalMinutes = (int) round($totalActiveSeconds / 60);
        $totalHours = round($totalActiveSeconds / 3600, 1);
        $days = floor($totalHours / 24);
        $remHours = round($totalHours - ($days * 24), 1);

        $this->newLine();
        $this->info("==================================================");
        $this->info("EINDCONCLUSIE VOOR {$character->name}:");
        $this->info("Eerste geregistreerde actie: " . Carbon::createFromTimestamp($sortedTimestamps->first())->toDateTimeString());
        $this->info("Laatste geregistreerde actie: " . Carbon::createFromTimestamp($sortedTimestamps->last())->toDateTimeString());
        $this->info("Totaal actieve sessies: {$sessionCount}");
        $this->info("Totale ACTIEVE zuivere speeltijd: {$totalMinutes} minuten");
        $this->info("Totale ACTIEVE speeltijd in uren: {$totalHours} uur");
        if ($days > 0) {
            $this->info("Omgerekend: {$days} dagen en {$remHours} uur");
        }
        $this->info("==================================================");

        // Daily breakdown
        $this->newLine();
        $this->info("ACTIEVE SPEELTIJD PER DAG (5-min drempel):");
        foreach ($dailyActivity as $d => $secs) {
            $mins = round($secs / 60);
            $hrs = round($secs / 3600, 1);
            $this->line("   - {$d}: {$mins} minuten ({$hrs} uur)");
        }

        // Top actions
        if (Schema::hasTable('audit_logs')) {
            $topActions = DB::table('audit_logs')
                ->where('character_id', $character->id)
                ->select('action', DB::raw('count(*) as total'))
                ->groupBy('action')
                ->orderByDesc('total')
                ->limit(15)
                ->get();

            $this->newLine();
            $this->info("TOP 15 UITGEVOERDE ACTIES VAN {$character->name} (AuditLog):");
            foreach ($topActions as $a) {
                $this->line("   - {$a->action}: {$a->total}x");
            }
        }

        return self::SUCCESS;
    }
}
