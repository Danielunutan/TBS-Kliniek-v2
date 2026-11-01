<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Character;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;

class AuditLogService
{
    /**
     * Actions where the logged user/character is the target of an admin, system, or webhook action rather than the HTTP request actor.
     *
     * @var list<string>
     */
    protected const TARGET_OR_EXTERNAL_ACTIONS = [
        'user_ban',
        'user_unban',
        'user_warning',
        'warning_deleted',
        'discord_shield_manual_approval',
        'buymeacoffee_manual_link',
        'buymeacoffee_regrant',
        'buymeacoffee_donation',
        'buymeacoffee_membership',
        'buymeacoffee_membership_cancelled',
        'buymeacoffee_extra',
    ];

    /**
     * Log an action with automatic risk assessment.
     *
     * @param  array<string, mixed>  $details
     */
    public static function log(
        string $action,
        string $category,
        string $description,
        ?Character $character = null,
        ?User $user = null,
        ?int $amount = null,
        array $details = [],
        ?string $customRisk = null,
        ?string $customReason = null,
        ?Carbon $createdAt = null,
        ?string $ipAddress = null
    ): AuditLog {
        if ($character !== null && ($user === null || (int) $character->user_id !== (int) $user->id)) {
            $user = $character->user ?? $user;
        }

        $isAuthenticatedActor = Request::hasSession()
            && Auth::check()
            && $user !== null
            && (int) Auth::id() === (int) $user->id
            && $createdAt === null
            && empty($details['backfilled'])
            && ! self::isPassiveOrExternalEvent($action, $description);

        $ip = $ipAddress ?? ($isAuthenticatedActor ? Request::ip() : null);

        if ($isAuthenticatedActor && ! empty($ip) && $user !== null && $user->last_login_ip !== $ip) {
            try {
                $user->forceFill(['last_login_ip' => $ip])->saveQuietly();
            } catch (\Throwable) {
                // Ignore if user model cannot be updated during read-only context
            }
        }

        [$riskLevel, $riskScore, $riskReason, $riskFlags] = self::assessRisk(
            $action,
            $category,
            $amount,
            $character,
            $details,
            $customRisk,
            $customReason
        );

        $log = new AuditLog([
            'user_id' => $user?->id,
            'character_id' => $character?->id,
            'action' => $action,
            'category' => $category,
            'description' => $description,
            'amount' => $amount,
            'risk_level' => $riskLevel,
            'risk_score' => $riskScore,
            'risk_reason' => $riskReason,
            'risk_flags' => $riskFlags,
            'ip_address' => $ip,
            'details' => $details,
        ]);

        if ($createdAt) {
            $log->created_at = $createdAt;
            $log->updated_at = $createdAt;
        }

        $log->save();

        return $log;
    }

    /**
     * Check whether an audit event is passive, target-oriented, or webhook-driven so the current request IP must not be attached.
     */
    public static function isPassiveOrExternalEvent(string $action, string $description): bool
    {
        if (in_array($action, self::TARGET_OR_EXTERNAL_ACTIONS, true)) {
            return true;
        }

        if (str_starts_with($description, 'Bank geplunderd bij liquidatie')) {
            return true;
        }

        return false;
    }

    /**
     * Determine risk score, level, and reasons.
     *
     * @param  array<string, mixed>  $details
     * @return array{0: string, 1: int, 2: ?string, 3: array<string>}
     */
    public static function assessRisk(
        string $action,
        string $category,
        ?int $amount,
        ?Character $character,
        array $details,
        ?string $customRisk = null,
        ?string $customReason = null
    ): array {
        if ($customRisk) {
            $score = match ($customRisk) {
                'hoog' => 85,
                'verdacht' => 50,
                default => 10,
            };

            return [
                $customRisk,
                $score,
                $customReason ?: 'Handmatig ingesteld risiconiveau',
                $customReason ? [$customReason] : [],
            ];
        }

        $flags = [];
        $score = 5;

        // 1. Evaluate money transfers, transactions, negative values, and integer overflows
        if ($amount !== null) {
            $txType = (string) ($details['type'] ?? '');
            $isLegitimateDebit = $action === 'bank_transaction'
                && in_array($txType, ['withdraw', 'withdrawal', 'loan_repayment'], true);
            $evaluatedAmount = $isLegitimateDebit ? abs($amount) : $amount;

            if ($evaluatedAmount < 0) {
                $score += 90;
                $flags[] = 'Negatief bedrag (€'.number_format($amount, 0, ',', '.').') - Mogelijke exploit poging';
            } elseif ($evaluatedAmount >= 2_000_000_000) {
                $score += 85;
                $flags[] = 'Uitzonderlijk bedrag dichtbij integer limiet (€'.number_format($evaluatedAmount, 0, ',', '.').')';
            } elseif ($evaluatedAmount >= 10_000_000) {
                $score += 75;
                $flags[] = 'Extreem hoog geldbedrag (€'.number_format($evaluatedAmount, 0, ',', '.').')';
            } elseif ($evaluatedAmount >= 2_500_000) {
                $score += 45;
                $flags[] = 'Aanzienlijk geldbedrag (€'.number_format($evaluatedAmount, 0, ',', '.').')';
            } elseif ($evaluatedAmount >= 1_000_000) {
                $score += 25;
                $flags[] = 'Groot geldbedrag (€'.number_format($evaluatedAmount, 0, ',', '.').')';
            }

            if ($character && $character->level < 10 && $evaluatedAmount >= 1_000_000) {
                $score += 40;
                $flags[] = "Lage level speler (lvl {$character->level}) handelt met hoog bedrag";
            }
        }

        // 2. Velocity and timing automation anomalies
        $durationMs = (int) ($details['duration_ms'] ?? 0);
        $actionsPerMin = (int) ($details['actions_per_minute'] ?? 0);

        if ($durationMs > 0 && $durationMs < 250 && ! str_contains($action, 'arm_wrestle')) {
            $score += 75;
            $flags[] = "Extreem snelle actie ({$durationMs}ms) - Mogelijke automatisering/clickbot";
        }

        if ($actionsPerMin > 60) {
            $score += 65;
            $flags[] = "Hoge actiesnelheid ({$actionsPerMin} acties/minuut) - Mogelijke macro";
        }

        // 3. Multi-account and shared IP anomalies
        if (! empty($details['same_ip_transfer']) || (int) ($details['ip_shared_accounts_count'] ?? 0) >= 3) {
            $score += 75;
            $flags[] = 'Geldoverdracht of transactie tussen accounts op hetzelfde IP-adres';
        }

        if (! empty($details['ip_flagged']) || ! empty($details['is_vpn'])) {
            $score += 25;
            $flags[] = 'Verbinding vanaf verdacht of anoniem IP/VPN netwerk';
        }

        // 4. Action specific checks
        if ($action === 'user_ban') {
            $score += 70;
            $flags[] = 'Account verbannen door beheer';
        } elseif ($action === 'user_warning') {
            $score += 40;
            $flags[] = 'Officiële waarschuwing opgelegd';
        } elseif (str_contains($action, 'theft')) {
            $stolen = (int) ($details['cash_stolen'] ?? 0);
            if ($stolen >= 2_500_000) {
                $score += 65;
                $flags[] = 'Enorme diefstal buit (€'.number_format($stolen, 0, ',', '.').')';
            } elseif ($stolen >= 750_000) {
                $score += 35;
                $flags[] = 'Hoge diefstal buit (€'.number_format($stolen, 0, ',', '.').')';
            }
        } elseif ($action === 'arm_wrestle') {
            $bet = (int) ($details['bet_amount'] ?? 0);

            if ($durationMs > 0 && $durationMs < 400) {
                $score += 80;
                $flags[] = "Verdacht snelle armdrukwinst ({$durationMs}ms) mogelijke macro/clickbot";
            }
            if ($bet >= 5_000_000) {
                $score += 60;
                $flags[] = 'Enorme inzet bij armdrukken (€'.number_format($bet, 0, ',', '.').')';
            }
        } elseif ($action === 'gang_donation') {
            if (($amount ?? 0) >= 10_000_000) {
                $score += 40;
                $flags[] = 'Massale bende donatie (€'.number_format($amount, 0, ',', '.').')';
            }
        } elseif (str_contains($action, 'gang_withdraw') || $action === 'gang_vault_withdrawal') {
            if (($amount ?? 0) >= 5_000_000) {
                $score += 60;
                $flags[] = 'Grote opname uit bendekluis (€'.number_format((int) $amount, 0, ',', '.').')';
            }
            if (! empty($details['unauthorized_role'])) {
                $score += 45;
                $flags[] = 'Bende-opname door niet-gemachtigd lid';
            }
        }

        // 5. Category specific heuristics
        if ($category === 'security' || str_contains($action, 'exploit') || str_contains($action, 'bypass')) {
            $score += 80;
            $flags[] = 'Beveiligingswaarschuwing of mogelijke exploit poging';
        } elseif ($category === 'casino') {
            $bet = (int) ($details['bet_amount'] ?? $amount ?? 0);
            $payout = (int) ($details['payout_amount'] ?? 0);
            $multiplier = (float) ($details['multiplier'] ?? 0);

            if ($bet >= 5_000_000) {
                $score += 50;
                $flags[] = 'Grote casino inzet (€'.number_format($bet, 0, ',', '.').')';
            }
            if ($payout >= 15_000_000 || $multiplier >= 50.0) {
                $score += 60;
                $flags[] = 'Uitzonderlijk hoge casino winst (€'.number_format($payout, 0, ',', '.').')';
            }
            if ($character && $character->level < 5 && $bet >= 1_000_000) {
                $score += 45;
                $flags[] = "Beginnende speler (lvl {$character->level}) plaatst grote casino inzet";
            }
        } elseif ($category === 'marketplace') {
            $price = (int) ($details['price'] ?? $amount ?? 0);

            if ($price >= 25_000_000) {
                $score += 55;
                $flags[] = 'Opmerkelijk hoge marktplaatstransactie (€'.number_format($price, 0, ',', '.').')';
            }
            if (! empty($details['suspicious_trade'])) {
                $score += 60;
                $flags[] = 'Afwijkende marktplaatshandel (mogelijke verkapte geldoverheveling)';
            }
        } elseif ($category === 'heist') {
            $loot = (int) ($details['cash_earned'] ?? $amount ?? 0);
            $seconds = (int) ($details['duration_seconds'] ?? 0);

            if ($loot >= 15_000_000) {
                $score += 50;
                $flags[] = 'Enorme buit bij overval (€'.number_format($loot, 0, ',', '.').')';
            }
            if ($seconds > 0 && $seconds < 5) {
                $score += 75;
                $flags[] = "Verdacht korte overval-duur ({$seconds}s) mogelijke speedhack";
            }
        }

        $score = min(100, $score);

        if ($score >= 70) {
            $level = 'hoog';
        } elseif ($score >= 35) {
            $level = 'verdacht';
        } else {
            $level = 'normaal';
        }

        $primaryReason = ! empty($flags) ? implode('; ', $flags) : ($level === 'normaal' ? 'Reguliere activiteit' : 'Verhoogd risico');

        return [$level, $score, $primaryReason, $flags];
    }

    /**
     * Bulk fetch existing source keys to avoid N+1 queries.
     *
     * @param  array<string>  $sourceKeys
     * @return array<string, bool>
     */
    protected static function getExistingSourceKeys(string $action, array $sourceKeys): array
    {
        if (empty($sourceKeys)) {
            return [];
        }

        $existing = [];

        foreach (array_chunk($sourceKeys, 500) as $chunk) {
            $rows = DB::table('audit_logs')
                ->where('action', $action)
                ->whereIn('details->source_key', $chunk)
                ->get(['details']);

            foreach ($rows as $row) {
                $details = is_string($row->details) ? json_decode($row->details, true) : (array) $row->details;
                if (isset($details['source_key']) && is_string($details['source_key'])) {
                    $existing[$details['source_key']] = true;
                }
            }
        }

        return $existing;
    }

    /**
     * Sanitize existing audit log records where a foreign session IP (from backfill, liquidation victim, or admin action) was previously attached.
     */
    public static function sanitizeContaminatedIps(): int
    {
        try {
            $rows = DB::table('audit_logs')
                ->whereNotNull('ip_address')
                ->where('ip_address', '!=', '')
                ->orderBy('id', 'asc')
                ->get(['id', 'user_id', 'character_id', 'action', 'description', 'ip_address', 'details', 'created_at']);
        } catch (\Throwable) {
            return 0;
        }

        if ($rows->isEmpty()) {
            return 0;
        }

        $cleaned = 0;

        // 1. Ensure character_id and user_id on audit_logs always belong to the same account.
        if (Schema::hasTable('characters')) {
            $charToUser = DB::table('characters')->pluck('user_id', 'id')->all();
            $userToChar = DB::table('characters')->pluck('id', 'user_id')->all();

            foreach ($rows as $row) {
                if (! $row->user_id || ! $row->character_id) {
                    continue;
                }
                $expectedUserId = $charToUser[$row->character_id] ?? null;
                if ($expectedUserId !== null && (int) $expectedUserId !== (int) $row->user_id) {
                    if (in_array($row->action, ['report_resolved', 'report_dismissed', 'admin_run_migrations', 'admin_run_normalization', 'discord_shield_ban_add', 'discord_shield_ban_remove'], true)) {
                        $actorCharId = $userToChar[$row->user_id] ?? null;
                        DB::table('audit_logs')->where('id', $row->id)->update(['character_id' => $actorCharId]);
                        $row->character_id = $actorCharId;
                    } else {
                        DB::table('audit_logs')->where('id', $row->id)->update([
                            'user_id' => (int) $expectedUserId,
                            'ip_address' => null,
                        ]);
                        $row->user_id = (int) $expectedUserId;
                        $cleaned++;
                    }
                }
            }
        }

        // 2. Build map of verified user IPs from users, sessions, discord_verifications, and strictly first-party actions.
        $authVerifiedUserIps = [];
        $verifiedUserIps = [];

        if (Schema::hasTable('users')) {
            $hasRegIp = Schema::hasColumn('users', 'registration_ip');
            $hasLoginIp = Schema::hasColumn('users', 'last_login_ip');
            if ($hasRegIp || $hasLoginIp) {
                $cols = ['id'];
                if ($hasRegIp) {
                    $cols[] = 'registration_ip';
                }
                if ($hasLoginIp) {
                    $cols[] = 'last_login_ip';
                }
                foreach (DB::table('users')->get($cols) as $u) {
                    $uid = (int) $u->id;
                    if ($hasRegIp && ! empty($u->registration_ip)) {
                        $ip = trim((string) $u->registration_ip);
                        $authVerifiedUserIps[$uid][$ip] = true;
                        $verifiedUserIps[$uid][$ip] = true;
                    }
                    if ($hasLoginIp && ! empty($u->last_login_ip)) {
                        $ip = trim((string) $u->last_login_ip);
                        $authVerifiedUserIps[$uid][$ip] = true;
                        $verifiedUserIps[$uid][$ip] = true;
                    }
                }
            }
        }

        if (Schema::hasTable('sessions')) {
            try {
                $sessions = DB::table('sessions')
                    ->whereNotNull('user_id')
                    ->whereNotNull('ip_address')
                    ->where('ip_address', '!=', '')
                    ->get(['user_id', 'ip_address']);
                foreach ($sessions as $s) {
                    $uid = (int) $s->user_id;
                    $ip = trim((string) $s->ip_address);
                    $authVerifiedUserIps[$uid][$ip] = true;
                    $verifiedUserIps[$uid][$ip] = true;
                }
            } catch (\Throwable) {
                // Ignore if sessions table cannot be queried
            }
        }

        if (Schema::hasTable('discord_verifications')) {
            try {
                $verifications = DB::table('discord_verifications')
                    ->whereNotNull('user_id')
                    ->whereNotNull('ip_address')
                    ->where('ip_address', '!=', '')
                    ->get(['user_id', 'ip_address']);
                foreach ($verifications as $v) {
                    $uid = (int) $v->user_id;
                    $ip = trim((string) $v->ip_address);
                    $authVerifiedUserIps[$uid][$ip] = true;
                    $verifiedUserIps[$uid][$ip] = true;
                }
            } catch (\Throwable) {
                // Ignore if discord_verifications table cannot be queried
            }
        }

        // First pass: collect strictly first-party real-time logs (no source_key, no passive/target actions) as verified user IPs
        foreach ($rows as $row) {
            if (! $row->user_id) {
                continue;
            }
            $uid = (int) $row->user_id;
            $ip = trim((string) $row->ip_address);
            $action = (string) ($row->action ?? '');
            $desc = (string) ($row->description ?? '');
            $details = is_string($row->details) ? (json_decode($row->details, true) ?: []) : (array) $row->details;

            if (
                empty($details['source_key'])
                && empty($details['backfilled'])
                && ! self::isPassiveOrExternalEvent($action, $desc)
                && ! str_starts_with($desc, 'TBS Patiënten Uitkering')
                && ! ($action === 'arm_wrestle' && str_starts_with($desc, 'PvP armdrukken duel beslecht'))
            ) {
                // Only add if user has no conflicting auth-verified IPs or already owns this IP
                if (empty($authVerifiedUserIps[$uid]) || ! empty($authVerifiedUserIps[$uid][$ip])) {
                    $verifiedUserIps[$uid][$ip] = true;
                }
            }
        }

        // Second pass: detect contaminated rows
        $idsToClear = [];
        $maxCreatedAtTs = 0;
        $maxSourceIdByPrefix = [
            'bank_tx_' => 0,
            'theft_' => 0,
        ];

        $rowList = $rows->values()->all();
        $count = count($rowList);

        for ($i = 0; $i < $count; $i++) {
            $row = $rowList[$i];
            $uid = $row->user_id ? (int) $row->user_id : null;
            $ip = trim((string) $row->ip_address);
            $action = (string) ($row->action ?? '');
            $desc = (string) ($row->description ?? '');
            $details = is_string($row->details) ? (json_decode($row->details, true) ?: []) : (array) $row->details;
            $sourceKey = isset($details['source_key']) && is_string($details['source_key']) ? $details['source_key'] : null;
            $hasSourceKey = $sourceKey !== null && $sourceKey !== '';
            $ts = $row->created_at ? Carbon::parse($row->created_at)->getTimestamp() : 0;

            $isContaminated = false;

            // A. Passive victim, moderation target, or external webhook actions
            if (self::isPassiveOrExternalEvent($action, $desc)) {
                $isContaminated = true;
            } elseif ($action === 'arm_wrestle' && str_starts_with($desc, 'PvP armdrukken duel beslecht')) {
                if (isset($details['challenger_id']) && (int) $details['challenger_id'] === (int) $row->character_id) {
                    $isContaminated = true;
                }
            }

            // B. Backfilled records or backfill-only source_key actions
            if (! empty($details['backfilled'])) {
                $isContaminated = true;
            } elseif ($hasSourceKey && in_array($action, ['gang_donation', 'arm_wrestle', 'user_ban', 'user_warning'], true)) {
                $isContaminated = true;
            }

            // C. Source key ID monotonicity and chronological order check for backfillHistory() batches
            if ($hasSourceKey && $sourceKey !== null) {
                foreach (['bank_tx_', 'theft_'] as $prefix) {
                    if (str_starts_with($sourceKey, $prefix)) {
                        $numId = (int) substr($sourceKey, strlen($prefix));
                        if ($numId > 0) {
                            if ($numId < $maxSourceIdByPrefix[$prefix]) {
                                $isContaminated = true;
                            }
                            // Check if next row with same prefix has a smaller source ID (first row of descending backfill batch)
                            for ($j = $i + 1; $j < min($count, $i + 5); $j++) {
                                $nextDetails = is_string($rowList[$j]->details) ? (json_decode($rowList[$j]->details, true) ?: []) : (array) $rowList[$j]->details;
                                $nextKey = isset($nextDetails['source_key']) && is_string($nextDetails['source_key']) ? $nextDetails['source_key'] : '';
                                if (str_starts_with($nextKey, $prefix)) {
                                    $nextNumId = (int) substr($nextKey, strlen($prefix));
                                    if ($nextNumId > 0 && $nextNumId < $numId) {
                                        $isContaminated = true;
                                    }
                                    break;
                                }
                            }
                            $maxSourceIdByPrefix[$prefix] = max($maxSourceIdByPrefix[$prefix], $numId);
                        }
                    }
                }

                if ($ts > 0 && $maxCreatedAtTs > 0 && $ts < ($maxCreatedAtTs - 2)) {
                    $isContaminated = true;
                }
            }

            // D. Source key or allowance row where IP does not match user's verified IPs
            if ($hasSourceKey || str_starts_with($desc, 'TBS Patiënten Uitkering')) {
                if ($uid === null || empty($verifiedUserIps[$uid][$ip])) {
                    $isContaminated = true;
                } elseif (! empty($authVerifiedUserIps[$uid]) && empty($authVerifiedUserIps[$uid][$ip])) {
                    $isContaminated = true;
                }
            }

            if ($ts > 0) {
                $maxCreatedAtTs = max($maxCreatedAtTs, $ts);
            }

            if ($isContaminated) {
                $idsToClear[] = (int) $row->id;
            }
        }

        if (! empty($idsToClear)) {
            foreach (array_chunk(array_unique($idsToClear), 500) as $chunk) {
                $cleaned += DB::table('audit_logs')
                    ->whereIn('id', $chunk)
                    ->update(['ip_address' => null]);
            }
        }

        return $cleaned;
    }

    /**
     * Backfill historical events from core game log tables.
     *
     * @return array{total_synced: int, high_risk: int, suspicious: int}
     */
    public static function backfillHistory(int $limitPerSource = 1500): array
    {
        self::sanitizeContaminatedIps();

        $synced = 0;
        $highRisk = 0;
        $suspicious = 0;

        // 1. Bank Transactions
        $bankTxs = DB::table('bank_transactions')
            ->orderBy('id', 'desc')
            ->limit($limitPerSource)
            ->get();

        $bankKeys = $bankTxs->map(fn ($tx) => "bank_tx_{$tx->id}")->all();
        $existingBankKeys = self::getExistingSourceKeys('bank_transaction', $bankKeys);

        $bankCharIds = $bankTxs->pluck('character_id')->filter()->unique()->values()->all();
        $bankChars = ! empty($bankCharIds)
            ? Character::with('user')->whereIn('id', $bankCharIds)->get()->keyBy('id')
            : collect();

        foreach ($bankTxs as $tx) {
            $sourceKey = "bank_tx_{$tx->id}";
            if (isset($existingBankKeys[$sourceKey])) {
                continue;
            }

            $char = $bankChars->get($tx->character_id);
            $amount = (int) $tx->amount;
            $desc = $tx->description ?: "Bank {$tx->type} van €".number_format($amount, 0, ',', '.');

            $log = self::log(
                action: 'bank_transaction',
                category: 'economy',
                description: $desc,
                character: $char,
                user: $char?->user,
                amount: $amount,
                details: [
                    'source_key' => $sourceKey,
                    'backfilled' => true,
                    'type' => $tx->type,
                    'source' => $tx->source,
                    'balance_after' => $tx->balance_after,
                ],
                createdAt: Carbon::parse($tx->created_at),
                ipAddress: null
            );

            $synced++;
            if ($log->risk_level === 'hoog') {
                $highRisk++;
            } elseif ($log->risk_level === 'verdacht') {
                $suspicious++;
            }
        }

        // 2. Theft Records
        $thefts = DB::table('theft_records')
            ->orderBy('id', 'desc')
            ->limit($limitPerSource)
            ->get();

        $theftKeys = $thefts->map(fn ($th) => "theft_{$th->id}")->all();
        $existingTheftKeys = self::getExistingSourceKeys('theft', $theftKeys);

        $theftCharIds = $thefts->pluck('thief_id')
            ->concat($thefts->pluck('victim_id'))
            ->filter()
            ->unique()
            ->values()
            ->all();
        $theftChars = ! empty($theftCharIds)
            ? Character::with('user')->whereIn('id', $theftCharIds)->get()->keyBy('id')
            : collect();

        foreach ($thefts as $th) {
            $sourceKey = "theft_{$th->id}";
            if (isset($existingTheftKeys[$sourceKey])) {
                continue;
            }

            $thief = $theftChars->get($th->thief_id);
            $victim = $th->victim_id ? $theftChars->get($th->victim_id) : null;
            $targetName = $victim ? $victim->name : ($th->target_name ?: 'Onbekend doelwit');
            $status = $th->success ? 'Succesvol' : ($th->jailed ? "Mislukt (opgepakt voor {$th->jail_minutes}m)" : 'Mislukt');
            $stolen = (int) $th->cash_stolen;
            $desc = "Zakkenrollen: {$targetName} - {$status} (€".number_format($stolen, 0, ',', '.').')';

            $log = self::log(
                action: 'theft',
                category: 'crime',
                description: $desc,
                character: $thief,
                user: $thief?->user,
                amount: $stolen,
                details: [
                    'source_key' => $sourceKey,
                    'backfilled' => true,
                    'success' => (bool) $th->success,
                    'cash_stolen' => $stolen,
                    'victim_id' => $th->victim_id,
                    'victim_name' => $victim?->name,
                    'jailed' => (bool) $th->jailed,
                ],
                createdAt: Carbon::parse($th->created_at),
                ipAddress: null
            );

            $synced++;
            if ($log->risk_level === 'hoog') {
                $highRisk++;
            } elseif ($log->risk_level === 'verdacht') {
                $suspicious++;
            }
        }

        // 3. Gang Donations
        $donations = DB::table('gang_donations')
            ->orderBy('id', 'desc')
            ->limit($limitPerSource)
            ->get();

        $donationKeys = $donations->map(fn ($don) => "gang_don_{$don->id}")->all();
        $existingDonationKeys = self::getExistingSourceKeys('gang_donation', $donationKeys);

        $donationCharIds = $donations->pluck('character_id')->filter()->unique()->values()->all();
        $donationChars = ! empty($donationCharIds)
            ? Character::with('user')->whereIn('id', $donationCharIds)->get()->keyBy('id')
            : collect();

        foreach ($donations as $don) {
            $sourceKey = "gang_don_{$don->id}";
            if (isset($existingDonationKeys[$sourceKey])) {
                continue;
            }

            $char = $donationChars->get($don->character_id);
            $amount = (int) $don->amount;
            $desc = 'Donatie van €'.number_format($amount, 0, ',', '.')." aan bende #{$don->gang_id}";

            $log = self::log(
                action: 'gang_donation',
                category: 'gang',
                description: $desc,
                character: $char,
                user: $char?->user,
                amount: $amount,
                details: [
                    'source_key' => $sourceKey,
                    'backfilled' => true,
                    'gang_id' => $don->gang_id,
                ],
                createdAt: Carbon::parse($don->created_at),
                ipAddress: null
            );

            $synced++;
            if ($log->risk_level === 'hoog') {
                $highRisk++;
            } elseif ($log->risk_level === 'verdacht') {
                $suspicious++;
            }
        }

        // 4. Arm Wrestling Matches
        $matches = DB::table('arm_wrestle_matches')
            ->whereNotNull('winner_id')
            ->orderBy('id', 'desc')
            ->limit($limitPerSource)
            ->get();

        $matchKeys = $matches->map(fn ($m) => "arm_{$m->id}")->all();
        $existingMatchKeys = self::getExistingSourceKeys('arm_wrestle', $matchKeys);

        $winnerIds = $matches->pluck('winner_id')->filter()->unique()->values()->all();
        $winnerChars = ! empty($winnerIds)
            ? Character::with('user')->whereIn('id', $winnerIds)->get()->keyBy('id')
            : collect();

        foreach ($matches as $match) {
            $sourceKey = "arm_{$match->id}";
            if (isset($existingMatchKeys[$sourceKey])) {
                continue;
            }

            $winner = $winnerChars->get($match->winner_id);
            $bet = (int) $match->bet_amount;
            $duration = (int) $match->match_duration_ms;
            $clicks = (int) $match->challenger_clicks;
            $desc = "Armdrukken match gewonnen door {$winner?->name} (inzet €".number_format($bet, 0, ',', '.').", duur {$duration}ms, clicks: {$clicks})";

            $log = self::log(
                action: 'arm_wrestle',
                category: 'combat',
                description: $desc,
                character: $winner,
                user: $winner?->user,
                amount: $bet,
                details: [
                    'source_key' => $sourceKey,
                    'backfilled' => true,
                    'bet_amount' => $bet,
                    'duration_ms' => $duration,
                    'clicks' => $clicks,
                    'challenger_id' => $match->challenger_id,
                    'opponent_id' => $match->opponent_id,
                ],
                createdAt: Carbon::parse($match->created_at),
                ipAddress: null
            );

            $synced++;
            if ($log->risk_level === 'hoog') {
                $highRisk++;
            } elseif ($log->risk_level === 'verdacht') {
                $suspicious++;
            }
        }

        // 5. User Bans & Warnings
        $bans = DB::table('user_bans')
            ->orderBy('id', 'desc')
            ->limit($limitPerSource)
            ->get();

        $banKeys = $bans->map(fn ($ban) => "ban_{$ban->id}")->all();
        $existingBanKeys = self::getExistingSourceKeys('user_ban', $banKeys);

        $banUserIds = $bans->pluck('user_id')->filter()->unique()->values()->all();
        $banUsers = ! empty($banUserIds)
            ? User::with('character')->whereIn('id', $banUserIds)->get()->keyBy('id')
            : collect();

        foreach ($bans as $ban) {
            $sourceKey = "ban_{$ban->id}";
            if (isset($existingBanKeys[$sourceKey])) {
                continue;
            }

            $user = $banUsers->get($ban->user_id);
            $char = $user?->character;
            $desc = "Speler verbannen: {$ban->reason} (tot {$ban->banned_until})";

            $log = self::log(
                action: 'user_ban',
                category: 'moderation',
                description: $desc,
                character: $char,
                user: $user,
                details: [
                    'source_key' => $sourceKey,
                    'backfilled' => true,
                    'reason' => $ban->reason,
                    'banned_by_id' => $ban->banned_by_id,
                    'banned_until' => $ban->banned_until,
                    'is_active' => (bool) $ban->is_active,
                ],
                customRisk: 'hoog',
                customReason: "Account verbannen: {$ban->reason}",
                createdAt: Carbon::parse($ban->created_at),
                ipAddress: null
            );

            $synced++;
            $highRisk++;
        }

        $warnings = DB::table('user_warnings')
            ->orderBy('id', 'desc')
            ->limit($limitPerSource)
            ->get();

        $warningKeys = $warnings->map(fn ($warn) => "warn_{$warn->id}")->all();
        $existingWarningKeys = self::getExistingSourceKeys('user_warning', $warningKeys);

        $warningUserIds = $warnings->pluck('user_id')->filter()->unique()->values()->all();
        $warningUsers = ! empty($warningUserIds)
            ? User::with('character')->whereIn('id', $warningUserIds)->get()->keyBy('id')
            : collect();

        foreach ($warnings as $warn) {
            $sourceKey = "warn_{$warn->id}";
            if (isset($existingWarningKeys[$sourceKey])) {
                continue;
            }

            $user = $warningUsers->get($warn->user_id);
            $char = $user?->character;
            $desc = "Officiële waarschuwing: {$warn->reason}";

            $log = self::log(
                action: 'user_warning',
                category: 'moderation',
                description: $desc,
                character: $char,
                user: $user,
                details: [
                    'source_key' => $sourceKey,
                    'backfilled' => true,
                    'reason' => $warn->reason,
                    'issuer_id' => $warn->issuer_id,
                ],
                customRisk: 'verdacht',
                customReason: "Waarschuwing opgelegd: {$warn->reason}",
                createdAt: Carbon::parse($warn->created_at),
                ipAddress: null
            );

            $synced++;
            $suspicious++;
        }

        return [
            'total_synced' => $synced,
            'high_risk' => $highRisk,
            'suspicious' => $suspicious,
        ];
    }
}
