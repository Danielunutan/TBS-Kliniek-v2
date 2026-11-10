<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Character;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AnalyzePlayerActivityCommand extends Command
{
    protected $signature = 'tbs:player-activity {name=Arjan}';
    protected $description = 'Bereken de actieve speeltijd van een speler op basis van alle interacties (database en webserver access logs)';

    public function handle(): int
    {
        ini_set('memory_limit', '1024M');

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

        // Store all integer timestamps
        $intTimestamps = [];
        $sourceBreakdown = [];

        // 2. Scan ALL database tables dynamically
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

                $idToMatch = in_array($column, ['user_id', 'author_id', 'creator_id']) ? $user?->id : $character->id;
                if (! $idToMatch) {
                    continue;
                }

                $pairKey = "{$table}.{$column}";
                if (isset($scannedTables[$pairKey])) {
                    continue;
                }
                $scannedTables[$pairKey] = true;

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

                        $addedCount = 0;
                        foreach ($records as $val) {
                            if (! empty($val) && $val !== '0000-00-00 00:00:00') {
                                $ts = is_numeric($val) ? (int) $val : strtotime($val);
                                if ($ts && $ts > 1000000) {
                                    $intTimestamps[] = $ts;
                                    $addedCount++;
                                }
                            }
                        }

                        if ($addedCount > 0) {
                            $sourceBreakdown["{$table} ({$column} -> {$tc})"] = ($sourceBreakdown["{$table} ({$column} -> {$tc})"] ?? 0) + $addedCount;
                        }
                    } catch (\Throwable $e) {
                        // ignore query error on incompatible table/column
                    }
                }
            }
        } catch (\Throwable $e) {
            $this->warn("Dynamische schema scan waarschuwing: " . $e->getMessage());
        }

        // Print table breakdown
        $this->info("Gevonden database activiteitsrecords per tabel:");
        foreach ($sourceBreakdown as $source => $count) {
            $this->line("   - {$source}: {$count} acties");
        }

        // 3. Scan Web Server Access Logs
        $casinoRequests = collect();
        $logPaths = [
            '/var/www/vhosts/system/tbs-kliniek.nl/logs/access_ssl_log',
            '/var/www/vhosts/system/tbs-kliniek.nl/logs/access_ssl_log.processed',
            '/var/www/vhosts/system/tbs-kliniek.nl/logs/proxy_access_ssl_log',
        ];

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
            $ipArray = $knownIps->all();

            foreach ($readableLogs as $logPath) {
                $isGz = Str::endsWith($logPath, '.gz');
                $fp = $isGz ? @gzopen($logPath, 'rb') : @fopen($logPath, 'r');
                if (! $fp) {
                    continue;
                }

                while (($line = $isGz ? gzgets($fp, 8192) : fgets($fp, 8192)) !== false) {
                    // Check if line contains any known IP
                    $hasIp = false;
                    foreach ($ipArray as $ip) {
                        if (str_contains($line, $ip)) {
                            $hasIp = true;
                            break;
                        }
                    }
                    if (! $hasIp) {
                        continue;
                    }

                    if (! preg_match('/^(\S+)\s+\S+\s+\S+\s+\[([^\]]+)\]\s+"([A-Z]+)\s+([^\s\?]+)/', $line, $matches)) {
                        continue;
                    }

                    $method = $matches[3];
                    $path = $matches[4];
                    $dateStr = $matches[2];

                    // Exclude passive background polling and static assets
                    if (
                        $path === '/chat/poll-inkomend' ||
                        $path === '/cinema/poll' ||
                        $path === '/favicon.ico' ||
                        $path === '/up' ||
                        str_starts_with($path, '/build/') ||
                        str_starts_with($path, '/vendor/') ||
                        str_starts_with($path, '/images/') ||
                        str_starts_with($path, '/img/') ||
                        preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|woff|woff2|ico|map|webp)$/i', $path)
                    ) {
                        continue;
                    }

                    // Track casino specific requests
                    if (
                        str_contains($path, 'casino') ||
                        str_contains($path, 'roulette') ||
                        str_contains($path, 'slots') ||
                        str_contains($path, 'highlow') ||
                        str_contains($path, 'numbergame') ||
                        str_contains($path, 'rps') ||
                        str_contains($path, 'lottery')
                    ) {
                        $totalCasinoHttpClicks++;
                        if ($casinoRequests->count() < 25) {
                            $casinoRequests->push("{$method} {$path} [{$dateStr}]");
                        }
                    }

                    $ts = strtotime($dateStr);
                    if ($ts && $ts > 1000000) {
                        $intTimestamps[] = $ts;
                        $totalActiveLogEntries++;
                    }
                }

                if ($isGz) {
                    gzclose($fp);
                } else {
                    fclose($fp);
                }
            }

            $this->info("Actieve webserver interacties van Arjan gevonden (excl. passive chat pollers): {$totalActiveLogEntries}");
            $this->info("Waarvan directe casino & mini-game verzoeken: {$totalCasinoHttpClicks}");

            if ($casinoRequests->isNotEmpty()) {
                $this->info("Voorbeeld geregistreerde casino interacties uit web access logs:");
                foreach ($casinoRequests->take(10) as $cr) {
                    $this->line("   - {$cr}");
                }
            }
        }

        // 4. Calculate Clustering & Speeltijd
        $this->info("\n--- 3. SPEELTIJD BEREKENING (SESSION CLUSTERING) ---");

        // Deduplicate and sort timestamps
        $intTimestamps = array_values(array_unique($intTimestamps));
        sort($intTimestamps, SORT_NUMERIC);
        $totalPoints = count($intTimestamps);

        $this->info("Totaal aantal unieke interactie meetpunten (Database + Echte Webserver Acties): {$totalPoints}");

        if ($totalPoints === 0) {
            $this->warn("Geen meetpunten gevonden voor deze speler.");
            return self::SUCCESS;
        }

        // Multi-threshold comparison
        $thresholds = [
            '3 minuten (zeer strikt: alleen onafgebroken achter elkaar doorklikken)' => 180,
            '5 minuten (standaard maatstaf: rekening houdend met lezen, chatten en nadenken)' => 300,
            '10 minuten (ruime drempel: inclusief rustpauzes tussen acties)' => 600,
        ];

        foreach ($thresholds as $label => $maxGap) {
            $totalActiveSeconds = 0;
            $sessionCount = 0;
            $prev = null;

            foreach ($intTimestamps as $curr) {
                if ($prev === null) {
                    $sessionCount++;
                    $totalActiveSeconds += 60;
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
            $this->line("   -> {$totalMinutes} actieve minuten ({$totalHours} uur" . ($days > 0 ? " | {$days} dagen en {$remHours} uur" : "") . ") over {$sessionCount} aparte speelsessies");
        }

        // Detailed calculation for standard 5-minute threshold
        $maxGap = 300;
        $totalActiveSeconds = 0;
        $sessionCount = 0;
        $prev = null;
        $dailyActivity = [];

        foreach ($intTimestamps as $curr) {
            $day = date('Y-m-d', $curr);
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
        $this->info("EINDCONCLUSIE TOTALE ACTIEVE SPEELTIJD VOOR {$character->name}:");
        $this->info("Eerste geregistreerde interactie: " . date('Y-m-d H:i:s', $intTimestamps[0]));
        $this->info("Laatste geregistreerde interactie: " . date('Y-m-d H:i:s', $intTimestamps[$totalPoints - 1]));
        $this->info("Totaal aantal aparte speelsessies: {$sessionCount}");
        $this->info("Totale ACTIEVE zuivere speeltijd: {$totalMinutes} minuten");
        $this->info("Totale ACTIEVE speeltijd in uren: {$totalHours} uur");
        if ($days > 0) {
            $this->info("Omgerekend in hele dagen: {$days} dagen en {$remHours} uur");
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
            $this->info("TOP 15 UITGEVOERDE ACTIES IN HET SPEL (AuditLog):");
            foreach ($topActions as $a) {
                $this->line("   - {$a->action}: {$a->total}x");
            }
        }

        return self::SUCCESS;
    }
}
