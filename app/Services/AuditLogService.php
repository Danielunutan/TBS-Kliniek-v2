<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Character;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
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
        $user = $user ?? $character?->user;
        $ip = $ipAddress ?? (Request::hasSession() ? Request::ip() : null);

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

        // Evaluate money transfers & transactions
        if ($amount !== null && $amount > 0) {
            if ($amount >= 10_000_000) {
                $score += 75;
                $flags[] = 'Extreem hoog geldbedrag (€'.number_format($amount, 0, ',', '.').')';
            } elseif ($amount >= 2_500_000) {
                $score += 45;
                $flags[] = 'Aanzienlijk geldbedrag (€'.number_format($amount, 0, ',', '.').')';
            } elseif ($amount >= 1_000_000) {
                $score += 25;
                $flags[] = 'Groot geldbedrag (€'.number_format($amount, 0, ',', '.').')';
            }

            if ($character && $character->level < 10 && $amount >= 1_000_000) {
                $score += 40;
                $flags[] = "Lage level speler (lvl {$character->level}) handelt met hoog bedrag";
            }
        }

        // Action specific checks
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
            $duration = (int) ($details['duration_ms'] ?? 0);
            $bet = (int) ($details['bet_amount'] ?? 0);

            if ($duration > 0 && $duration < 400) {
                $score += 80;
                $flags[] = "Verdacht snelle armdrukwinst ({$duration}ms) mogelijke macro/clickbot";
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
     * Backfill historical events from core game log tables.
     *
     * @return array{total_synced: int, high_risk: int, suspicious: int}
     */
    public static function backfillHistory(int $limitPerSource = 1500): array
    {
        $synced = 0;
        $highRisk = 0;
        $suspicious = 0;

        // 1. Bank Transactions
        $bankTxs = DB::table('bank_transactions')
            ->orderBy('id', 'desc')
            ->limit($limitPerSource)
            ->get();

        foreach ($bankTxs as $tx) {
            $sourceKey = "bank_tx_{$tx->id}";
            $exists = DB::table('audit_logs')
                ->where('action', 'bank_transaction')
                ->where('details->source_key', $sourceKey)
                ->exists();

            if ($exists) {
                continue;
            }

            $char = Character::find($tx->character_id);
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
                    'type' => $tx->type,
                    'source' => $tx->source,
                    'balance_after' => $tx->balance_after,
                ],
                createdAt: Carbon::parse($tx->created_at)
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

        foreach ($thefts as $th) {
            $sourceKey = "theft_{$th->id}";
            $exists = DB::table('audit_logs')
                ->where('action', 'theft')
                ->where('details->source_key', $sourceKey)
                ->exists();

            if ($exists) {
                continue;
            }

            $thief = Character::find($th->thief_id);
            $victim = $th->victim_id ? Character::find($th->victim_id) : null;
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
                    'success' => (bool) $th->success,
                    'cash_stolen' => $stolen,
                    'victim_id' => $th->victim_id,
                    'victim_name' => $victim?->name,
                    'jailed' => (bool) $th->jailed,
                ],
                createdAt: Carbon::parse($th->created_at)
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

        foreach ($donations as $don) {
            $sourceKey = "gang_don_{$don->id}";
            $exists = DB::table('audit_logs')
                ->where('action', 'gang_donation')
                ->where('details->source_key', $sourceKey)
                ->exists();

            if ($exists) {
                continue;
            }

            $char = Character::find($don->character_id);
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
                    'gang_id' => $don->gang_id,
                ],
                createdAt: Carbon::parse($don->created_at)
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

        foreach ($matches as $match) {
            $sourceKey = "arm_{$match->id}";
            $exists = DB::table('audit_logs')
                ->where('action', 'arm_wrestle')
                ->where('details->source_key', $sourceKey)
                ->exists();

            if ($exists) {
                continue;
            }

            $winner = Character::find($match->winner_id);
            $bet = (int) $match->bet_amount;
            $duration = (int) $match->match_duration_ms;
            $clicks = (int) $match->challenger_clicks;
            $desc = "Armdrukken match gewonnnen door {$winner?->name} (inzet €".number_format($bet, 0, ',', '.').", duur {$duration}ms, clicks: {$clicks})";

            $log = self::log(
                action: 'arm_wrestle',
                category: 'combat',
                description: $desc,
                character: $winner,
                user: $winner?->user,
                amount: $bet,
                details: [
                    'source_key' => $sourceKey,
                    'bet_amount' => $bet,
                    'duration_ms' => $duration,
                    'clicks' => $clicks,
                    'challenger_id' => $match->challenger_id,
                    'opponent_id' => $match->opponent_id,
                ],
                createdAt: Carbon::parse($match->created_at)
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

        foreach ($bans as $ban) {
            $sourceKey = "ban_{$ban->id}";
            $exists = DB::table('audit_logs')
                ->where('action', 'user_ban')
                ->where('details->source_key', $sourceKey)
                ->exists();

            if ($exists) {
                continue;
            }

            $user = User::find($ban->user_id);
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
                    'reason' => $ban->reason,
                    'banned_by_id' => $ban->banned_by_id,
                    'banned_until' => $ban->banned_until,
                    'is_active' => (bool) $ban->is_active,
                ],
                customRisk: 'hoog',
                customReason: "Account verbannen: {$ban->reason}",
                createdAt: Carbon::parse($ban->created_at)
            );

            $synced++;
            $highRisk++;
        }

        $warnings = DB::table('user_warnings')
            ->orderBy('id', 'desc')
            ->limit($limitPerSource)
            ->get();

        foreach ($warnings as $warn) {
            $sourceKey = "warn_{$warn->id}";
            $exists = DB::table('audit_logs')
                ->where('action', 'user_warning')
                ->where('details->source_key', $sourceKey)
                ->exists();

            if ($exists) {
                continue;
            }

            $user = User::find($warn->user_id);
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
                    'reason' => $warn->reason,
                    'issuer_id' => $warn->issuer_id,
                ],
                customRisk: 'verdacht',
                customReason: "Waarschuwing opgelegd: {$warn->reason}",
                createdAt: Carbon::parse($warn->created_at)
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
